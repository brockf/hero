<?php

function local_time ($time) {	
	$CI =& get_instance();
	
	$timestamp = (!is_numeric($time)) ? strtotime($time) : $time;
	$timestamp = $timestamp - date("Z");
	
	$timezone = $client->gmt_offset;
	$daylight_saving = (date("I") == 1) ? TRUE : FALSE;
	
	// format
	$format = (defined("_CONTROLPANEL")) ? "M j, Y" : "c";
	if (defined("_CONTROLPANEL") and strstr($time, ' ')) {
		$format = 'M j, Y h:i a';
	}
	
	$check_empty = $timestamp + date("Z");
	
	if (empty($check_empty) and defined("_CONTROLPANEL")) {
		return 'N/A';
	}
	elseif (empty($check_empty)) {
		return '0';
	}

	return date($format,gmt_to_local($timestamp, $timezone, $daylight_saving));
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