<?php

function strip_whitespace ($param) {
	$param = preg_replace('/\s/s','',$param);
	
	return $param;
}