<?php
//permalink.php
//converts get args to post
//classes=cs2336:cs1336:cs4337

$url = "http://utdcourseplanner.ddns.net/index.php";

if(!isset($_GET['classes'])) Header('Location: '.$url);

$classes = explode(":",$_GET['classes']);
?>
<html>
<form action='index.php' method='post' name='form'>
<?php
for($x = 0; $x < count($classes); $x++){
	echo "<input type='hidden' name='course". ($x+1) ."' value='".htmlentities($classes[$x])."'>";
}
if(isset($_GET['allowClosed']) && $_GET['allowClosed'] == "true"){
	echo "<input type='hidden' name='closed' value='allow'>";	
}
if(isset($_GET['showSchedule'])){
	echo "<input type='hidden' name='showSchedule' value='".$_GET['showSchedule']."'>";
}
?>
</form>
<script language="JavaScript">
document.form.submit();
</script>
</html>