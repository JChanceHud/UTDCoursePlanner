<?php
//index.php
//

include_once('courseScraper.php');
include_once('calendarGenerator.php');
include_once('courseScheduler.php');

$c = 1;
while(isset($_POST['course' . $c]) && strlen($_POST['course' . $c]) > 0){
	$c++;
}

$courses = array(); //this will be an array of arrays
$classErrorArr = array();
for($x = 1; $x < $c; $x++){
	$a = search($_POST['course' . $x]);
	if(count($a) <= 0 && count($classErrorArr) < $x){ //if we didn't find a class and don't already have an error for this class, then post a new one
		array_push($classErrorArr, "Unable to find listed course");
	}
	if(!isset($_POST['closed'])){
		$a = removeClosedCourses($a);
		if(count($a) <= 0 && count($classErrorArr) < $x)
			array_push($classErrorArr, "All instances of the listed course are full");
	}
	$a = removeClassesBeforeOrAfter($_POST['early'], $_POST['late'], $a);
	$a = removeOnlineClasses($a);

	if(count($a) <= 0 && count($classErrorArr) < $x)
		array_push($classErrorArr, "Unable to find class matching time parameters");
	if(count($a) > 0){
		array_push($courses, $a);
		array_push($classErrorArr, "");
	}
}

$scheduler = new scheduler($courses);
$schedule = $scheduler->getSchedule(1);


//if($c == 1) //then there was no input last time
?>
<html>
<head>
<title>Course Planner</title>
<link rel="stylesheet" type="text/css" href="tableStyle.css">
<link rel="stylesheet" type="text/css" href="design/stylesheet.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>
var counter = <?php echo $c-1==0?1:$c-1 ?>;
var limit = 6;
function addInput(divName){
	if (counter == limit)  {
		alert("There is a limit of " + counter + " classes because I'm lazy");
	}
	else {
		var newdiv = document.createElement('div');
		newdiv.innerHTML = "<br />Class " + (counter + 1) + ": <input type='text' name='course"+(counter+1)+"'><br />";
		newdiv.id = divName + (counter+1);
		document.getElementById(divName + counter).appendChild(newdiv);
		counter++;
	}
}
var totalSchedules = <?php echo count($scheduler->getAllCombinations()); ?>;
$(document).ready(function(){
	for(var i = 1; i < totalSchedules; i++){
		console.log("test" + i);
		$('#table' + i).hide();
	}
	$("#currentSchedule").change(function(){
		for(var i = 0; i < totalSchedules; i++){
			$("#table" + i).hide();
		}
		$("#table" + $("#currentSchedule").val()).show();
	});
});

function openNewTab(url){
	var win = window.open(url, '_blank');
}
</script>
</head>
<body>
<div class="header">
<div class="header-content">
<a id="logo" href="http://utdallas.edu">UT Dallas</a>
<h2 style="float:right; display:inline-block; text-align:center; padding-top:26px;">Course Scheduler!</h2>
</div>
</div>




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
<input type="checkbox" name="closed" value="include" <?php echo isset($_POST['closed'])?'checked':''?>> Include closed classes  <br /><br />
Class starts after: 
<select name="early">
<?php
echo '<option value="1">-</option>';
for($x = 6; $x < 12; $x++){
	if($_POST['early'] == $x)
		echo '<option value="'.$x.'" selected="selected">'.$x.' AM</option>';
	else
		echo '<option value="'.$x.'">'.$x.' AM</option>';
}	
?>
</select>
<br />
Class ends before:  
<select name="late"> 
<?php
echo '<option value="23">-</option>';
for($x = 15; $x < 22; $x++){
	if($_POST['late'] == $x)
		echo '<option value="'.$x.'" selected="selected">'.($x-12).' PM</option>';
	else
		echo '<option value="'.$x.'">'. ($x-12) .' PM</option>';
}	
?>
</select>
</form>
<br />
<br />
<?php
if(count($scheduler->getAllCombinations()) == 0) echo "<!--";
?>
<div id="combo" style="position:absolute; bottom:0">
	Found a total of <?php echo count($scheduler->getAllCombinations())?> possible schedules. Currently displaying combination 
<select id="currentSchedule">
<?php
for($x = 0; $x < count($scheduler->getAllCombinations()); $x++)
	echo '<option value="' . ($x) . '">'. ($x+1) .'</option>"';
?>
</select><br /><br />
<?php
if(count($scheduler->getAllCombinations()) == 0) echo "-->";
?>
</div>
</div> <!-- ending colinternal -->
</div> <!-- ending edge-inline-block -->
</div> <!-- ending colmas -->
</div> <!--ending center div-->

<div class="center">

<!--output the calendar-->
<?php
$combos = $scheduler->getAllCombinations();
for($x = 0; $x < count($combos); $x++){
	echo generateCalendar($combos[$x], $x);
}
if(count($combos) == 0) echo generateCalendar(array(), 0);

?>
</div>

<div class="footer">
<div class="footertext">
Don't look at me!
</div>
</div>

</body>

</html>
