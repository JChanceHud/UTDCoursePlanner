<?php
//autoupdate.php
//code to auto pull from the repo and install the latest version of the server
//

$zipURL = "https://github.com/JChanceHud/UTDCoursePlanner/archive/master.zip";
file_put_contents("tmp.zip", fopen($zipURL, 'r'));
mkdir("new");

$file = new ZipArchive;
if($file->open("tmp.zip") === TRUE){
	$file->extractTo('new/');
	$file->close();
}
else
	echo "Failed to open zip";

$files = scandir("new");
$source = "new/";
$destination = "../";
// Cycle through all source files
foreach ($files as $file) {
	if (in_array($file, array(".",".."))) continue;
	if (is_dir($file)) continue;
	// If we copied this successfully, mark it for deletion
	copy($source.$file, $destination.$file);
}

echo "Cleaning up";
unlink("tmp.zip");
rmdir("new");

?>
