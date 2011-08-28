<?php

/**
* Local Time
*
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function local_time ($time, $format = FALSE) {
	if ($time == '0000-00-00 00:00:00' or $time == '0000-00-00') {
		return 'n/a';
	}	
	
	$time = strtotime($time);
	
	// see if we should return something like "X minutes ago"
	$time_since = time_since($time);
	
	if (!empty($time_since) and setting('use_time_since') == '1' and $format == FALSE) {
		return $time_since;
	}
	else {
		if ($format == FALSE) {
		 	// return in the default date format
			return date(setting('date_format'), $time);
		}
		else {
			// return in specified format
			return date($format, $time);
		}
	}
}

function server_time ($time, $format = "Y-m-d", $today_or_more = false) {
	$time = strtotime($time);
	
	$time = $time - date("Z");
	
	if ($today_or_more == true) {
		if ($time < strtotime(date('Y-m-d'))) {
			$time = strtotime(date('Y-m-d'));
		}
	}
	
	return date($format, $time);
}