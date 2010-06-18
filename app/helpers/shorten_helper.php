<?php

function shorten ($string, $length) {
	if (strlen($string) > $length) {
		$string = substr($string, 0, $length);
		$string .= '&hellip';
		
		return $string;
	}
	else {
		return $string;
	}
}