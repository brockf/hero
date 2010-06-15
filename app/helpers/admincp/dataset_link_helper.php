<?php

function dataset_link ($url, $filters = array()) {
	$CI =& get_instance();
	
	$CI->load->library('asciihex');
	
	$filters = $CI->asciihex->AsciiToHex(base64_encode(serialize($filters)));
	
	$url = site_url($url) . '/' .$filters . '/0';
	
	return $url;
}
