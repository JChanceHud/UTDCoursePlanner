<?php
//index.php
//

include_once('settings.php');

include_once('calendarGenerator.php');
include_once('courseScheduler.php');
include_once('databaseConnection.php');

$connection = new databaseConnection();

$c = 1;
while(isset($_POST['course' . $c]) && strlen($_POST['course' . $c]) > 0){
	$c++;
}

$courses = array(); //this will be an array of arrays
$classErrorArr = array();
for($x = 1; $x < $c; $x++){
	$a = database_search($_POST['course' . $x], $connection);
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


//if($c == 1) //then there was no input last time

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

function setJSValue($varName, $value){
	if(isset($value)){
		echo $varName . " = " . $value . ";";
	}
}

?>
<html>
<head>
<title>Course Planner</title>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="tableStyle.css">
<link rel="stylesheet" type="text/css" href="design/stylesheet.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>
var counter = <?php echo $c-1==0?1:$c-1 ?>;
var limit = 6;
function addInput(divName){
	if (counter == limit)  {
		alert("There is a limit of " + counter + " classes");
	}
	else {
		var newdiv = document.createElement('div');
		newdiv.innerHTML = "<br />Class " + (counter + 1) + ": <input type='text' name='course"+(counter+1)+"'><br />";
		newdiv.id = divName + (counter+1);
		document.getElementById(divName + counter).appendChild(newdiv);
		counter++;
	}
}
var classes = <?php
	//generate permalink
	$courseString = "'";
	for($x = 1; $x < $c; $x++){
		$courseString = $courseString.$_POST['course' . $x] . ":";
	}
	if(strlen($courseString) > 1)
		$courseString = substr($courseString, 0, strlen($courseString)-1); //strip the last colon
	$courseString .= "';";
	echo $courseString;
?>

var timeLimitEarly = 0;
var timeLimitLate = 23;
var showSchedule = 0;
var totalSchedules = 0;
var realTotalSchedules = 0; //not limited to 100
var allowClosedClasses = 0;
var timeBetweenClasses = 0;
var dayClasstime = 0;

<?php
	setJSValue("timeLimitEarly", $_POST['early']);
	setJSValue("timeLimitLate", $_POST['late']);
	setJSValue("showSchedule", $_POST['showSchedule']);
	setJSValue("totalSchedules", count($scheduler->getAllCombinations()));
	setJSValue("allowClosedClasses", $_POST['closed']);
	setJSValue("timeBetweenClasses", $_POST['timeBetweenClasses']);
	setJSValue("dayClasstime", $_POST['dayClasstime']);
?>

realTotalSchedules = totalSchedules;
if(totalSchedules > 100) totalSchedules = 100;

$(document).ready(function(){
	$("#allowClosed").prop("checked", allowClosedClasses!=0);
	$("#early").val(timeLimitEarly);
	$("#late").val(timeLimitLate);
	$("#timeBetweenClasses").prop("checked", timeBetweenClasses!=0)
	var $radios = $('input:radio[name=dayClasstime]');
	$radios.filter('[value='+dayClasstime+']').prop('checked', true);

	if(totalSchedules != 0) showScheduleSelector();

	for(var i = 1; i < totalSchedules; i++){
		$('#table' + i).hide();
	}
	$("#currentSchedule").change(function(){
		for(var i = 0; i < totalSchedules; i++){
			$("#table" + i).hide();
		}
		$("#table" + $("#currentSchedule").val()).show();
		//update the permalink
		showSchedule = $("#currentSchedule").val();
		$.get("generatePermalink.php?classes=" + classes + "&allowClosed=" + $("#allowClosed").is(':checked') + "&showSchedule=" + showSchedule + "&late=" + timeLimitLate + "&early=" + timeLimitEarly, function(data){
			$("#permalink").html("PERMALINK");
			$("#permalink").attr("href", data);
		});
	});
	$("#currentSchedule").val(showSchedule);
	$("#currentSchedule").change();
});

function showScheduleSelector(){
	str = "Found a total of " + realTotalSchedules + " possible schedules. " + ((totalSchedules!=realTotalSchedules)? "Limiting number of displayed schedules to 100.":"") + " Currently displaying combination";
	$("#combo").prepend(str);
	console.log(totalSchedules);
	for(var x = 0; x < totalSchedules; x++){
		$('#currentSchedule').append("<option value="+x+">"+ (x+1) +"</option>"); 
	}
}

function showAllSchedules(count){
	if(count >= totalSchedules)
		return;
	$("#currentSchedule").val(count);
	$("#currentSchedule").change();
	count++;
	setTimeout(function(){ showAllSchedules(count); }, 100);
}

function openNewTab(url){
	var win = window.open(url, '_blank');
}
</script>
</head>
<body>
<div class="header">
<div class="header-content">
<a id="logo" href="http://utdallas.edu">UT Dallas</a>
<h2 style="float:right; display:inline-block; text-align:center; padding-top:26px;">Course Scheduler</h2>
</div>
</div>
<!--    -->

<div class="center-clear">
<!-- -->
<div class="colmask">
<div class="edge-inline-box">
<div class="colhead">
<div class="colheadinternal">
Information
</div>
</div>
<div class="colinternal">
Enter the classes you would like to take in the form and then press submit. 
<br /><br />
The calendar below will update.
<br /><br />
Sample input: CS2336
<br /><br /><br />
<!-- <input type="text" id="permalink"> -->
<a id="permalink" href=""></a>


<div id="combo" style="position:absolute; bottom:0"> 
<select id="currentSchedule">
</select><br /><br />
<button onClick="showAllSchedules(0)" type="button">Show all</button>
<br /><br />
</div>


</div>
</div>
<div class="center-inline-box">
<div class="colhead"><div class="colheadinternal">Classes</div></div>
<div class="colinternal">
<form action="index.php" method="POST">
<?php
if($c == 1){
echo '<div id="dynamicInput1">
Class 1:
<input type="text" name="course1" value=""><div id="course1"></div>
</div>';
}
else
	for($x = 1; $x < $c; $x++){
		if($x != 1) echo '<br />';
		echo '<div id="dynamicInput'.$x.'">
			Class '.$x.': <input type="text" name="course'.$x.'" value="'.$_POST['course' . $x].'"><font style="color:red"> ';
		echo $classErrorArr[$x-1];
		echo '</font>
			</div>';
	}
?>
<br />
<input type="button" value="Add another course" onClick="addInput('dynamicInput');">
<input type="submit" value="Submit">
</div>
</div>
<div class="edge-inline-box">
<div class="colhead"><div class="colheadinternal">settings</div></div>
<div class="colinternal">
<input type="checkbox" id="allowClosed" name="closed" value="1"> Include closed classes  <br /><br />
Class starts after: 
<select name="early" id="early">
	<option value="0">-</option>
	<option value="6">6 AM</option>
	<option value="7">7 AM</option>
	<option value="8">8 AM</option>
	<option value="9">9 AM</option>
	<option value="10">10 AM</option>
	<option value="11">11 AM</option>
</select>
<br />
Class ends before:  
<select name="late" id="late">
	<option value="23">-</option>
	<option value="15">3 PM</option>
	<option value="16">4 PM</option>
	<option value="17">5 PM</option>
	<option value="18">6 PM</option>
	<option value="19">7 PM</option>
	<option value="20">8 PM</option>
	<option value="21">9 PM</option>
</select><br /><br />
<input type="checkbox" id="timeBetweenClasses" name="timeBetweenClasses" value="1"> Minimize time between classes  <br /><br />
Class distribution<br />
<input type="radio" id="days" name="dayClasstime" value="0"> Do not weight  <br />
<input type="radio" id="days" name="dayClasstime" value="1"> Minimize class per day (more days of class)  <br />
<input type="radio" id="days" name="dayClasstime" value="-1"> Maximize class per day (fewer days of class)  <br />
</form>
</div> <!-- ending colinternal -->
</div> <!-- ending edge-inline-block -->
</div> <!-- ending colmas -->
</div> <!--ending center div-->

<div class="center">

<!--output the calendar-->
<?php
$combos = $scheduler->getAllCombinations();
for($x = 0; $x < count($combos); $x++){
	if($x >= 100) break;
	echo generateCalendar($combos[$x]->courses, $x);
}
if(count($combos) == 0) echo generateCalendar(array(), 0);

?>
</div>

<div class="footer">
<div class="footertext">
Current version: 1.1 | Copyright © 2015 Chance Hudson <br /> <a href="https://github.com/JChanceHud/UTDCoursePlanner">This site is open source!</a>
</div>
</div>

</body>

</html>

<?php
//close the database connection
$connection->close();
?>
