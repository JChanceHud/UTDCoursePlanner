<?php

$servername = "utdcourseplanner.ddns.net";
$username = "chance";
$password = $_GET['password'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=courselist", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	debugLog("Connected successfully");
    $filepath = "temp.xlsx";
	getExcelDoc($filepath);

	$content = loadExcelFile($filepath);

    $conn->exec("DROP TABLE IF EXISTS courses_bak");
    $conn->exec("RENAME TABLE courses TO courses_bak");
	$conn->exec("CREATE TABLE courses LIKE courses_bak");
	$conn->exec("TRUNCATE TABLE autocomplete"); //get rid of all entries
	$autocompleteEntries = array();
	$finalQuery = "";
	for($x = 4; $x < count($content); $x++){
		$classLoc = $content[$x]['K'];
		$days = "";
		$time = "";
		$room = "";
		$classOnline = "0";
		if($classLoc != "-schedule is not posted or not applicable-"){
			//adding support for labs with semicolons
			$diffRooms = explode(";", $classLoc);
			if (strpos($classLoc, ";") === FALSE) {
				$diffRooms = array($classLoc);
			}
			foreach ($diffRooms as $string) {
				$strs = explode(" : ", $string, 3);
				$days .= $strs[0]."|";
				$time .= $strs[1]."|";
				$room .= str_replace("_", " ", $strs[2])."|";
			}
		}
		else
			$classOnline = "1";
		
		$finalQuery .= "INSERT INTO courses (classID, prefix, course, classSection, classTerm, classNumber, classTitle, classOpen, classInstructor, classDays, classTime, classRoom, classOnline)
			VALUES (". gs($content[$x]['A']) . gs($content[$x]['B']) . gs($content[$x]['C']) . gs($content[$x]['D']) . gs($content[$x]['E']) . gs($content[$x]['F']) . gs($content[$x]['G']) . gs( ($content[$x]['I']=="Open")?"1":"0" ) . gs($content[$x]['J']) . gs($days) . gs($time) . gs($room) . "'$classOnline'); ";
		//iterate through each row
		//
		//now add the autocomplete row
		$string = explode(".", $content[$x]['A'])[0];
		if (!in_array($string, $autocompleteEntries)) {
			array_push($autocompleteEntries, $string);
		}
	}
	$conn->exec($finalQuery);
	$acQuery = "";
	foreach ($autocompleteEntries as $entry) {
		$acQuery .= "INSERT INTO autocomplete VALUES ('".$entry."'); ";
	}
	debugLog($conn->exec($acQuery));
	debugLog("Finished updating database");
	unlink($filepath);
}
catch(PDOException $e)
    {
    debugLog("Connection failed: " . $e->getMessage());
    }

//unlink($filepath);

function gs($string){
	return '\'' . addslashes(trim($string)) . '\', ';
}

function getExcelDoc($filepath){
	set_time_limit(0);
	
	$response = exec('..\phantomjs.exe loadCookie.js'); //for windows
	debugLog($response);
	$dataURL = "https://coursebook.utdallas.edu/reportmonkey/coursebook";
	$fp = fopen ($filepath, 'w+');
	//$fpp = fopen ("error.txt", 'w+');
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $dataURL);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_FILE, $fp); 
	curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl_handle, CURLOPT_COOKIE, $response);
	curl_setopt($curl_handle, CURLOPT_VERBOSE, true);
	//curl_setopt($curl_handle, CURLOPT_STDERR, $fpp);
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl_handle, CURLOPT_COOKIEFILE, "cookies.txt");
	$data = curl_exec($curl_handle);
	$error = curl_error($curl_handle);
	curl_close($curl_handle);
	fclose($fp);
	//fclose($fpp);
	debugLog($data);
}

function loadExcelFile($file){
	date_default_timezone_set("US/Central"); //fix warning from PHPExcel
	set_include_path(get_include_path() . PATH_SEPARATOR . 'Classes/');
	include 'PHPExcel/IOFactory.php';
	try {
		$objPHPExcel = PHPExcel_IOFactory::load($file);
	} catch(Exception $e) {
		die('Error loading file "'.pathinfo($file,PATHINFO_BASENAME).'": '.$e->getMessage());
	}

	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
	unset($objPHPExcel);
	return $sheetData;
}

function debugLog($string) {
	echo $string."<br /><br />";
}

?>
