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
	public $timeBetweenClasses = 0; //0 for no sorting, -1 to minimize, 1 to maximize
	public $totalClassdays = 0; //0 for no sorting, -1 for fewer days of class, 1 for more days of class (classes spread out further)
	public $sortingSchedule; //used to store max values for calculating weighted values

	function __construct($array, $timeslots=array()){
		//array of courses array - each index contains an array
		$this->courses = $array;
		usort($this->courses, "compareCourses");
		$this->courseCount = count($array);
		$this->reservedTimeslots = $timeslots;
		$this->sortingSchedule = new schedule(array());
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
		$total = "";
		foreach($classes as $c) $total = $total . $c->classNumber;
		return $total;
	}

	function sort($w1=1.0, $w2=1.0, $w3=1.0, $w4=1.0){
		if(!isset($this->allCombos))
			$this->getAllCombinations();
		if($this->allCombos == null) return;
		if($w1 == 0 && $w2 == 0 && $w3 == 0 && $w4 == 0){
			shuffle($this->allCombos); return;}
		foreach($this->allCombos as $s)
			$s->calculateWeight($this->sortingSchedule, $w1, $w2, $w3, $w4);
		usort($this->allCombos, "compareSchedules");
	}

	//functions to find all combinations
	//
	//

	private function compareScheduleArr($course, $schedule) {
		foreach ($schedule as $c) {
			if($c->doesCourseConflict($course))
				return false;
		}
		return true;
	}

	function getAllCombinations($force=false){
		//if 0 or 1 classes return
		if(count($this->courses) == 0)
			return array();
		else if(count($this->courses) == 1){
			$r = array();
			foreach($this->courses[0] as $cc)
				array_push($r, new schedule(array($cc)));
			return $r;
		}

		//cache the result and only recalculate if necessary
		if(isset($this->allCombos) && !$force){
			return $this->allCombos;
		}

		//new sorting
		$schedules = array();
		for ($x = 0; $x < count($this->courses[0]); $x++){
			$schedules[$x] = array($this->courses[0][$x]);
		}
		for ($x = 1; $x < $this->courseCount; $x++) {
			unset($schedules_temp);
			$schedules_temp = array();
			$courseList = $this->courses[$x];
			foreach ($schedules as $schedule) {
				foreach ($courseList as $course) {
					if ($this->compareScheduleArr($course, $schedule)) {
						$newSchedule = $schedule;
						array_push($newSchedule, $course);
						array_push($schedules_temp, $newSchedule);
					}
				}
			}
			$schedules = $schedules_temp;
		}
		$this->allCombos = array();
		foreach ($schedules as $schedule) {
			$s = new schedule($schedule);
			$this->updateSortingValues($s);
			array_push($this->allCombos, $s);
		}

		return $this->allCombos;
	}

	private function updateSortingValues($schedule){
		get_max($this->sortingSchedule->totalTimeBetweenClasses, $this->sortingSchedule->totalTimeBetweenClasses, $schedule->totalTimeBetweenClasses);
		get_max($this->sortingSchedule->averageTimeBetweenClasses, $this->sortingSchedule->averageTimeBetweenClasses, $schedule->averageTimeBetweenClasses);
		get_max($this->sortingSchedule->maxDayClasstime, $this->sortingSchedule->maxDayClasstime, $schedule->maxDayClasstime);
		get_max($this->sortingSchedule->averageDayClasstime, $this->sortingSchedule->averageDayClasstime, $schedule->averageDayClasstime);
	}

	private function getCourseArrFromCourseIndexArr($courseIndexArr) {
		$courseArr = array();
		for($x = 0; $x < count($courseIndexArr); $x++){
			array_push($courseArr, $this->courses[$x][$courseIndexArr[$x]]);
		}
		return $courseArr;
	}

	private function comboIsValid($courses){
		for($x = count($courses)-1; $x >= 0; $x--){
			for($y = $x-1; $y >= 0; $y--){
				$c1 = $courses[$x];
				$c2 = $courses[$y];
				if($c1->doesCourseConflict($c2)) {
					return FALSE;
				}
			}
		}
		return TRUE;
	}
}

class schedule{
	public $courses;
	public $count;
	public $totalTimeBetweenClasses = 0; //weighted value
	public $averageTimeBetweenClasses = 0;
	public $totalClassdays = 0; //weighted value
	public $maxDayClasstime = 0;
	public $averageDayClasstime = 0;
	public $minDayClasstime = 0;
	private $orderedTimeslots;

