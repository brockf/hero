<?php

function shorten ($string, $length, $retain_whole_words = FALSE) {
	$string = trim($string);
	
	if (strlen(strip_tags($string)) > $length) {
		if ($retain_whole_words == FALSE) {
			$string = substr($string, 0, ($length - 3));
			$string .= '&hellip;';
		}
		else {
			$string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, ($length - 3)));
			$string .= '&hellip;';
		}
		
		return $string;
	}
	else {
		return $string;
	}
}