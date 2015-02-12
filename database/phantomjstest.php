<?php

//getExcelDoc("test.xlsx");

function getExcelDoc($filepath){
	//first get cookies from the course planner
	set_time_limit(0);
	
	$response = exec('phantomjs.exe test.js');
	echo $response;
	$dataURL = "https://coursebook.utdallas.edu/reportmonkey/coursebook";
	$fp = fopen ($filepath, 'w+');
	$fpp = fopen ("error.txt", 'w+');
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $dataURL);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_FILE, $fp); 
	curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl_handle, CURLOPT_COOKIE, $response);
	curl_setopt($curl_handle, CURLOPT_VERBOSE, true);
	curl_setopt($curl_handle, CURLOPT_STDERR, $fpp);
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
	//curl_setopt($curl_handle, CURLOPT_COOKIEFILE, "cookies.txt");
	$data = curl_exec($curl_handle);
	$error = curl_error($curl_handle);
	curl_close($curl_handle);
	fclose($fp);
	fclose($fpp);
	echo $data;
}

?>