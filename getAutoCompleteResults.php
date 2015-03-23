<?php
if (!isset($_GET['term']))
	exit();

include_once("databaseConnection.php");

$connection = new databaseConnection();

$query = "SELECT * FROM autocomplete WHERE classID LIKE '".$_GET['term']."%';";
$result = $connection->query($query);
$arr = $result->fetchAll();
$formattedArr = array();
foreach ($arr as $obj) {
	//each objc is formatted as follows
	//{"classID":"ce1202","0":"ce1202"}
	array_push($formattedArr, $obj["classID"]);
	if (count($formattedArr) == 5) break;
}
echo json_encode($formattedArr);

$connection->close();

?>
