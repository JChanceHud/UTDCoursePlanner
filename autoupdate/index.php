<?php
//autoupdate.php
//code to auto pull from the repo and install the latest version of the server
//

$zipURL = "https://github.com/JChanceHud/UTDCoursePlanner/archive/master.zip";
file_put_contents("tmp.zip", fopen($zipURL, 'r'));
//$file = new ZipArchive;
//if($file->open("tmp.zip") === TRUE){
	
//}


?>