	public $weightedValue = 0; //smaller values are better

	function __construct($courses){
		if(count($courses) == 0) return;
		$this->courses = $courses;
		$this->count = count($courses);
		$this->calculateValues();
	}

	function calculateWeight($s, $w1, $w2, $w3, $w4){
		//calcualte weights by converting each value to a range between 0-1000
		//use values in $s as the max values
		$this->weightedValue = (
		                        (($this->totalTimeBetweenClasses * 1000)/$s->totalTimeBetweenClasses)*$w1 + 
		                        (($this->averageTimeBetweenClasses * 1000)/$s->averageTimeBetweenClasses)*$w2 +
		                        (($this->maxDayClasstime * 1000)/$s->maxDayClasstime)*$w3 + 
		                        (($this->averageDayClasstime * 1000)/$s->averageDayClasstime)*$w4
		                        );
	}

	function calculateValues(){
		// $this->totalClassdays = $this->getTotalClassdays();
		// $this->calculateTimeBetweenClasses();
		// $this->calculateClasstime();
		$this->totalClassdays = 0;
		for($day = 0; $day < 5; $day++)
			$this->totalClassdays += (count($this->getOrderedTimeslotsForDay($day))==0)?0:1;

		//for time between classes
		$totalTimeBetween = 0;
		$days = 0;
		//for total classtime
		$max = 1000000;
		$finalTotal = 0;
		$min = 0;
		for($day = 0; $day < 5; $day++){
			$timeslots = $this->getOrderedTimeslotsForDay($day);
			if(count($timeslots) == 0) continue;
			//handle time between classes
			if(count($timeslots) > 1) $days++; //keep track for calculating total time between classes
			$timeBetweenForDay = 0;
			for($x = 0; $x < count($timeslots)-1; $x++){
				$timeBetweenForDay += $timeslots[$x+1]->startTime->toInteger() - $timeslots[$x]->endTime->toInteger();
			}
			$totalTimeBetween += $timeBetweenForDay;

			//handle classtime for day
			$total = 0;
			for($x = 0; $x < count($timeslots); $x++){
				$total += $timeslots[$x]->endTime->toInteger() - $timeslots[$x]->startTime->toInteger();
			}
			if($total > $max) $max = $total;
			if($total < $min) $min = $total;
			$finalTotal += $total;
		}
		//time between classes
		$this->totalTimeBetweenClasses = $totalTimeBetween;
		if($days == 0)
			$this->averageTimeBetweenClasses = $totalTimeBetween;
		else
			$this->averageTimeBetweenClasses = $totalTimeBetween / $days;
		
		//total classtime
		$this->maxDayClasstime = $max;
		$this->minDayClasstime = $min;
		$this->averageDayClasstime = $finalTotal / $this->totalClassdays;
	}

	private function getOrderedTimeslotsForDay($day){
		//we can assume that the timeslots don't overlap
		//return timeslots in chronological order
		if (count($this->orderedTimeslots) != 0)
			return $this->orderedTimeslots[$day];
		$this->orderedTimeslots = array();
		for($x = 0; $x < 5; $x++) {
			$this->orderedTimeslots[$x] = array();
			foreach($this->courses as $c){
				if($c->getTimeslotForDay($x))
					array_push($this->orderedTimeslots[$x], $c->getTimeslotForDay($x));
			}
			usort($this->orderedTimeslots[$x], "compareTimeslots");
		}
		return $this->orderedTimeslots[$day];
	}

	function moveElement(&$array, $a, $b) {
    	$out = array_splice($array, $a, 1);
    	array_splice($array, $b, 0, $out);
	}
}

function get_max(&$var, $val1, $val2){
	$var = ($val1 > $val2) ? $val1 : $val2;
}

//Comparison functions for sorting
//
//

function compareSchedules($a, $b){
	if($a->weightedValue == $b->weightedValue)
		return 0;
	return ($a->weightedValue < $b->weightedValue) ? -1 : 1;
}

function compareTimeslots($a, $b){
	return $a->isTimeslotAfter($b) ? -1 : 1;
}

function compareCourses($a, $b){
	return count($a)>count($b)?1:-1; //sort from largest to smalled
}

?>
