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

	//gets a combination of classes (a schedule) that isn't already contained in the array of arrays ($usedCombinations)
	function getAllCombinations(){ 
		if(isset($this->allCombos))
			return $this->allCombos; //don't regenerate all the combinations every time
		$allCombinations = array(); //will be an array of arrays;
		$comboSums = array();
		$currentCourses = array();
		$c = $this->courses;
		$classCount = count($c);
		for($x = 0; $x < $classCount; $x++) array_push($currentCourses, 0); //currentCourses represents which index for which course will currently be used - start all at 0
		if(count($c) == 0)
			return $allCombinations;
		else if(count($c) == 1){
			$r = array();
			foreach($c[0] as $cc)
				array_push($r, array($cc));
			return $r;
		}

		for(;;){
			for($x = 0; $x < $classCount; $x++){
				for($y = 0; $y < $classCount; $y++){
					//if $y = $x then we are on the same course, if both are the last course then we are done
					if($y === $x) continue;

					if($c[$x][$currentCourses[$x]]->doesCourseConflict($c[$y][$currentCourses[$y]])){
						//if the 2 current courses conflict, then move the the next instance of $y class
						$currentCourses[$y]++;
						//if we have already compared all the $y classes than move back to the first $y class and move to the next $x class
						if($currentCourses[$y] >= count($c[$y])){
							$currentCourses[$y] = 0;
							$currentCourses[$x]++;
							//if we have already check all the $x courses against all the $y courses then there is no possible combination between those two courses
							if($currentCourses[$x] >= count($c[$x])){
								//no possible combination to be made
								$this->allCombos = $allCombinations;
								return $allCombinations;
							}
							//move back to the beginning to reconfirm that the new courses work
							$x = 0; break;
						}
					}
				}
			}
			//test the current combination to see if it's already in the array
			$currentCombo = array();
			for($x = 0; $x < $classCount; $x++){
				$cc = $c[$x];
				array_push($currentCombo, $cc[$currentCourses[$x]]);
			}
			$currentSum = $this->sumClasses($currentCombo);
			if(!in_array($currentSum, $comboSums) && count($currentCombo) == $classCount){
				array_push($comboSums, $currentSum);
				array_push($allCombinations, $currentCombo);
			}
			for($x = 0; $x < count($currentCourses); $x++){
				if($currentCourses[$x] < count($c[$x])-1){ 
					$currentCourses[$x]++; 
					break; 
				}
				if($x == count($currentCourses)-1) return $allCombinations;
			}
		}
		$this->allCombos = $allCombinations;
		return $allCombinations;
	}
}



?>
