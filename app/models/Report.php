<?php

class Report {
	use \Helper;

	/**
	 * Gets the data for the certificate of billing
	 *
	 * @param 	array $q 'studid', 'sy', 'sem'
	 * @return 	array assoc
	 *
	 * @ignore 	return data
	 * ---
	 *	'h' => 'studid', 'sy', 'sem', 'studfullname2', 'studmajor', 'studlevel', 'tuiamt', 'labamt', 't_unit', 't_as', 't_misc'
	 * 	's' => array => 'subjcodedsp', 'subjlec_units', 'subjlab_units', 'subjcredit'
	 * 	'misc' => array
	 *	'lab' => object
	 * 	'reg' => object
	 * 	'tui' => object
	 * 	'refund' => object
	 * ---
	 */
	public function getCertBilling($q)
	{
		$data = [];
		extract($q);

		// Get headers
		$h = DB::select("
			SELECT studid, t1.sy, t1.sem, studfullname2, studmajor, studlevel, t2.amt AS tuiamt, t3.amt AS labamt 
			FROM semstudent AS t1 
			LEFT JOIN student USING(studid)
			LEFT JOIN tuitionmatrix_new AS t2
				ON (
					t1.payment_sy = t2.sy AND
					t1.payment_sem = t2.sem AND
					t1.studmajor = t2.progcode
				)
			LEFT JOIN labmatrix_new AS t3
				ON (
					t1.payment_sy = t3.sy AND
					t1.payment_sem = t3.sem
				)
			WHERE studid=? AND t1.sy=? AND t1.sem=? AND t3.subjcode='DEFAULT        ' AND registered=true
		", array($studid, $sy, $sem));

		if (!$h)	throw new Exception('Student Not Enrolled in this Semester', 409);
		
		$h = static::encode($h);
		$data['h'] = $h[0];

		// Get the subjects enrolled with lec lab total units
		$subj = Subject::getEnrolledSubjects($studid, $sy, $sem);
		$data['s'] = $subj;
		$t = 0;

		foreach ($subj as $v) {
			$t += $v->subjcredit;
		}
		$data['h']['t_unit'] = number_format($t, 2);

		// Get assessment details
		$as = Assessment::getAssessmentDetails($studid, $sy, $sem);
		$t = 0;
		$m = 0;
		foreach ($as as $v) {
			switch($v->feecode) {
				case 'TUITIONFEE  ':
					$data['tui'] = $v;
					break;
				case 'REGFEE      ':
					$data['reg'] = $v;
					break;
				case 'LABFEE      ':
					$data['lab'] = $v;
					break;
				default:
					$data['misc'][] = $v;
					$m += $v->amt;
			}
			$t += $v->amt;
		}

		$data['h']['t_as'] = $t;
		$data['h']['t_misc'] = $m;

		// Get paid
		$p = Collection::getPaid($studid, $sy, $sem);
		$data['paid'] = $p;
		$data['h']['t_paid'] = null;
		
		$t = 0;
		foreach ($p as $v) {
			$t += $v->amt;
		}
		$data['h']['t_paid'] = $t;

		// Get Refund
		$r = Refund::getRefund($studid, $sy, $sem);
		$data['h']['t_refund'] = null;
		if ($r) {
			$data['refund'] = $r;
			$t = 0;
			foreach ($r as $v) {
				$t += $v->amt;
			}
			$data['h']['t_refund'] = $t;
		}

		$data['h']['bal'] = $data['h']['t_as'] - $data['h']['t_paid'] + $data['h']['t_refund'];
		$data['currentDate'] = date('Y-m-d');
		
		return $data;
	}

	public function getCollections($q)
	{
		extract($q);
		if ($bcode == 'CASHIER') {
			$rep = DB::select("SELECT * FROM get_bulkcollections_bydate(?, ?, ?, ?, ?)", array($datefrom, $dateto, $fund, '', ''));
		} else {
			$rep = DB::select("SELECT * FROM get_bulkcollections_bydate(?, ?, ?, ?, ?)", array($datefrom, $dateto, $fund, '1', $bcode));
		}
		
		$exp = [];
		$summary = [];
		if ($rep) {
			$c = 1;
			foreach ($rep as $k => $v) {
				$summary[$v->acctcode]['name'] = $v->acctname;
				$summary[$v->acctcode]['total'][] = $v->amount;
				if ((isset($rep[$k - 1]->refno) && $rep[$k - 1]->refno != $v->refno) || $k == 0) {
					$exp[$k] = array(
						$v->paydate,
						$v->refno,
						$v->studid,
						$v->payee,
						$v->acctcode,
						$v->acctname,
						$v->amount,
						'=SUM(G'. ($k+1) .':G'. ($k+1) .')'
					);
					if ($c > 1) {
						$exp[$k - $c][7] = '=SUM(G' . ($k - $c + 1) . ':G' . ($k) . ')';
						$c = 1;
					}
				} else {
					$exp[$k] = array(
						'',
						'',
						'',
						'',
						$v->acctcode,
						$v->acctname,
						$v->amount
					);

					if ($k == (count($rep) - 1)) {
						if ($c > 1) {
							$exp[$k - $c][7] = '=SUM(G' . ($k - $c + 1) . ':G' . ($k + 1) . ')';
							$c = 1;
						}
					}

					$c++;
				}
			}

			$x = count($exp);
			$exp[$x++] = array(
				'',
				'',
				'',
				'',
				'',
				'',
				'TOTAL:',
				'=SUM(H1:H' . ($x -1) . ')'
			);
		}

		$exp[$x++] = array();
		$exp[$x++] = array('', '', '', 'SUMMARY:');
		$c = $x;
		foreach ($summary as $k => $v) {
			$exp[$x][] = '';
			$exp[$x][] = '';
			$exp[$x][] = '';
			$exp[$x][] = '';
			$exp[$x]['code'] = $k;
			$exp[$x]['name'] = $v['name'];
			$exp[$x]['total'] = array_sum($v['total']);
			$x++;
		}
		$exp[$x] = array(
			'',
			'',
			'',
			'',
			'TOTAL:',
			'',
			'=SUM(G' . $c . ':G' . $x . ')'
		);

		Excel::create('Collections', function($excel) use ($exp) {
			$excel->setTitle('Our new awesome title');
			$excel->setDescription('A demonstration');

			$excel->sheet('Sheet 1', function($sheet) use ($exp) {
				$sheet->fromArray($exp, null, 'A1', false, false);
			});
		})->download('xlsx');
	}

	public function getReceivables($q)
	{
		extract($q);
		DB::beginTransaction();

			$conn = DB::connection()->getPdo();

			$query = $conn->prepare("SELECT * FROM get_studbalance(?, ?, ?, 'cursor', ?)");
			$query->execute(array($sy, $sem, $college, $date));

			$query = $conn->query('FETCH ALL IN cursor');
			$results = $query->fetchAll();

		DB::commit();
		unset($query);

		// prepare results
		foreach ($results as $k => $v) {
			$data['data'][$k] = [
				'studid' 		=> $v['studid'],
				'fullname' 		=> utf8_encode($v['fullname']),
				'total_assess' 	=> $v['total_assess'],
				'total_pay'		=> $v['total_pay'],
				'total_refund'	=> $v['total_refund'],
				'balance'		=> $v['balance'],
				'studmajor'		=> $v['studmajor'],
				'schdesc'		=> $v['schdesc']
			];
		}
		$data['total'] = [
			'count' => count($data['data']),
			'assess' => array_sum(array_column($data['data'], 'total_assess')),
			'paid' => array_sum(array_column($data['data'], 'total_pay')),
			'refund' => array_sum(array_column($data['data'], 'total_refund')),
			'balance' => array_sum(array_column($data['data'], 'balance'))
		];

		return $data;
	}

	public function getSubLedger($id)
	{
		$data = [];

		$res = DB::select("SELECT * FROM get_studentsubledger(?)", [$id]);
		foreach ($res as $key => $v) {
			$v->num = $key + 1;
			$k = $v->sy. ' &bull; '. $v->sem;
			$data['data'][$k]['name'] = $k;
			$data['data'][$k]['data'][] = $v;
		}

		$data['data'] = array_values($data['data']);
		$data['details'] = Student::getStudentWithMajor($id, $v->sy, $v->sem);

		return $data;
	}

	public function getSumBilling($q)
	{
		$data = [];
		$res = DB::select("SELECT * FROM get_summaryofbilling2(?, ?)", [$q['sy'], $q['sem']]);
		$data['data'] = $res;
		$data['sy'] = $q['sy'];
		$data['sem'] = $q['sem'];
		$data['total'] = 0;
		foreach ($res as $v) {
			$data['total'] += $v->amount;
		}

		if (isset($q['model']))	{ // for model structure
			$ret = [];
			$ret['data'] = $data;
			return $ret;
		} else {
			return $data;
		}
	}

}