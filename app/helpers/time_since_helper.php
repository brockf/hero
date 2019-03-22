<?php

/**
* Time Since
*
* Return a string like "4 minutes ago" for a datetime, in relation to now
*
* @param time|date $time
* @param time $now (default: now)
*
* @return string|boolean
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function time_since($time, $now = FALSE) {
	if ($now == FALSE) {
		$now = time();
	}

	if (!is_numeric($time)) {
		$time = strtotime($time);
	}
	
	// calculate $since
	$since = $now - $time;
	
	// greater than a day?
	if ($since > (60*60*24) or $since < 0) {
		// it's more than a day ago, let's just return the data
		return FALSE;
	}
	else {
	    $chunks = array(
	        array(60 * 60 , 'hour'),
	        array(60 , 'minute'),
	        array(1 , 'second')
	    );
	
	    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
	        $seconds = $chunks[$i][0];
	        $name = $chunks[$i][1];
	        if (($count = floor($since / $seconds)) != 0) {
	            break;
	        }
	    }
	
	    $return = ($count == 1) ? '1 '.$name : "$count {$name}s";
	    $return .= ' ago';
    }
    
    return $return;
}
