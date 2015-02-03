<?php
//time.php
//

class time {
	public $hour = 0;
	public $min = 0;

	function __construct($h, $m){
		$this->hour = $h;
		$this->min = $m;
	}

	function p(){
		echo $this->hour.":".$this->min." ";
	}
}
