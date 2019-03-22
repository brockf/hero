<?php

function get_notices () {
	$CI =& get_instance();
	
	$errors = $CI->notices->GetErrors();
	
	$return = '';
	$eCount = count($errors);
	for($i = 0; $i < $eCount; $i++){
		$return .= '<div class="error">' . $errors[$i] . '</div>';
	}
	unset($eCount);
	reset($errors);

	$notices = $CI->notices->GetNotices();
	$nCount = count($notices);
	for($i = 0; $i < $nCount; $i++){
		$return .= '<div class="notice">' . $notices[$i] . '</div>';
	}
	unset($nCount);
	reset($notices);
	
	return $return;
}