<?php
//course object - stores all relevant info for a given class
//each instance represent a certain course

include_once('timeslot.php');

class course {
	public $classID = ""; //full class id cs2336.001.15s
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
		if (count($classArray) == 0) return;
		$this->classID = $classArray[0];
		$this->classSection = $classArray[1].$classArray[2].'.'.$classArray[3];
		$this->classTerm = $classArray[4];
		$this->classNumber = $classArray[5];
		$this->classTitle = $classArray[6];
		$this->classIsOpen = $classArray[7];
		$this->classInstructor = $classArray[8];
		$this->classTimes = $this->getTimeslots($classArray[9], $classArray[10], $classArray[11]);
		$this->classRoom = explode("|", $classArray[11])[0];
		$this->classDoesNotHaveTime = $classArray[12];
	}

	function setCustomTimeslots($timeslots) {
		$this->classTimes = $timeslots;
		$this->classID = "custom";
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
		$return = array();
		foreach($this->classTimes as $t) {
			if($t->day === $day) array_push($return, $t);
		}
		return $return;
	}
	
	function getClasstime(){
		$timeslot = $this->classTimes[0];
		return $timeslot->startTime->getString() . " - " . $timeslot->endTime->getString(); 
	}

	function getTimeslots($daystring, $timestring, $roomstring){
		if(strlen($daystring) < 1 || strlen($timestring) < 1)
			return array();
		$timeslots = array();
		$delimiter = "|";
		$explodedTimes = explode($delimiter, $timestring);
		$explodedDays = explode($delimiter, $daystring);
		$explodedRooms = explode($delimiter, $roomstring);

		for ($x = 0; $x < count($explodedTimes); $x++) {
			$currentTimestring = $explodedTimes[$x];
			$currentDaystring = $explodedDays[$x];
			$currentRoomstring = $explodedRooms[$x];
			
			$currentTimestring = str_replace(" ", "", $currentTimestring);
			$times = explode("-", $currentTimestring);
			$startTime = $this->parseTime($times[0]); //get the starting time
			$endTime = $this->parseTime($times[1]); //get the ending time
			if(strpos($currentDaystring, "Mon") !== false) array_push($timeslots, new timeslot(0, $startTime, $endTime, $currentRoomstring));
			if(strpos($currentDaystring, "Tues") !== false) array_push($timeslots, new timeslot(1, $startTime, $endTime, $currentRoomstring));
			if(strpos($currentDaystring, "Wed") !== false) array_push($timeslots, new timeslot(2, $startTime, $endTime, $currentRoomstring));
			if(strpos($currentDaystring, "Thurs") !== false) array_push($timeslots, new timeslot(3, $startTime, $endTime, $currentRoomstring));
			if(strpos($currentDaystring, "Fri") !== false) array_push($timeslots, new timeslot(4, $startTime, $endTime, $currentRoomstring));
			if(strpos($currentDaystring, "Sat") !== false) array_push($timeslots, new timeslot(5, $startTime, $endTime, $currentRoomstring));
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
