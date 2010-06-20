<?php

function local_time ($time) {
	if ($time == '0000-00-00 00:00:00') {
		return 'n/a';
	}	
	
	return $time;
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