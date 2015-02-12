<?php
//courseScheduler.php
//class to generate schedules
//

include_once("course.php");
include_once("timeslot.php");

class scheduler {
	public $courseCount; //number of independent courses	
	public $courses; //array of course array - each index has an array of possible classes
	public $reservedTimeslots; //array of timeslots that are to be reserved
	private $allCombos;

	function __construct($array, $timeslots=array()){
		//array of courses array - each index contains an array
		$this->courses = $array;
		$this->courseCount = count($array);
		$this->reservedTimeslots = $timeslots;
	}
	
	function setCourses($c){
		$this->courses = $c;
		unset($this->allCombos);
	}

	//get schedule
	function getSchedule($index = 0){
		$courseCombinations = $this->getAllCombinations(); //array of course arrays
		//$combo now contains all possible combinations of the courses
		if($index >= count($courseCombinations)) return FALSE;
		return $courseCombinations[$index];
	}
	
	//used as to generate unique ID based on list of classes
	private function sumClasses($classes){
		$total = 0;
		foreach($classes as $c) $total += $c->classNumber;
		return $total;
	}

	function getAllCombinations($force=false){
		//if 0 or 1 classes return
		if(count($this->courses) == 0)
			return array();
		else if(count($this->courses) == 1){
			$r = array();
			foreach($this->courses[0] as $cc)
				array_push($r, array($cc));
			return $r;
		}

		//cache the result and only recalculate if necessary
		if(isset($this->allCombos) && !$force){
			return $this->allCombos;
		}
		$schedules = array(); //all the schedules
		$currentCourses = array();
		$sums = array(); //array of sums to check for uniqueness
		$classCount = count($this->courses);
		for($x = 0; $x < $classCount; $x++) array_push($currentCourses, 0); //currentCourses represents which index for which course will currently be used - start all at 0
		for(;;){
			$courseArr = $this->getCourseArrFromCourseIndexArr($currentCourses);
			$sum = $this->sumClasses($courseArr);
			if($this->comboIsValid($courseArr) && !in_array($sum, $sums)){
				array_push($schedules, $courseArr);
				array_push($sums, $sum);
			}

			$x = $classCount-1;
			$currentCourses[$x]++;
			while($currentCourses[$x] >= count($this->courses[$x])){
				if($x == 0){
					$this->allCombos = $schedules;
					return $this->allCombos;
				}
				$currentCourses[$x] = 0;
				$currentCourses[$x-1]++;
				$x -= 1;
			}
		}
		return false;
	}

	private function getCourseArrFromCourseIndexArr($courseIndexArr) {
		$courseArr = array();
		for($x = 0; $x < count($courseIndexArr); $x++){
			array_push($courseArr, $this->courses[$x][$courseIndexArr[$x]]);
		}
		return $courseArr;
	}

	private function comboIsValid($courses){
		for($x = 0; $x < count($courses); $x++){
			for($y = $x+1; $y < count($courses); $y++){
				$c1 = $courses[$x];
				$c2 = $courses[$y];
				if($c1->doesCourseConflict($c2))
					return FALSE;
			}
		}
		return TRUE;
	}
}

?>
