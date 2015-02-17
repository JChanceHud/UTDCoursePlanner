<!--//index.php
//
//
-->

<html>
<head>
<title>Course Planner</title>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="tableStyle.css">
<link rel="stylesheet" type="text/css" href="design/stylesheet.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>
var counter = 0;
var limit = 6;
function addInput(count){
	var divName = "dynamicInput";
	for(var x = 0; x < count; x++)
		if (counter == limit)  {
			alert("There is a limit of " + counter + " classes");
			break;
		}
		else {
			var newdiv = document.createElement('div');
			newdiv.innerHTML = "Class " + (counter + 1) + ": <input type='text' name='course"+(counter+1)+"' id='course"+(counter+1)+"' class='courseInput'><br /><br />";
			newdiv.id = divName + (counter+1);
			var previousInput = document.getElementById(divName + counter);
			previousInput.parentNode.insertBefore(newdiv, previousInput.nextSibling);
			counter++;
		}
}

var timeLimitEarly = 0;
var timeLimitLate = 23;
var showSchedule = 0;
var allowClosedClasses = 0;
var timeBetweenClasses = 0;
var dayClasstime = 0;
var $_GET = {};

$(document).ready(function(){
	loadGetParameters();

	$("#currentSchedule").change(function(){
		$(".calendar").each(function(c){
			//iterate through each calendar and set hidden
			$(this).hide();
		});
		$("#table" + $("#currentSchedule").val()).show();
	});
	
	//handle form being submitted
	jQuery('#courseForm').submit( function(event) {
		event.preventDefault();
		var post = $.post("generateSchedules.php", $("#courseForm").serialize());
		post.done(function(data){
			updateSchedules(data);
		});
		return false;
	});

	setFieldValues();
});

function setFieldValues(){
	//sets field values based on get parametrs
	$("#loading").hide();
	$("#combo").hide();
	$("#early").val(0); //set default values
	$("#late").val(23);
	var $radios = $('input:radio[name=dayClasstime]');
	$radios.filter('[value=0]').prop('checked', true);
	addInput(1);
	if(typeof $_GET['classes'] === 'undefined')
		return; //if not provided classes then don't continue
	var classes = $_GET['classes'].split(":");
	var classCount = classes.length;
	addInput(classCount-1);
	for(var x = 0; x < classCount; x++){
		$("#course" + (x+1)).val(classes[x]); //update text field values
	}
	if(typeof $_GET['allowClosed'] !== 'undefined' && $_GET['allowClosed'] === "true")
		$("#allowClosed").prop("checked", true);

	if(typeof $_GET['timeBetweenClasses'] !== 'undefined' && $_GET['timeBetweenClasses'] === "true")
		$("#timeBetweenClasses").prop("checked", true);
	
	if(typeof $_GET['dayClasstime'] !== 'undefine'){
		//should be set to either -1, 0, or 1
		var $radios = $('input:radio[name=dayClasstime]');
		$radios.filter('[value='+$_GET['dayClasstime']+']').prop('checked', true);
	}

	if(typeof $_GET['early'] !== 'undefined')
		$('#early').val($_GET['early']);
	if(typeof $_GET['late'] !== 'undefined')
		$('#late').val($_GET['late']);

	$("#courseForm").submit();

	$("#currentSchedule").val(0);
	$("#currentSchedule").change();
}

function getPermalink(){
	var courseString = "classes=";
	$(".courseInput").each(function(i){
		if($(this).attr('id') != 'course1')
			courseString += ":";
		courseString += $(this).val();
	});
	var allowClosed = "&allowClosed=" + $("#allowClosed").prop("checked");
	var timeBetweenClasses = "&timeBetweenClasses=" + $("#timeBetweenClasses").prop("checked");
	var dayClasstime = "&dayClasstime=" + $('input[name=dayClasstime]:checked').val();
	var late = "&late=" + $('#late').val();
	var early = "&early=" + $('#early').val();
	var permalink = "http://utdcourseplanner.ddns.net/index.php?" + courseString + allowClosed + timeBetweenClasses + dayClasstime + late + early;
	return permalink;
}

