<?php
/**
* Jerrymail Newsletter Controller 
*
* Allow for newsletter-only subscriptions
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/
class Newsletter extends Front_Controller {
	function __construct ()
	{
		parent::__construct();
	}
	
	function index () {
		$return = 'newsletter_thanks';
	
		$data = array(
					'return' => $return
					);
		
		return $this->smarty->display('newsletter.thtml', $data);
	}
	
	function thanks () {
		return $this->smarty->display('newsletter_thanks.thtml');
	}
}