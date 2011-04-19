<?php

function get_notices () {
	$CI =& get_instance();
	
	$errors = $CI->notices->GetErrors();
	$notices = $CI->notices->GetNotices();
	
	$return = '';
	while (list(,$error) = each($errors)) {
		$return .= '<div class="error">' . $error . '</div>';
	}
	reset($errors);
	
	while (list(,$notice) = each($notices)) {
		$return .= '<div class="notice">' . $notice . '</div>';
	}
	reset($notices);
	
	return $return;
}