function showAllSchedules(count){
	if(count >= $(".calendar").length)
		return;
	$("#currentSchedule").val(count);
	$("#currentSchedule").change();
	count++;
	setTimeout(function(){ showAllSchedules(count); }, 100);
}

function updateSchedules(data){
	$("#calendar").html(data); //replace the calednar div contents
	var scheduleCount = $("#scheduleCount").val(); //get number of schedules

	//update schedule selector
	var roundedCount = scheduleCount>100?100:scheduleCount;
	str = "Found a total of " + scheduleCount + " possible schedules. " + ((scheduleCount!=roundedCount)? "Limiting number of displayed schedules to 100.":"") + " Currently displaying combination";
	$("#comboText").html(str);
	$("#currentSchedule").empty();
	for(var x = 0; x < roundedCount; x++){
		$('#currentSchedule').append("<option value="+x+">"+ (x+1) +"</option>"); 
	}
	$('#currentSchedule').val(0);
	$('#currentSchedule').change();
	$("#combo").show();
	

	//update permalink
	$("#permalink").html("PERMALINK");
	$("#permalink").attr("href", getPermalink());
	$("#loading").hide();
}


function loadGetParameters(){
	document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
		function decode(s) {
			return decodeURIComponent(s.split("+").join(" "));
		}
		$_GET[decode(arguments[1])] = decode(arguments[2]);
	});
}

function getNewSchedules(){
	$("#loading").show();
	$("#courseForm").submit();
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
Sample input: CS2336
<br /><br />
Classes that are red are closed. Click on a class to open the course in the UTD coursebook.
<br /><br /><br />
<!-- <input type="text" id="permalink"> -->
<a id="permalink" href=""></a>


<div id="combo" style="position:absolute; bottom:0;">
<span id="comboText"></span>
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
<form action="/" method="POST" id="courseForm">
<div id="dynamicInput0" style="display:none;"></div>

<br />
<input type="button" value="Add another course" onClick="addInput(1);">
<input type="submit" value="Submit">
</div>
</div>
<div class="edge-inline-box">
<div class="colhead"><div class="colheadinternal">settings</div></div>
<div class="colinternal">
<input type="checkbox" id="allowClosed" name="closed" value="1" onChange="getNewSchedules()"> Include closed classes  <br /><br />
Class starts after: 
<select name="early" id="early" onChange="getNewSchedules()">
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
<select name="late" id="late" onChange="getNewSchedules()">
	<option value="23">-</option>
	<option value="15">3 PM</option>
	<option value="16">4 PM</option>
	<option value="17">5 PM</option>
	<option value="18">6 PM</option>
	<option value="19">7 PM</option>
	<option value="20">8 PM</option>
	<option value="21">9 PM</option>
</select><br /><br />
<input type="checkbox" id="timeBetweenClasses" name="timeBetweenClasses" value="1" onChange="getNewSchedules()"> Minimize time between classes  <br /><br />
Class distribution<br />
<input type="radio" id="days" name="dayClasstime" value="0" onChange="getNewSchedules()"> Do not weight  <br />
<input type="radio" id="days" name="dayClasstime" value="1" onChange="getNewSchedules()"> Minimize class per day (more days of class)  <br />
<input type="radio" id="days" name="dayClasstime" value="-1" onChange="getNewSchedules()"> Maximize class per day (fewer days of class)  <br />
</form>
<div id="loading" style="position:absolute; bottom:0;">
<p>Loading....</p>
</div>
</div> <!-- ending colinternal -->
</div> <!-- ending edge-inline-block -->
</div> <!-- ending colmas -->
</div> <!--ending center div-->

<div id="calendar" class="center">

<!--output the calendar-->
<!--
-->

</div>

<div class="footer">
<div class="footertext">
Current version: 1.2.1 | Copyright © 2015 Chance Hudson <br /> <a href="https://github.com/JChanceHud/UTDCoursePlanner">This site is open source!</a>
</div>
</div>

</body>

</html>

