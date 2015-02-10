<?php
// courseScraper.php
// responsible for getting course information from UTD coursebook
//

include_once('course.php');

//performs a search with a string argument
//returns an array of course objects

function search($searchStr){
	$returnArr = array();

	$searchResults = file_get_contents('http://coursebook.utdallas.edu/search/'.$searchStr);
	
	$dom = new DOMDocument;
	libxml_use_internal_errors(true); //silence warnings from bad html
	$dom->loadHTML($searchResults);
	$tbodyList = $dom->getElementsByTagName("tbody");
	$table = $tbodyList->item(0); //contains the table element
	
	$returnArr = array();
	//now iterate through each row
	for($x = 0; $x < $table->childNodes->length; $x++){
		$row = $table->childNodes->item($x);
		$arr = getStringsArrayFromNode($row);
		$course = new course($arr);
		array_push($returnArr, $course);
	}
	return $returnArr;
}

function getStringsArrayFromNode($node){
	$a = array();
	return getStringsArrayFromNodeRecursive($node, $a);
}

function getStringsArrayFromNodeRecursive($node, $arr){
	if($node->childNodes->length == 0)
		return $arr;
	for($x = 0; $x < $node->childNodes->length; $x++){
		$currentChild = $node->childNodes->item($x);
		if($currentChild->nodeType == XML_TEXT_NODE){
			$str = $currentChild->wholeText;
			if(strlen($str) > 0 && $str != " "){
				array_push($arr, $str);
			}
		}
	}

	for($x = 0; $x < $node->childNodes->length; $x++){
		$currentChild = $node->childNodes->item($x);
		if($currentChild->nodeType == XML_ELEMENT_NODE)
			$arr = getStringsArrayFromNodeRecursive($currentChild, $arr);
	}
	return $arr;
}


//pass an array of courses - removes the courses that are closed for registration
function removeClosedCourses($courseArr){
	$newArr = array();
	foreach($courseArr as $c){
		if($c->classIsOpen) array_push($newArr, $c);
	}
	return $newArr;
}


//removes classes that start before $early, or end after $late
function removeClassesBeforeOrAfter($early, $late, $courses){
	$newArr = array();
	foreach($courses as $c){
		$conflicts = FALSE;
		foreach($c->classTimes as $t){
			if($t->startTime->hour < $early || $t->endTime->hour >= $late){
				$conflicts = TRUE;
				break;
			}
		}
		if($conflicts === FALSE)
			array_push($newArr, $c);
	}
	return $newArr;
}


//accepts an array of arrays - each subarray contains an array of classes at different times
//returns an array with classes that work
function generateSchedule($courses){
	$classCount = count($courses);	
	$currentCourses = array();
	for($x = 0; $x < $classCount; $x++) array_push($currentCourses, 0); //currentCourses represents which course will currently be used
	for($x = 0; $x < $classCount; $x++){
		//$courses[$x][$currentCourses[$x]]
		for($y = 0; $y < $classCount; $y++){
			if($y === $x) continue;
			if($courses[$x][$currentCourses[$x]]->doesCourseConflict($courses[$y][$currentCourses[$y]])){
				$currentCourses[$y]++;
				if($currentCourses[$y] > count($courses[$y])){
					$currentCourses[$y] = 0;
					$currentCourses[$x]++;
					$x = 0; break;
				}
			}
		}
	}
	$final = array();
	for($x = 0; $x < $classCount; $x++){
		$c = $courses[$x];
		array_push($final, $c[$currentCourses[$x]]);
	}
	return $final;
}

function removeOnlineClasses($courses){
	$final = array();
	foreach($courses as $c)
		if(!$c->classDoesNotHaveTime)
			array_push($final, $c);
	return $final;
}

//no longer used - marked for removal
function removeDuplicates($courses){
	$courseNumbers = array();
	$final = array();
	foreach($courses as $c){
		if(!in_array($c->classNumber, $courseNumbers)){
			array_push($courseNumbers, $c->classNumber);
			array_push($final, $c);
		}
	}
	return $final;
}

?>
