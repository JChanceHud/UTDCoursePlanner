var $_GET = {};
var classes = [];
var baseURL = "utdcourseplanner.ddns.net";
var currentSchedules;
var customTimeslots;

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
	customTimeslots = [];
	resetCalendar();
	loadGetParameters();
	$("#currentSchedule").change(function(){
		displaySchedule($("#currentSchedule").val());
	});
	
	//handle form being submitted
	jQuery('#courseForm').submit( function(event) {
		event.preventDefault();
		var post = $.post("generateSchedules.php?classes=" + getClassesString() + "&timeslots=" + encodeURIComponent(JSON.stringify(customTimeslots)), $("#courseForm").serialize());
		post.done(function(data){
			updateSchedules(data);
		});
		return false;
	});

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
			searchForClass(ui.item.value);
		},
		minLength: 2
	});

	setupClassInput();
	setFieldValues();
	$("#customTimeslotDialog").dialog({
		autoOpen: false,
		show: {
			effect: "fade",
			duration: 300 
		},
		hide: {
			effect: "fade",
			duration: 300 
		},
		modal: true,
		buttons: {
			"Add timeslot": function() {
				addCustomTimeslot();
				getNewSchedules();
				$(this).dialog( "close" );
			},
			Cancel: function() {
				$(this).dialog( "close" );
			}
		}
	});
	$("#customTimeslot").click(function() {
		$("#customTimeslotDialog").dialog("open");
		$('.ui-widget-overlay').css('background', 'black');
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
	//$("#calendar").html(data);
	currentSchedules = JSON.parse(data);
	var scheduleCount = currentSchedules.combos.length;
	$("#scheduleLoadTime").html("Generated " + scheduleCount + " schedule" + (scheduleCount==1?"":"s") + " in " + currentSchedules.generationTime + " seconds.");

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
	$("#classInput").autocomplete("close");
	if(searchTerm === undefined)
		searchTerm = $("#classInput").val();
	var search = $.get("getClassInfo.php?class=" + searchTerm);
	search.done(function(data){
		var j;
		try {
			j = JSON.parse(data);
		} catch (e) {
			alert(e + data);
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
	resetCalendar();
	var courseNumbers = [];
	if (currentSchedules !== undefined && currentSchedules.combos.length > 0) {
		courseNumbers = currentSchedules.combos[scheduleNum];
	}
	var courses = [];
	for(var x = 0; x < courseNumbers.length; x++) {
		courses[x] = currentSchedules.courses[courseNumbers[x]];
	}
	//display the custom timeblocks
	for (x = 0; x < customTimeslots.length; x++) {
		var classTD = $(document.createElement('td'));
		classTD.attr("class", "has-events timeblock");
		classTD.attr("rowspan", getClassLength(customTimeslots[x]));
		// classTD.attr("onclick", "openNewTab('http://coursebook.utdallas.edu/" + courses[x].classID + "')");
		// if (courses[x].classIsOpen != 1)
			classTD.attr("style", "background-color:purple;");
		classTD.attr("classIndex", x);
		
		var topLine = '<span class="title">Busy time</span>';
		//var middleLine = '<span class="lecturer">' + courses[x].classInstructor + "</span>"; 
		var bottomLine = '<span class="time">' + getTimeString(customTimeslots[x].startTime, ":") + " - " + getTimeString(customTimeslots[x].endTime, ":") + '</span>';

		classTD.append(topLine);
		// classTD.append(middleLine);
		classTD.append(bottomLine);

		var t = {hour:customTimeslots[x].startTime.hour, min:customTimeslots[x].startTime.min, day:customTimeslots[x].day};
		var roundedStartTime = roundTime(t);
		$("#" + getTimeString(roundedStartTime)).children().eq(t.day+1).replaceWith(classTD);

		//adjust following rows
		for(var z = 0; z < getClassLength(customTimeslots[x]) - 1; z++){
			roundedStartTime = addMinsToTime(roundedStartTime, 30);
			$("#" + getTimeString(roundedStartTime)).children().eq(t.day+1).replaceWith('<div class="dummy"></div>');
		}
	}
	for (x = 0; x < courses.length; x++) { //iterate through each class in schedule
		for (var y = 0; y < courses[x].classTimes.length; y++) { //iterate through the classtimes for given class
			var classTD = $(document.createElement('td'));
			classTD.attr("class", "has-events");
			classTD.attr("rowspan", getClassLength(courses[x].classTimes[y]));
			classTD.attr("onclick", "openNewTab('http://coursebook.utdallas.edu/" + courses[x].classID + "')");
			if (courses[x].classIsOpen != 1)
				classTD.attr("style", "background-color:red;");
			classTD.attr("classIndex", x);
			//classTD.attr("title", courses[x].classTitle+"<br /><br />Click to show coursebook listing");
			
			var topLine = '<span class="title">' + courses[x].classSection + " | " + courses[x].classRoom + '</span>';
			var middleLine = '<span class="lecturer">' + courses[x].classInstructor + "</span>"; 
			var bottomLine = '<span class="time">' + getTimeString(courses[x].classTimes[y].startTime, ":") + " - " + getTimeString(courses[x].classTimes[y].endTime, ":") + '</span>';

			classTD.append(topLine);
			classTD.append(middleLine);
			classTD.append(bottomLine);

			var t = {hour:courses[x].classTimes[y].startTime.hour, min:courses[x].classTimes[y].startTime.min, day:courses[x].classTimes[y].day};
			var roundedStartTime = roundTime(t);
			$("#" + getTimeString(roundedStartTime)).children().eq(t.day+1).replaceWith(classTD);

			//adjust following rows
			for(var z = 0; z < getClassLength(courses[x].classTimes[y]) - 1; z++){
				roundedStartTime = addMinsToTime(roundedStartTime, 30);
				$("#" + getTimeString(roundedStartTime)).children().eq(t.day+1).replaceWith('<div class="dummy"></div>');
			}
		}
	}
	$("#mainTableBody").find(".dummy").remove();
	//mouseover for class blocks
	$(".has-events").mouseenter(function(event){
		if ($(this).attr("class") !== "has-events")
			return;
		var c = courses[$(this).attr("classindex")];
		var content = "";
		content += c.classTitle+"<br />";
		content += c.classIsOpen==1?"":'<span style="font-weight:bold;">Section is full!</span><br />';
		content += "Click to show coursebook listing";
		createPopup(event, content);
	}).on("mousemove", function(event){
		positionPopup(event);	
	}).mouseleave(function(event){
		if (!$(this).hasClass("has-events"))
			return;
		$(".popup").hide();
	});
	//mouseover for custom busy periods
	$(".has-events").mouseenter(function(event){
		if (!$(this).hasClass("timeblock"))
			return;
		var content = "";
		content += "Busy time: no classes will be scheduled here<br />Click to remove";
		createPopup(event, content);
	}).on("mousemove", function(event){
		positionPopup(event);	
	}).mouseleave(function(event){
		if (!$(this).hasClass("has-events"))
			return;
		$(".popup").hide();
	}).click(function(){
		$(".popup").hide();
		$(this).remove();
		customTimeslots.splice($(this).attr("classIndex"), 1);
		getNewSchedules();
	});
}

function roundTime(time) {
	var min = (time.min > 45 || time.min < 15)?0:30;
	var modifier = (time.min>45)?1:0;
	var hour = time.hour + modifier;
	return {hour:hour, min:min};
}

function createPopup(event, html){
	if ($(".popup").length === 0) {
		var popup = $(document.createElement('div'));
		popup.attr("class", "popup");
		var popupInternal = $(document.createElement('div'));
		popupInternal.attr("class", "popupInternal");
		popup.append(popupInternal);
		popup.appendTo('body');
	}
	var p = $(".popup");
	p.show();
	var internal = p.children().eq(0);
	internal.html(html);
	positionPopup(event);
}

function positionPopup(event){
	var xPos = event.pageX + 10;
    var yPos = event.pageY + 10;
    $('div.popup').css({'position': 'absolute', 'top': yPos, 'left': xPos});
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

	for(var x = 0; x < 6; x++)
		$("#mainTableBody").children().append('<td class="no-events" rowspan="1"><span style="width:0px;"></span></td>');
}

//custom timeslots
//-------------------------------------------------------------------------
//

function addCustomTimeslot() {
	if ($("#customTimeslotDialog") === undefined)
		return;
	var day = parseInt($("#dialogDay").val());
	var startTime = parseInt($("#dialogStartTime").val());
	var endTime = parseInt($("#dialogEndTime").val());
	if (endTime <= startTime){
		return;
	}
	var start = {hour:startTime, min:0};
	var end = {hour:endTime, min:0};
	var timeslot = {day:day, startTime:start, endTime:end};
	for (var x = 0; x < customTimeslots.length; x++) {
		if (doTimeslotsConflict(timeslot, customTimeslots[x]))
			return false;
	}
	customTimeslots.push(timeslot);
	return true;
}

function doTimeslotsConflict(t1, t2) {
	if(t1.day != t2.day) return false;
	s1 = getTimeInt(t1.startTime);
	e1 = getTimeInt(t1.endTime);
	s2 = getTimeInt(t2.startTime);
	e2 = getTimeInt(t2.endTime);
	if(s1 < e2 && e1 > e2) return true;
	if(s1 < s2 && e1 > s2) return true;
	if(s2 < e1 && e2 > e1) return true;
	if(s2 < s1 && e2 > s1) return true;
	return false;
}
