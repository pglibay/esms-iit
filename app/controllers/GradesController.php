<?php

class GradesController extends \BaseController {
	use \Helper;

	protected $model;
	protected $data = array();

	public function __construct(Grade $model)
	{
		$this->model = $model;
		$this->data['sy'] = Session::get('user.sem.sy', '2014-2015');
		$this->data['sem'] = Session::get('user.sem.sem', '1');
	}

	/**
	 * Returns the students with grades by subjcode by section
	 */
	public function getBySubjectBySection($subjcode, $section)
	{
		$this->data['subjcode'] = urldecode($subjcode);
		$this->data['section'] = $section;

		return Response::json($this->model->getStudentsBySection($this->data));
	}

	public function saveGrade() {
		return Response::json($this->model->store(json_decode(Input::get('data'))));
	}

	public function lockGrade() {
		return Response::json($this->model->lock(json_decode(Input::get('data')), Input::get('lock')));
	}

	/**
	 * Exports grade sheet into excel.
	**/
	public function exportGrades() {
		if (Input::get('exportgrade')) {
			$this->data['subjcode'] = Input::get('subjcode');
			$this->data['section'] = Input::get('section');

			$data = $this->model->getStudentsBySection($this->data);
			$data['meta']['date'] = date('F d, Y');

			Excel::create(trim($data['meta']['subjcode']) . '-' . $data['meta']['section'], function($excel) use ($data) {
				// Creating the worksheet
			    $excel->sheet('New sheet', function($sheet) use ($data) {
					$sheet->setPageMargin(0.5, 0.75, 0.5, 0.75);
					$sheet->setFitToPage(false);
					$sheet->setStyle(array(
						'font' => array(
							'size'	=> 10,
							'name'	=> 'Calibri'
						)
					));
					$sheet->cell('A1', function($cell) {
						$cell->setFont(array(
								'size' => 10,
								'name' => 'Rockwell',
								'bold' => true
						));
					});
					$sheet->cell('A5', function($cell) {
						$cell->setFont(array(
								'size' => 10,
								'name' => 'Rockwell',
								'bold' => true
						));
					});
					$sheet->setColumnFormat(array('B' => '000000', 'D' => '0.0', 'E' => '0.0', 'F' => '0.0', 'G' => '0.0'));
					$sheet->setWidth(array('A' => 3.5, 'B' => 10, 'C' => 34, 'D' => 10, 'E' => 10, 'F' => 10, 'G' => 10, 'H' => 10));

			        $sheet->loadView('grades')->with('data', $data);
			    })->export('xlsx');
			});
		}
	}
}