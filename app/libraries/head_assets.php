<?php

/**
* Head Assets Library
*
* Loaded by the Admincp_Controller, this library allows one to added JavaScript
* and CSS includes to the <head> of a page.  An output() method must be called
* in the template to actually display these.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Head_assets {
	private $assets;
	
	/**
	* Add Stylesheet File
	*
	* @param string $file
	* @param boolean $is_branded (default: TRUE)
	* @param string $rel (default: stylesheet)
	* @param string $type (default: text/css)
	*
	* @return void;
	*/
	function stylesheet ($file, $is_branded = TRUE, $rel = 'stylesheet', $type = 'text/css') {
		$file = ($is_branded == TRUE) ? branded_include($file) : $file;
	
		$this->assets .= '<link rel="' . $rel . '" type="' . $type . '" href="' . $file . '" />' . "\n";
		
		return;
	}
	
	/**
	* Add JS File
	*
	* @param string $file
	* @param boolean $is_branded (default: TRUE)
	* @param string $type (default: text/javascript)
	*
	* @return void;
	*/
	function javascript ($file, $is_branded = TRUE, $type = 'text/javascript') {
		$file = ($is_branded == TRUE) ? branded_include($file) : $file;
	
		$this->assets .= '<script type="' . $type . '" src="' . $file . '"></script>' . "\n";
		
		return;
	}
	
	/**
	* Add Other Code
	*
	* @param string $code
	*
	* @return void;
	*/
	function code ($code) {
		$this->assets .= $code . "\n";
		
		return;
	}
	
	/**
	* Display Assets
	*
	* @return string $assets
	*/
	function display () {
		return $this->assets;
	}
}