<?php

function unique_email ($email) {
	$CI =& get_instance();
	
	return $CI->user_model->unique_email($email);
}