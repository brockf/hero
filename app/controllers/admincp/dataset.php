<?php
/**
* Dataset Controller 
*
* Handles miscellaneous dataset features
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/
class Dataset extends Controller {

	function __construct()
	{
		parent::Controller();
	}
	
	/**
	* Prep Filters
	*
	* Handles AJAX posts of dataset filters and returns an encoded array
	*
	* @return string Encoded string.
	*/
	function prep_filters()
	{	
		$serialize = array();
	
		$values = explode('&',$this->input->post('filters'));
		foreach ($values as $value) {
			list($name,$value) = explode('=',$value);
			
			if ($value != '' and $value != 'filter+results' and $value != 'start+date' and $value != 'end+date') {
				$serialize[$name] = $value;
			}	
		}
		
		$this->load->library('asciihex');
	
		echo $this->asciihex->AsciiToHex(base64_encode(serialize($serialize)));
	}
	
	/**
	* Prep Actions
	*
	* Creates a ASCII'd, base64'd, serialized array of all dataset items to pass to a URL
	*
	* @return string Encoded string
	*/
	function prep_actions()
	{
		$serialize = array();
	
		$values = explode('&',$this->input->post('items'));
		foreach ($values as $value) {
			list($name,$value) = explode('=',$value);
			$name = str_replace('check_','',$name);
			
			if ($value == 1) {
				$serialize[] = $name;
			}
		}
		
		$this->load->library('asciihex');
	
		echo $this->asciihex->AsciiToHex(base64_encode(serialize($serialize))) . '/' . $this->asciihex->AsciiToHex(base64_encode($this->input->post('return_url')));
	}
}