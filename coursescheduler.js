var $_GET = {};
var classes = [];
var baseURL = "utdcourseplanner.ddns.net";
var currentSchedules;

function getClassesString(){
	var r = "";
	for(var x = 0; x < classes.length; x++){
		r += classes[x];
		if(x != classes.length-1)
			r += ":";
	}
	return r;
}

function getNewSchedules(){
	$("#loading").show();
	$("#courseForm").submit();
}

$(document).ready(function(){
	resetCalendar();
	loadGetParameters();
	$("#currentSchedule").change(function(){
		displaySchedule($("#currentSchedule").val());
	});
	
	//handle form being submitted
	jQuery('#courseForm').submit( function(event) {
		event.preventDefault();
		var post = $.post("generateSchedules.php?classes=" + getClassesString(), $("#courseForm").serialize());
		post.done(function(data){
			updateSchedules(data);
		});
		return false;
	});

	setupClassInput();
	setFieldValues();
	$( "#classInput" ).autocomplete({
		source:function( request, response ) {
			//request.term //current search value
			$.ajax({
				url: "getAutoCompleteResults.php?term="+request.term,
				dataType:"json",
				success: function(data) {
					response(data);
				}
			});
		},
		delay:300,
		select: function (event, ui) {
			searchForClass();
		},
		minLength: 2
	});
	$( document ).tooltip({
		track: true
	});
});

function setFieldValues(){
	//sets field values based on get parametrs
	$("#loading").hide();
	$("#combo").hide();
	$("#early").val(0); //set default values
	$("#late").val(23);
	var $radios = $('input:radio[name=dayClasstime]');
	$radios.filter('[value=0]').prop('checked', true);
	if(typeof $_GET.classes === 'undefined')
		return; //if not provided classes then don't continue
	var c = $_GET.classes.split(":");
	var classCount = c.length;
	for(var x = 0; x < classCount; x++){
		searchForClass(c[x], false);
	}
	if(typeof $_GET.allowClosed !== 'undefined' && $_GET.allowClosed === "true")
		$("#allowClosed").prop("checked", true);

	if(typeof $_GET.timeBetweenClasses !== 'undefined' && $_GET.timeBetweenClasses === "true")
		$("#timeBetweenClasses").prop("checked", true);
	
	if(typeof $_GET.dayClasstime !== 'undefined'){
		//should be set to either -1, 0, or 1
		$radios.filter('[value='+$_GET.dayClasstime+']').prop('checked', true);
	}

	if(typeof $_GET.early !== 'undefined')
		$('#early').val($_GET.early);
	if(typeof $_GET.late !== 'undefined')
		$('#late').val($_GET.late);
	
	checkLoaded(c.length, 0); //wait until we load the class info to generate schedules
	$("#currentSchedule").val(0);
	$("#currentSchedule").change();
}

function checkLoaded(target, testCount){
	if(classes.length == target || testCount > 10)
		getNewSchedules();
	else
		setTimeout(function(){checkLoaded(target, ++testCount);}, 100);
}

function getPermalink(){
	var courseString = "classes="+getClassesString();
	var allowClosed = "&allowClosed=" + $("#allowClosed").prop("checked");
	var timeBetweenClasses = "&timeBetweenClasses=" + $("#timeBetweenClasses").prop("checked");
	var dayClasstime = "&dayClasstime=" + $('input[name=dayClasstime]:checked').val();
	var late = "&late=" + $('#late').val();
	var early = "&early=" + $('#early').val();
	var permalink = "http://"+baseURL+"/index.php?" + courseString + allowClosed + timeBetweenClasses + dayClasstime + late + early;
	return permalink;
}

function showAllSchedules(count){
	if(count >= currentSchedules.combos.length)
		return;
	$("#currentSchedule").val(count);
	$("#currentSchedule").change();
	count++;
	setTimeout(function(){ showAllSchedules(count); }, 100);
}

function changeSchedule(val){
	var current = parseInt($("#currentSchedule").val());
	current += parseInt(val);
	if(current < 0 || current >= $("#currentSchedule option").length)
		return;
	$("#currentSchedule").val(current);
	$("#currentSchedule").change();
}

