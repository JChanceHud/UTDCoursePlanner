<?php
//course object - stores all relevant info for a given class
//each instance represent a certain course

include_once('timeslot.php');

class course {
	public $classTerm = ""; //the semester
	public $classIsOpen = false; //if the class is open for registration
	public $classSection = ""; //i.e. cs 2336.003
	public $classNumber; //unique class number
	public $classTitle = "";
	public $classInstructor = "";
	public $classTimes; //array of timeslots
	public $classRoom = ""; //class room location
	public $classDoesNotHaveTime = FALSE;
	
	function __construct($classArray){
		$this->classTerm = $classArray[0];
		$this->classIsOpen = $classArray[1]=="Open";
		$this->classNumber = intval($classArray[2]);
		$this->classSection = $classArray[3];
		$this->classTitle = $classArray[4];
		$this->classInstructor = $classArray[5];
		//get class days and time
		$this->classTimes = $this->parseTimeslots($classArray[6]);
		$this->classRoom = $classArray[7];
	}
	
	function getClassURL(){
		$url = "http://coursebook.utdallas.edu/";
		$s = $this->classSection;
		$s = str_replace(" ", "", $s);
		return $url.$s;
	}

	//checks if this course conflicts with $course
	function doesCourseConflict($course){
		foreach($this->classTimes as $t){
			foreach($course->classTimes as $tt){
				if($t->doesTimeslotConflict($tt))
					return true;
			}
		}
		return false;
	}

	//returns the first timeslot that falls on a given day - or false if no timeslot on that day
	//$day is an integer value as per the timeslot class
	function getTimeslotForDay($day){
		foreach($this->classTimes as $t)
			if($t->day == $day) return $t;
		return false;
	}
	
	function getClasstime(){
		$timeslot = $this->classTimes[0];
		return $timeslot->startTime->getString() . " - " . $timeslot->endTime->getString(); 
	}

	function parseTimeslots($str){
		//parse the time first
		$base = strlen($str)-1;
		while(substr($str, $base, 1) !== "-"){
			$base--;
			if($base < 0){
				$this->classDoesNotHaveTime = TRUE;
				return array();
			}
		}
		++$base; //add 1 more to get rid of the dash
		$endTime = $this->parseTime(substr($str, $base, strlen($str)-$base)); //get the ending time

		$oldBase = $base-1;	
		$base -= 7;
		while(is_numeric(substr($str, $base, 1)) || $base < 0)
			$base--;
		++$base; //add 1 more to get rid of the dash
		$startTime = $this->parseTime(substr($str, $base, $oldBase-$base)); //get the ending time
		$timeslots = array();
		if(strpos($str, "Mon") !== false) array_push($timeslots, new timeslot(0, $startTime, $endTime));
		if(strpos($str, "Tues") !== false) array_push($timeslots, new timeslot(1, $startTime, $endTime));
		if(strpos($str, "Wed") !== false) array_push($timeslots, new timeslot(2, $startTime, $endTime));
		if(strpos($str, "Thurs") !== false) array_push($timeslots, new timeslot(3, $startTime, $endTime));
		if(strpos($str, "Fri") !== false) array_push($timeslots, new timeslot(4, $startTime, $endTime));
		if(count($timeslots) == 0){
			$this->classDoesNotHaveTime = TRUE;
		}
		return $timeslots;
	}

	//parses time from a string in format 12:45pm
	function parseTime($str){
		$base = strlen($str)-1; //end of str
		$currentStr = "";
		//check if endtime is am or pm
		$hourOffset = 0;
		if(substr($str, -2, 2) === "pm")
			$hourOffset = 12; //value to add the hour by to convert to 24 hour time
		$base = $base-3; //skip the am/pm part and move to the minutes of the ending time
		$min = intval(substr($str, $base, 2));
		$base = $base-3;
		$hourLength = 2;
		if($base < 0){
			$base += 1; //if the hour is 1 digit then move the base forward
			$hourLength = 1;
		}
		$hour = intval(substr($str, $base,$hourLength))+$hourOffset;
		if($hour === 24) $hour = 12; //account for 12 pm being weird
		$endTime = new time($hour, $min);
		return $endTime;
	}
}

?>
