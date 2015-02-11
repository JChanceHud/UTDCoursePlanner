<?php

$servername = "utdcourseplanner.ddns.net";
$username = "chance";
$password = $_GET['password'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=courselist", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
    $filepath = "temp.xlsx";
	getExcelDoc($filepath);

	$content = loadExcelFile($filepath);

    $conn->exec("DROP TABLE IF EXISTS courses_bak");
    $conn->exec("RENAME TABLE courses TO courses_bak");
    $conn->exec("CREATE TABLE courses LIKE courses_bak");
	for($x = 4; $x < count($content); $x++){
		$classLoc = $content[$x]['K'];
		$days = "";
		$time = "";
		$room = "";
		$classOnline = "0";
		if($classLoc != "-schedule is not posted or not applicable-"){
			$strs = explode(" : ", $classLoc, 3);
			$days = $strs[0];
			$time = $strs[1];
			$room = str_replace("_", " ", $strs[2]);
		}
		else
			$classOnline = "1";
		
		$query = "INSERT INTO courses (classID, prefix, course, classSection, classTerm, classNumber, classTitle, classOpen, classInstructor, classDays, classTime, classRoom, classOnline)
		VALUES (". gs($content[$x]['A']) . gs($content[$x]['B']) . gs($content[$x]['C']) . gs($content[$x]['D']) . gs($content[$x]['E']) . gs($content[$x]['F']) . gs($content[$x]['G']) . gs( ($content[$x]['I']=="Open")?"1":"0" ) . gs($content[$x]['J']) . gs($days) . gs($time) . gs($room) . "'$classOnline')";
		//iterate through each row
		$conn->exec($query);
	}
		echo "Finished updating database";
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }

//unlink($filepath);

function gs($string){
	return '\'' . addslashes(trim($string)) . '\', ';
}

function getExcelDoc($filepath){
	//first get cookies from the course planner
	set_time_limit(0);
	/*
	$courseURL = "http://coursebook.utdallas.edu/";
	$ch = curl_init($courseURL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// get headers too with this line
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
	$result = curl_exec($ch);
	*/
	$dataURL = "https://coursebook.utdallas.edu/reportmonkey/coursebook";
	$fp = fopen ($filepath, 'w+');
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $dataURL);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_FILE, $fp); 
	curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl_handle, CURLOPT_COOKIE, "PTGSESSID=51ijgrck7a7vktlvaannvtql96; __utmt=1; __utma=25620399.354926190.1423632839.1423632839.1423632839.1; __utmb=25620399.1.10.1423632839; __utmc=25620399; __utmz=25620399.1423632839.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)");
	//curl_setopt($curl_handle, CURLOPT_COOKIEFILE, "cookies.txt");
	$data = curl_exec($curl_handle);
	$error = curl_error($curl_handle);
	curl_close($curl_handle);
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

?>
