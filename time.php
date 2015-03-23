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

	//converts the current stored time to an integer value that can be easily compared against other times
	//the integer value returned is lossy - it cannot be converted back into an exact time
	function toInteger(){
		$m = ($this->min*100)/60;
		return intval($this->hour . (($m==0)?"0":'') . $m);
	}

	function toUnscaledInteger(){
		return intval($this->hour . (($this->min==0)?"0":'') . $this->min);
	}

	function getString(){
		return $this->hour.":". (($this->min==0)?"0":"") . $this->min;
	}
}

?>
