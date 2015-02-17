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
<script src="coursescheduler.js"></script>
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
Enter the classes you would like to take.
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
<button onClick="changeSchedule(-1);" type="button">Previous</button> <button onClick="changeSchedule(1);" type="button">Next</button> <button onClick="showAllSchedules(0)" type="button">Show all</button> 
<br /><br />
</div>


</div>
</div>
<div class="center-inline-box">
<div class="colhead"><div class="colheadinternal">Classes</div></div>
<div class="colinternal">
<div id="classes">
</div>

<br />

<input type="text" class="classInput" id="classInput" placeholder="CS2336" value="">
<input type="button" value="Add" onClick="searchForClass()">
<br /><br />
<span id="classInputError" style="color:red;"></span>
</div>
</div>
<form action="/" method="POST" id="courseForm">
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
Current version: 1.2.2 | Copyright © 2015 Chance Hudson <br /> <a href="https://github.com/JChanceHud/UTDCoursePlanner">This site is open source!</a>
</div>
</div>

</body>

</html>

