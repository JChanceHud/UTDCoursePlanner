<?php

include_once("password.php");
$serverUpdateURL = "http://192.168.0.2/autoupdate";
$sqlUpdateURL = "http://192.168.0.2/database/?password=".$password;

echo file_get_contents($serverUpdateURL);
echo file_get_contents($sqlUpdateURL);

?>
