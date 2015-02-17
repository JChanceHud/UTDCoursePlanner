var $_GET = {};
var classes = [];

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
		var post = $.post("generateSchedules.php?classes=" + getClassesString(), $("#courseForm").serialize());
		post.done(function(data){
			updateSchedules(data);
		});
		return false;
	});

	setupClassInput();
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
	var domain = "utdcourseplanner.ddns.net";
	var courseString = "classes="+getClassesString();
	var allowClosed = "&allowClosed=" + $("#allowClosed").prop("checked");
	var timeBetweenClasses = "&timeBetweenClasses=" + $("#timeBetweenClasses").prop("checked");
	var dayClasstime = "&dayClasstime=" + $('input[name=dayClasstime]:checked').val();
	var late = "&late=" + $('#late').val();
	var early = "&early=" + $('#early').val();
	var permalink = "http://"+domain+"/index.php?" + courseString + allowClosed + timeBetweenClasses + dayClasstime + late + early;
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

function changeSchedule(val){
	var current = parseInt($("#currentSchedule").val());
	current += parseInt(val);
	if(current < 0 || current >= $("#currentSchedule option").length)
		return;
	$("#currentSchedule").val(current);
	$("#currentSchedule").change();
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
		var j = JSON.parse(data);
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
