<?php
//generateSchedules.php
//Used to generate schedules for ajax calls
//

include_once('settings.php');
include_once('courseScheduler.php');
include_once('databaseConnection.php');

$connection = new databaseConnection();

beginTimeMeasurement();

if(!isset($_GET['classes']))
	exit();
$enteredClasses = explode(":", $_GET['classes']);

$courses = array(); //this will be an array of arrays
$classErrorArr = array();
for($x = 0; $x < count($enteredClasses); $x++){
	$a = database_search($enteredClasses[$x], $connection);
	if(count($a) <= 0 && count($classErrorArr) < $x){ //if we didn't find a class and don't already have an error for this class, then post a new one
		array_push($classErrorArr, "Unable to find listed course");
	}
	if(!isset($_POST['closed'])){
		$a = removeClosedCourses($a);
		if(count($a) <= 0 && count($classErrorArr) < $x)
			array_push($classErrorArr, "All instances of the listed course are full");
	}
	$a = removeClassesBeforeOrAfter(isset($_POST['early'])?$_POST['early']:0, isset($_POST['late'])?$_POST['late']:23, $a);
	$a = removeOnlineClasses($a);

	if(count($a) <= 0 && count($classErrorArr) < $x)
		array_push($classErrorArr, "Unable to find class matching time parameters");
	if(count($a) > 0){
		array_push($courses, $a);
		array_push($classErrorArr, "");
	}
}

$scheduler = new scheduler($courses);
$w = array(0, 0, 0, 0);
if(isset($_POST['timeBetweenClasses'])){
	$w[0] = $w[1] = 1.0;
}
if(isset($_POST['dayClasstime'])){
	$w[3] = $w[4] = $_POST['dayClasstime'];
}
$scheduler->sort($w[0], $w[1], $w[2], $w[3]);
$schedule = $scheduler->getSchedule(1);


$combos = $scheduler->getAllCombinations();

$finalArr = array();
$allCourses = array();
foreach ($courses as $classArr) {
	foreach($classArr as $class) {
		array_push($allCourses, $class);
	}
}
$finalArr["courses"] = $allCourses;

$courseNums = array();

foreach ($combos as $obj) {
	//strip unnecessary data
	$courseNumArr = array();
	foreach($obj->courses as $c) {
		array_push($courseNumArr, getIndexFromClassNumber($c->classNumber, $allCourses));
	}
	array_push($courseNums, $courseNumArr);
}

$finalArr["combos"] = $courseNums;
$finalArr["generationTime"] = finishTimeMeasurement();

echo json_encode($finalArr);

$connection->close();

//helper functions
//

function getIndexFromClassNumber ($classNumber, $courseArr) {
	for ($x = 0; $x < count($courseArr); $x++) {
		if ($classNumber == $courseArr[$x]->classNumber)
			return $x;
	}
	return -1;
}

function database_search($searchStr, $connection){
	$searchStr = trim($searchStr);
	$searchStr = str_replace(' ', '', $searchStr);
	$returnArr = array();
	$prefix = substr($searchStr, 0, strlen($searchStr)-4);
	$course = substr($searchStr, strlen($searchStr)-4);
	
    $query = "SELECT * FROM courses WHERE prefix='$prefix' AND course='$course'";
    $result = $connection->query($query);
    $arr = $result->fetchAll();
    //$arr is an array of rows
    $returnArr = array();
    foreach($arr as $r){
    	$course = new course($r);
    	array_push($returnArr, $course);
    }
    return $returnArr;
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

function removeOnlineClasses($courses){
	$final = array();
	foreach($courses as $c)
		if(!$c->classDoesNotHaveTime)
			array_push($final, $c);
	return $final;
}

function beginTimeMeasurement() {
	global $start;
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$start = $time;
}

function finishTimeMeasurement() {
	global $start;
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish = $time;
	$total_time = round(($finish - $start), 4);
	return $total_time;
}

?>
