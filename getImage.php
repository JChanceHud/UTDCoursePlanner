<?php

if (!isset($_GET['schedule'])) {
	echo "Did not receive schedule information";
	exit();
}

$schedule = json_decode($_GET['schedule'], true);
// var_dump($schedule);
// exit();
$timeslots = array();

if (isset($_GET['timeslots'])) {
	$timeslots = json_decode($_GET['timeslots'], true);
}

$box_path = "imageCreation/box.jpg";

$finalImage = imagecreatefromjpeg("imageCreation/background.jpg");
$classBox = imagecreatefromjpeg($box_path);
$fullClassBox = imagecreatefromjpeg($box_path);
$busyBox = imagecreatefromjpeg($box_path);

//colorize the image
colorizeMaskedImage($classBox, array(0, 136, 204));
colorizeMaskedImage($fullClassBox, array(255, 0, 0));
colorizeMaskedImage($busyBox, array(128, 0, 128));


//now draw based on schedules
foreach ($schedule as $class) {
	foreach ($class['classTimes'] as $time) {
		$topLine = strtoupper($class['classSection']) . "\n" . $time['room'];
		$middleLine = $class['classInstructor'];
		$bottomLine = getTimeString($time['startTime'], ":") . " - " . getTimeString($time['endTime'], ":");
		$finalString = $topLine . "\n" . $bottomLine;
		$image = $class['classIsOpen']==1?$classBox:$fullClassBox;
		$startTime = intval(getTimeString($time['startTime']));
		$endTime = intval(getTimeString($time['endTime']));
		drawBoxAtPosition($image, $finalImage, getXPosForDay($time['day']), getYPosForTime($startTime), getHeightForTimes($startTime, $endTime), $finalString);
	}
}

//now draw the custom timeblocks
if (count($timeslots) != 0) {
	foreach($timeslots as $time) {
		$topLine = $time['title'];
		$bottomLine = getTimeString($time['startTime'], ":") . " - " . getTimeString($time['endTime'], ":");
		$finalString = $topLine . "\n" . $bottomLine;
		$startTime = intval(getTimeString($time['startTime']));
		$endTime = intval(getTimeString($time['endTime']));
		drawBoxAtPosition($busyBox, $finalImage, getXPosForDay($time['day']), getYPosForTime($startTime), getHeightForTimes($startTime, $endTime), $finalString);
	}
}

//use buffer to capture output
ob_start();
imagejpeg($finalImage, NULL, 100);
$i = ob_get_clean(); 
// Save file
$filename = "images/".randString(15).".jpg";
$fp = fopen ($filename,'w');
fwrite ($fp, $i);
fclose ($fp);
imagedestroy( $finalImage );
//redirect to image
Header("Location: ".$filename);


// Helper functions
// -----------------------------------------------------------------------------
//

function getHeightForTimes ($time1, $time2) {
	$divider = 2;
	return getYPosForTime($time2, false) - getYPosForTime($time1) - $divider;
}

function getXPosForDay($day) {
	$firstClassPosition = 40;
	$boxWidth = 144;
	$dividerWidth = 2;
	$xPos = $firstClassPosition;
	for ($x = 0; $x < $day; $x++) {
		$xPos += $boxWidth + $dividerWidth;
	}
	return $xPos;
}

function getYPosForTime($time, $isStart=true) {
	$firstClassPosition = 45;
	$classHeight = 22;
	$yPos = $firstClassPosition;
	$firstHour = 8;
	$minutes = $time - floor($time/100)*100;
	$rowNum = ((floor($time / 100) - $firstHour) * 2) + 1 + ($minutes>=30?1:0);
	if ($minutes > 30) {
		$rowNum++;
	}
	if (!$isStart && $minutes < 30) {
		$rowNum++;
	}
	for ($x = 0; $x < $rowNum; $x++) {
		$yPos += $classHeight;
	}
	return $yPos;
}

function drawBoxAtPosition($boxImage, $backgroundImage, $xPos, $yPos, $height, $text) {
	$boxImageRoundedHeight = 10; //height of the rounded section
	$innerHeight = $height - 2*$boxImageRoundedHeight;
	$boxImageWidth = imagesx($boxImage);
	$boxImageHeight = imagesy($boxImage);
	imagecopyresized($backgroundImage, $boxImage, $xPos, $yPos, 0, 0, $boxImageWidth, $boxImageRoundedHeight, $boxImageWidth, $boxImageRoundedHeight);
	$yPosTemp = $yPos + $boxImageRoundedHeight;
	imagecopyresized($backgroundImage, $boxImage, $xPos, $yPosTemp, 0, $boxImageRoundedHeight, $boxImageWidth, $innerHeight, $boxImageWidth, 10);
	$yPosTemp += $innerHeight;
	imagecopyresized($backgroundImage, $boxImage, $xPos, $yPosTemp, 0, $boxImageHeight-$boxImageRoundedHeight, $boxImageWidth, $boxImageRoundedHeight, $boxImageWidth, $boxImageRoundedHeight);

	$lines = explode("\n", $text);
	$font = 3;
	$totalHeight = imagefontheight($font) * count($lines);
	for ($i = 0; $i < count($lines); $i++) {
		$line = $lines[$i];
		$textHeight = imagefontheight($font);
		$textWidth = strlen($line) * imagefontwidth($font);
		$y = ($yPos + ($height/2)) - ($totalHeight/2) + $i*$textHeight;
		$x = ($xPos + ($boxImageWidth/2)) - $textWidth/2;
		$color = imagecolorallocate($backgroundImage, 255, 255, 255);
		imagestring($backgroundImage, $font, $x, $y, $line, $color);
		imagecolordeallocate($backgroundImage, $color);
	}
}

function colorizeMaskedImage($img, $color) { 
	//will colorize only black pixels and will leave all other pixels clear
    imagesavealpha($img, true); 
    imagealphablending($img, true); 

    $img_x = imagesx($img); 
    $img_y = imagesy($img); 
    for ($x = 0; $x < $img_x; ++$x) 
    { 
        for ($y = 0; $y < $img_y; ++$y) 
        { 
            $rgba = imagecolorsforindex($img, imagecolorat($img, $x, $y)); 
            if ($rgba['red'] <= 20 && $rgba['green'] <= 20 && $rgba['blue'] <= 20) {
	            $color_alpha = imagecolorallocate($img, $color[0], $color[1], $color[2]);
            	imagesetpixel($img, $x, $y, $color_alpha);
            	imagecolordeallocate($img, $color_alpha);
            } else {
            	$clear_color = imagecolorallocatealpha($img, 255, 255, 255, 0);
            	imagesetpixel($img, $x, $y, $clear_color);
            	imagecolordeallocate($img, $clear_color);
            }
        } 
    } 
} 

function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
    $str = '';
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
}

function getTimeString ($time, $separator = "") {
	return ($time['hour']>10?"":"0") . $time['hour'] . $separator . ($time['min']>10?"":"0") . $time['min'];
}

?>
