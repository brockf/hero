<?php
/**
* Jerry Presale Controller 
*
* Show the presale signup page
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher
*/
class Presale extends Front_Controller {
	function __construct ()
	{
		parent::__construct();
	}
	
	function index () {
		return $this->smarty->display('presale.thtml');
	}
}