function updateSchedules(data){
//	$("#calendar").html(data);
	currentSchedules = JSON.parse(data);
	var scheduleCount = currentSchedules.combos.length;
	$("#scheduleLoadTime").html("Generated " + scheduleCount + " schedule" + (scheduleCount>1?"s":"") + " in " + currentSchedules.generationTime + " seconds.");

	//update schedule selector
	str = "Found a total of " + scheduleCount + " possible schedules. " + " Currently displaying combination";
	$("#comboText").html(str);
	$("#currentSchedule").empty();
	for(var x = 0; x < scheduleCount; x++){
		$('#currentSchedule').append("<option value="+x+">"+ (x+1) +"</option>"); 
	}
	$('#currentSchedule').val(0);
	$('#currentSchedule').change();
	$("#combo").show();

	//update permalink
	$("#permalink").html("PERMALINK");
	$("#permalink").attr("href", getPermalink());
	//window.history.pushState("","",getPermalink());
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


function openNewTab(url){
	var win = window.open(url, '_blank');
}

//handle adding class
//
//

function setupClassInput(){
	$('#classInput').keypress(function (e) {
		var key = e.which;
		if(key == 13)  // the enter key code
			searchForClass();
	}); 
}

function searchForClass(searchTerm, getNew){
	if(searchTerm === undefined)
		searchTerm = $("#classInput").val();
	var search = $.get("getClassInfo.php?class=" + searchTerm);
	search.done(function(data){
		var j;
		try {
			j = JSON.parse(data);
		} catch (e) {
			alert(e);
			console.log(j);
			return;
		}
		if(j[1] > 0) {//class was found
			var a = addClass(j[0], j[1], j[2]);
			if(getNew === undefined)
				getNewSchedules();
			$("#classInputError").html(a);
		}
		else{
			$("#classInputError").html("Class not found");
		}
		$("#classInput").val("");
	});
}

function addClass(className, sectionCount, openCount){
	if(classes.length >= 6)
		return "Max classes added";
	for(var x = 0; x < classes.length; x++){
		if(classes[x] === className)
			return "Class already added";
	}
	classes.push(className);
	var classDiv = $(document.createElement('div'));
	classDiv.attr("class", "class");
	classDiv.attr("id", className);
	var classSpan = $(document.createElement('span'));
	classSpan.attr("class", "classSpan");
	classSpan.append(className);
	classDiv.append(classSpan);
	classDiv.append(" " + sectionCount + " sections, " + openCount + " open");
	var deleteButton = $(document.createElement('button'));
	deleteButton.attr("onClick", "removeClass('" + className + "');");
	deleteButton.append("Delete");
	classDiv.append("  ");
	classDiv.append(deleteButton);
	$("#classes").append(classDiv);
	return "";
}

function removeClass(className){
	$("#" + className).remove();
	var index = classes.indexOf(className);
	classes.splice(index, 1);
	getNewSchedules();
}


// calendar functions
//----------------------------------------------------------------------------------------


function displaySchedule(scheduleNum) {
	if (currentSchedules === undefined || scheduleNum >= currentSchedules.combos.length) {
		return false;
	}
	resetCalendar();
	var courseNumbers = currentSchedules.combos[scheduleNum];
	var courses = [];
	for(var x = 0; x < courseNumbers.length; x++) {
		courses[x] = currentSchedules.courses[courseNumbers[x]];
	}
	for (x = 0; x < courses.length; x++) { //iterate through each class in schedule
		for (var y = 0; y < courses[x].classTimes.length; y++) { //iterate through the classtimes for given class
			var classTD = $(document.createElement('td'));
			classTD.attr("class", "has-events");
			classTD.attr("rowspan", getClassLength(courses[x].classTimes[y]));
			classTD.attr("onclick", "openNewTab('http://coursebook.utdallas.edu/" + courses[x].classID + "')");
			classTD.attr("style", (courses[x].classIsOpen == 1)?"":"background-color:red;");
			
			var topLine = '<span class="title">' + courses[x].classSection + " | " + courses[x].classRoom + '</span>';
			var middleLine = '<span class="lecturer"><a href="http://coursebook.utdallas.edu/' + courses[x].classID + '" target="_blank" data-ytta-id="-">' + courses[x].classInstructor + "</a></span>"; 
			var bottomLine = '<span class="time">' + getTimeString(courses[x].classTimes[y].startTime, ":") + " - " + getTimeString(courses[x].classTimes[y].endTime, ":") + '</span>';

			classTD.append(topLine);
			classTD.append(middleLine);
			classTD.append(bottomLine);

			var t = {hour:courses[x].classTimes[y].startTime.hour, min:courses[x].classTimes[y].startTime.min, day:courses[x].classTimes[y].day};
			$("#" + getTimeString(t)).children().eq(t.day+1).replaceWith(classTD);

			//adjust following rows
			for(var z = 0; z < getClassLength(courses[x].classTimes[y]) - 1; z++){
				t = addMinsToTime(t, 30);
				$("#" + getTimeString(t)).children().eq(t.day+1).replaceWith('<div class="dummy"></div>');
			}
		}
	}
	$("#mainTableBody").find(".dummy").remove();
}

function addMinsToTime(time, mins) {
	time.min += mins;
	if (time.min >= 60) {
		time.hour += time.min / 60;
		time.min -= 60*(time.min / 60);
	}
	return time;
}

function getTimeString(time, separator) {
	if (separator === undefined)
		separator = "";
	return ((time.hour > 9)?"":"0") + time.hour + separator + ((time.min > 9)?"":"0") + time.min;
}

function getTimeInt(time) { //pass an object with properties hour and min
	var scaledMin = (time.min * 100) / 60;
	return parseInt(((time.hour > 9)?"":"0") + time.hour + ((scaledMin > 9)?"":"0") + scaledMin);
}

function getClassLength(classTime) { //pass object from json
	//returns the number of rows the class occupies
	return Math.ceil((getTimeInt(classTime.endTime) - getTimeInt(classTime.startTime)) / 50);
}

function resetCalendar() {
	//emptyTD is now good to go
	var times = $("#mainTableBody").children();
	$("#mainTableBody").find("td").not('#time').remove();
	$("#mainTableBody").find(".dummy").remove();

	for(var x = 0; x < 5; x++)
		$("#mainTableBody").children().append('<td class="no-events" rowspan="1"><span style="width:0px;"></span></td>');
}
