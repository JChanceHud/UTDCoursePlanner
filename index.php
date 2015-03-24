<!--//index.php
//
//
-->

<html>
<head>
<title>Course Planner</title>
<meta charset="UTF-8">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script> 
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>

<link rel="stylesheet" type="text/css" href="tableStyle.css" />
<link rel="stylesheet" type="text/css" href="design/stylesheet.css" />
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
<span id="scheduleLoadTime" style="font-weight:bold;">Load time</span>
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
<input type="radio" id="days" name="dayClasstime" value="-1" onChange="getNewSchedules()"> Maximize class per day (fewer days of class)  <br /><br />
<input id="customTimeslot" type="button" value="Add custom busy time" title="Specifies a time period where classes will not be scheduled. Good if you have work or other commitments." />
</form>
<div id="loading" style="font-weight:bold; position:absolute; bottom:0;">
<p>Loading....</p>
</div>
</div> <!-- ending colinternal -->
</div> <!-- ending edge-inline-block -->
</div> <!-- ending colmas -->
</div> <!--ending center div-->

<div id="calendar" class="center">
<!--output the calendar-->
<!--<input id="clickMe" type="button" value="clickme" onclick="calendarDownload();" />-->
<div id="customTimeslotDialog" style="background: #FFF">
Day: <select name="dialogDay" id="dialogDay">
	<option value="0">Monday</option>
	<option value="1">Tuesday</option>
	<option value="2">Wednesday</option>
	<option value="3">Thursday</option>
	<option value="4">Friday</option>
	<option value="5">Saturday</option>
</select><br /><br />
Start time: <select name="startTime" id="dialogStartTime">
	<option value="7">7 AM</option>
	<option value="8">8 AM</option>
	<option value="9" selected="selected">9 AM</option>
	<option value="10">10 AM</option>
	<option value="11">11 AM</option>
	<option value="12">12 PM</option>
	<option value="13">1 PM</option>
	<option value="14">2 PM</option>
	<option value="15">3 PM</option>
	<option value="16">4 PM</option>
	<option value="17">5 PM</option>
	<option value="18">6 PM</option>
	<option value="19">7 PM</option>
	<option value="20">8 PM</option>
	<option value="21">9 PM</option>
	<option value="22">10 PM</option>
</select><br /><br />
End time: <select name="endTime" id="dialogEndTime">
	<option value="7">7 AM</option>
	<option value="8">8 AM</option>
	<option value="9">9 AM</option>
	<option value="10">10 AM</option>
	<option value="11">11 AM</option>
	<option value="12">12 PM</option>
	<option value="13">1 PM</option>
	<option value="14">2 PM</option>
	<option value="15" selected="selected">3 PM</option>
	<option value="16">4 PM</option>
	<option value="17">5 PM</option>
	<option value="18">6 PM</option>
	<option value="19">7 PM</option>
	<option value="20">8 PM</option>
	<option value="21">9 PM</option>
	<option value="22">10 PM</option>
</select><br /><br />
</div>


<table id="calendar" class="calendar" style="display: table;">
    <thead>
        <tr id="">
            <th>&nbsp;</th>
            <th width="16.6%">Monday</th>
            <th width="16.6%">Tuesday</th>
            <th width="16.6%">Wednesday</th>
            <th width="16.6%">Thursday</th>
			<th width="16.6%">Friday</th>
			<th width="16.6%">Saturday</th>
        </tr>
    </thead>
    <tbody id="mainTableBody">
    		<tr id="0700">
    			<td id="time">07:00</td>
            </tr>
    		<tr id="0730">
    			<td id="time">07:30</td>
            </tr>
    		<tr id="0800">
    			<td id="time">08:00</td>
            </tr>
    		<tr id="0830">
    			<td id="time">08:30</td>
            </tr>
    		<tr id="0900">
				<td id="time">09:00</td>
            </tr>
    		<tr id="0930">
				<td id="time">09:30</td>
            </tr>
    		<tr id="1000">
				<td id="time">10:00</td>
            </tr>
    		<tr id="1030">
				<td id="time">10:30</td>
            </tr>
    		<tr id="1100">
				<td id="time">11:00</td>
            </tr>
    		<tr id="1130">
				<td id="time">11:30</td>
            </tr>
    		<tr id="1200">
				<td id="time">12:00</td>
            </tr>
    		<tr id="1230">
				<td id="time">12:30</td>
            </tr>
    		<tr id="1300">
				<td id="time">13:00</td>
            </tr>
    		<tr id="1330">
				<td id="time">13:30</td>
            </tr>
    		<tr id="1400">
				<td id="time">14:00</td>
            </tr>
    		<tr id="1430">
				<td id="time">14:30</td>
            </tr>
    		<tr id="1500">
				<td id="time">15:00</td>
            </tr>
    		<tr id="1530">
				<td id="time">15:30</td>
            </tr>
    		<tr id="1600">
				<td id="time">16:00</td>
            </tr>
    		<tr id="1630">
				<td id="time">16:30</td>
            </tr>
    		<tr id="1700">
				<td id="time">17:00</td>
            </tr>
    		<tr id="1730">
				<td id="time">17:30</td>
            </tr>
    		<tr id="1800">
				<td id="time">18:00</td>
            </tr>
    		<tr id="1830">
				<td id="time">18:30</td>
            </tr>
    		<tr id="1900">
				<td id="time">19:00</td>
            </tr>
    		<tr id="1930">
				<td id="time">19:30</td>
            </tr>
    		<tr id="2000">
				<td id="time">20:00</td>
            </tr>
    		<tr id="2030">
				<td id="time">20:30</td>
            </tr>
    		<tr id="2100">
				<td id="time">21:00</td>
            </tr>
    		<tr id="2130">
				<td id="time">21:30</td>
            </tr>
    </tbody>
</table>

</div>

<div class="footer">
<div class="footertext">
Current version: 1.2.3 | Copyright © 2015 Chance Hudson <br /> <a href="https://github.com/JChanceHud/UTDCoursePlanner">This site is open source!</a>
</div>
</div>

</body>

</html>

