<?php
//get classInfo
//input class as get variable
//returns json
//

include_once('settings.php');
include_once('calendarGenerator.php');
include_once('courseScheduler.php');
include_once('databaseConnection.php');

if(!isset($_GET['class']))
	exit();

$connection = new databaseConnection();
$a = database_search($_GET['class'], $connection);

$openCount = 0;
foreach($a as $c) if($c->classIsOpen) $openCount++;
$s = strtoupper($a[0]->classSection);
$ss = explode(".", $s);
$r = array($ss[0], count($a), $openCount);
echo json_encode($r);

$connection->close();

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
?>
