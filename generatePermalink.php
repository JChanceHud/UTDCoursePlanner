<?php
//generatePermalink.php
//

include_once('settings.php');

if(!isset($_GET['classes']))
	exit(0);
$closed = "";
if(isset($_GET['allowClosed']))
	$closed = "&allowClosed=".$_GET['allowClosed'];
$showSchedule = "";
if(isset($_GET['showSchedule']))
	$showSchedule = "&showSchedule=".$_GET['showSchedule'];
$early = "";
$late = "";
if(isset($_GET['early']))
	$early = "&early=".$_GET['early'];
if(isset($_GET['late']))
	$late = "&late=".$_GET['late'];

echo "http://".$baseURL."permalink.php?classes=".$_GET['classes'].$closed.$showSchedule.$early.$late;

?>