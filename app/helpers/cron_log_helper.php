<?php

/**
* Cron Log
*
* Log a note during the processing of your cron hook.
*
* @param string $message
*
* @return void
*/
function cron_log ($message) {
	log_message('debug', $message);
	
	echo '<div style="border-bottom: 1px solid #dedede; padding: 10px 25px; font-family: Monaco, Courier New, DejaVu Sans Mono, Bitstream Vera Sans Mono, monospace; color: #333; background-color: #f0f0f0; text-shadow: 0px 1px #fff">
			' . $message . '
		  </div>';
		  
	return;
}