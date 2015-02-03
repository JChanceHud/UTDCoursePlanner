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
$noClassesFoundArr = array();
for($x = 1; $x < $c; $x++){
	$a = search($_POST['course' . $x]);
	if(!isset($_POST['closed']))
		$a = removeClosedCourses($a);
	$a = removeClassesBeforeOrAfter($_POST['early'], $_POST['late'], $a);
	if(count($a) > 0){
		array_push($courses, $a);
		array_push($noClassesFoundArr, 0);
	}
	else
		array_push($noClassesFoundArr, 1);
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
		newdiv.innerHTML = "Class " + (counter + 1) + " <div></div><input type='text' name='course"+(counter+1)+"'><br /><br />";
		document.getElementById(divName).appendChild(newdiv);
		counter++;
	}
}
</script>
</head>
<body>

<h2>Course planner!</h2>

<p>Enter the classes you would like to take in the form and then press submit. The calendar below will update.</p>
<form action="index.php" method="POST">
<?php
if($c == 1){
echo '<div id="dynamicInput">
Class 1:<div></div>
<input type="text" name="course1" value=""><div id="course1"></div>
<br>
</div>';
}
else
for($x = 1; $x < $c; $x++){
echo '<div id="dynamicInput">
	Class '.$x.': <div id="course'.$x.'" style="color:red">';
if($noClassesFoundArr[$x-1] == 1) echo "Was not able to find a course";
echo '
<input type="text" name="course'.$x.'" value="'.$_POST['course' . $x].'">
</div>
<br>
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
