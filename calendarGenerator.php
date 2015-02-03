<?php
//calendarGenerator.php
//generates an html calendar based on an array of courses
//

include_once('timeslot.php');

function generateCalendar($courseArr){
	$returnStr = '<table class="calendar table table-bordered">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th width="20%">Monday</th>
            <th width="20%">Tuesday</th>
            <th width="20%">Wednesday</th>
            <th width="20%">Thursday</th>
            <th width="20%">Friday</th>
        </tr>
    </thead>
    <tbody>';
    $hour = 8;
    $min = 0;
    while($hour < 20){
    	$returnStr .= '
    	<tr>
    		<td>'.($hour<10?"0":'').$hour.":".($min==0?"0":'').$min.'</td>';
    	for($x = 0; $x < 5; $x++){
    		$c = doesClassStartAtTime($x, $hour, $min, $courseArr);
    		if($c !== 0){
    			$returnStr .= '
    			<td class=" has-events" rowspan="2">
                	<div class="row-fluid lecture" style="width: 99%; height: 100%;';
                    if(!$c->classIsOpen) $returnStr .= ' background-color:red;';
                    $returnStr .= '">
                    	<span class="title">'.$c->classTitle.'</span> <span class="lecturer"><a href="' . $c->getClassURL() . '" target="_blank">'.$c->classInstructor.'</a></span> <span class="location">'.$c->classRoom.'</span>
                	</div>
            	</td>';
    		}
    		else if(isClassHappening($x, $hour, $min, $courseArr) === false){
    			$returnStr .= '<td class=" no-events" rowspan="1"></td>';
		}

    	}
    	$returnStr .= '</tr>';
    	$min += 30;
    	if($min == 60){
    		$min = 0;
    		$hour++;
    	}
    }
    $returnStr .= '
    	</tbody>
		</table>';
	return $returnStr;
}

function isClassHappening($day, $hour, $min, $courseArr){
	foreach($courseArr as $c){
		$timeslots = $c->classTimes;
		foreach($timeslots as $t){
			if($t->doesTimeConflict($day, new time($hour, $min)) === true)
				return true;
		}
	}
	return false;
}

function doesClassStartAtTime($day, $hour, $min, $courseArr){
	foreach($courseArr as $c){
		$times = $c->classTimes;
		foreach($times as $t){
			if($t->day == $day && $t->startTime->hour === $hour && 
			   (abs($t->startTime->min - $min) < 16)){
				return $c;
			}
		}
    }
    return 0;
}

?>
