<?php

function clean_string ($text) {
	$clean = $text;
	$clean = preg_replace("/[^a-zA-Z0-9\_\s]/", '', $clean);
	$clean = str_replace(' ','_',$clean);
	$clean = strtolower($clean);
	$clean = preg_replace('/_+/i','_',$clean);
	
	return $clean;
}