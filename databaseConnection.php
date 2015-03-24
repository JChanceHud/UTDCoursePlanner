<?php
//databaseConnection.php
//keeps the connection open for lots of queries
//

include_once('course.php');
include_once('settings.php');

class databaseConnection {
	public $connection = null;
	//private $servername = "utdcourseplanner.ddns.net";
	private $servername;

	function __construct(){
		global $databaseURL;
		$this->servername = $databaseURL;
		$username = "chance";
		include_once("password.php");
		try {
		    $this->connection = new PDO("mysql:host=$this->servername;dbname=courselist", $username, $password, array(
	    		//PDO::ATTR_PERSISTENT => true
			));
		    // set the PDO error mode to exception
		    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    }
		catch(PDOException $e)
	    {
	    	echo "Connection failed: " . $e->getMessage();
	    	$this->connection = null;
	    }
	}

	//wrapper function
	function query($query){
		return $this->connection->query($query);
	}

	function close(){
		$this->connection = null;
	}

	function isConnectionOpen(){
		return $this->connection !== null;
	}
}


?>
