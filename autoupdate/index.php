<?php
//autoupdate.php
//code to auto pull from the repo and install the latest version of the server
//

$zipURL = "https://github.com/JChanceHud/UTDCoursePlanner/archive/master.zip";
$dirName = "UTDCoursePlanner-master";

file_put_contents("tmp.zip", fopen($zipURL, 'r'));

$file = new ZipArchive;
if($file->open("tmp.zip") === TRUE){
	$file->extractTo('.');
	$file->close();
}
else
	echo "Failed to open zip";

$files = scandir($dirName);
$source = $dirName."/";
$destination = "../";
// Cycle through all source files
foreach ($files as $file) {
	if (in_array($file, array(".",".."))) continue;
	if (is_dir($source.$file)) continue;
	// If we copied this successfully, mark it for deletion
	copy($source.$file, $destination.$file);
}

echo "Cleaning up";
unlink("tmp.zip");
rrmdir($dirName);


function rrmdir($dir) {
	   if (is_dir($dir)) {
		        $objects = scandir($dir);
				     foreach ($objects as $object) {
						        if ($object != "." && $object != "..") {
									         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
											        }
								     }
				     reset($objects);
				     rmdir($dir);
					    }
	    }


?>
