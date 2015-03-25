<?php

function dlog($string) {
	echo $string . "<br />";
	error_log($string);
}

?>
