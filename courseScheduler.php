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
		$total = 0;
		foreach($classes as $c) $total += $c->classNumber;
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
		$schedules = array(); //all the schedules
		$currentCourses = array();
		$sums = array(); //array of sums to check for uniqueness
		$classCount = count($this->courses);
		for($x = 0; $x < $classCount; $x++) array_push($currentCourses, 0); //currentCourses represents which index for which course will currently be used - start all at 0
		for(;;){
			$courseArr = $this->getCourseArrFromCourseIndexArr($currentCourses);
			$sum = $this->sumClasses($courseArr);
			if($this->comboIsValid($courseArr) && !in_array($sum, $sums)){
				$s = new schedule($courseArr);
				//update sorting values to get max values
				$this->updateSortingValues($s);
				array_push($schedules, $s);
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

class schedule{
	public $courses;
	public $count;
	public $totalTimeBetweenClasses = 0; //weighted value
	public $averageTimeBetweenClasses = 0;
	public $totalClassdays = 0; //weighted value
	public $maxDayClasstime = 0;
	public $averageDayClasstime = 0;
	public $minDayClasstime = 0;

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
		$this->totalClassdays = $this->getTotalClassdays();
		$this->calculateTimeBetweenClasses();
		$this->calculateClasstime();
	}

	private function calculateTimeBetweenClasses(){
		$totalTimeBetween = 0;
		$days = 0;
		for($day = 0; $day < 5; $day++){
			$timeslots = $this->getOrderedTimeslotsForDay($day);
			if(count($timeslots) <= 1) continue;
			$days++;
			$timeBetweenForDay = 0;
			for($x = 0; $x < count($timeslots)-1; $x++){
				$timeBetweenForDay += $timeslots[$x+1]->startTime->toInteger() - $timeslots[$x]->endTime->toInteger();
			}
			$totalTimeBetween += $timeBetweenForDay;
		}
		$this->totalTimeBetweenClasses = $totalTimeBetween;
		if($days == 0)
			$this->averageTimeBetweenClasses = $totalTimeBetween;
		else
			$this->averageTimeBetweenClasses = $totalTimeBetween / $days;
	}

	private function calculateClasstime(){
		$max = 1000000;
		$finalTotal = 0;
		$min = 0;
		for($day = 0; $day < 5; $day++){
			$timeslots = $this->getOrderedTimeslotsForDay($day);
			if(count($timeslots) == 0) continue;
			$total = 0;
			for($x = 0; $x < count($timeslots); $x++){
				$total += $timeslots[$x]->endTime->toInteger() - $timeslots[$x]->startTime->toInteger();
			}
			if($total > $max) $max = $total;
			if($total < $min) $min = $total;
			$finalTotal += $total;
		}
		$this->maxDayClasstime = $max;
		$this->minDayClasstime = $min;
		$this->averageDayClasstime = $finalTotal / $this->getTotalClassdays();
	}

	private function getTotalClassdays(){
		$days = 0;
		for($day = 0; $day < 5; $day++)
			$days += (count($this->getOrderedTimeslotsForDay($day))==0)?0:1;
		return $days;
	}

	private function getOrderedTimeslotsForDay($day){
		//we can assume that the timeslots don't overlap
		//return timeslots in chronological order
		$timeslots = array();
		foreach($this->courses as $c){
			if($c->getTimeslotForDay($day))
				array_push($timeslots, $c->getTimeslotForDay($day));
		}
		usort($timeslots, "compareTimeslots");
		return $timeslots;
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

?>
