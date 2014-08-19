<?php

class Student extends \Eloquent {
	use \Helper;

	protected $table = 'student';
	protected $primaryKey = 'studid';

	public function major()
	{
		return $this->hasOne('SemStudent', 'studid', 'studid');
	}

	public function search($q)
	{
		extract($q);
		// search for id
		//$model = static::find($q);
		$data = DB::table('studfullnames')
				->where('studid', $q)
				->get(['studid', 'fullname']);

		if ($data) {
			//$data = $model->toArray();
			$r[0] = static::encode($data[0]);

			if(isset($d)) { // direct return not array
				return $r[0];
			} else {
				return $r;
			}
		} else {
			return static::searchByLastName($q);
		}
	}

	public function searchByLastName($q)
	{
		$data = DB::table('studfullnames')
				->where('fullname', 'LIKE', strtoupper($q) . '%')
				->orderBy('fullname')
				->get(['studid', 'fullname']);
/*		$data = static::where('studlastname', 'LIKE', strtoupper($q) . '%')
				->orderBy('studlastname')
				->orderBy('studfirstname')
				->get(['studid', 'studfullname'])
				->toArray();
*/
		return static::encode($data);
	}

	public static function getStudentWithMajor($id, $sy, $sem)
	{
		$data = static::with('major')->whereHas('major', function($q) use ($id, $sy, $sem) {
			$q->where('studid', $id)
				->where('sy', $sy)
				->where('sem', $sem);
		})->get()->toArray();

		$data = static::encode($data);
		return $data[0];
	}
}