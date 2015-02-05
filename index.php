<?php
//index.php
//

include_once('courseScraper.php');
include_once('calendarGenerator.php');

$c = 1;
while($_POST['course' . $c] != NULL){
	$c++;
}

$courses = array(); //this will be an array of arrays
$classErrorArr = array();
for($x = 1; $x < $c; $x++){
	$a = search($_POST['course' . $x]);
	if(count($a) <= 0 && count($classErrorArr) < $x){
		array_push($classErrorArr, "Unable to find listed course");
	}
	if(!isset($_POST['closed'])){
		$a = removeClosedCourses($a);
		if(count($a) <= 0 && count($classErrorArr) < $x)
			array_push($classErrorArr, "All instances of the listed course are full");
	}
	$a = removeClassesBeforeOrAfter($_POST['early'], $_POST['late'], $a);
	if(count($a) <= 0 && count($classErrorArr) < $x)
		array_push($classErrorArr, "Unable to find class matching time parameters");
	if(count($a) > 0){
		array_push($courses, $a);
		array_push($classErrorArr, "");
	}
}
$schedule = generateSchedule($courses);



//if($c == 1) //then there was no input last time
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="tableStyle.css">

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
</script>
</head>
<body>

<h2>Course planner!TESTTEST</h2>

<p>Enter the classes you would like to take in the form and then press submit. The calendar below will update.</p>
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
<input type="checkbox" name="closed" value="include" <?php echo isset($_POST['closed'])?'checked':''?>> Include closed classes  <br /><br />
Don't allow classes before: 
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
or after:  
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
<input type="button" value="Add another course" onClick="addInput('dynamicInput');">
<input type="submit" value="Submit">
</form>

<?php

echo generateCalendar($schedule);

?>

</body>

</html>
