<?php

function unique_username ($username) {
	$CI =& get_instance();
	
	return $CI->user_model->unique_username($username);
}