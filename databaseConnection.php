<?php
//databaseConnection.php
//

include_once('course.php');

class databaseConnection {
	public $connection = null;

	function __construct(){
		$servername = "utdcourseplanner.ddns.net";
		$username = "chance";
		include_once("password.php");
		try {
		    $this->connection = new PDO("mysql:host=$servername;dbname=courselist", $username, $password, array(
	    		PDO::ATTR_PERSISTENT => true
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