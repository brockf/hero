<?php

class Plugin {
	public $CI;
	
	function __construct() {
		$this->CI =& get_instance();
	}
}