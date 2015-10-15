<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Recaptcha Model 
*
* Contains all the methods used to validate a recaptcha response.
*
* @author Jose Vargas
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Recaptcha_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	public function recaptchaValidation($validate){
		$return = false;
		
		if(!empty($validate)) {
			$this->CI->load->model('settings/settings_model', 'settings');
			$reCaptchaSettings = $this->CI->settings->get_setting('recaptcha_secret_key');
			$post['secret'] = $reCaptchaSettings['value'];
			$post['response'] = $validate;
			$url = 'https://www.google.com/recaptcha/api/siteverify';
			$validationJson = $this->curlRequest($url, $post);
			$validationArr = json_decode($validationJson, TRUE);
			$return = $validationArr['success'];
		}
		return $return;
	}
	
	public function curlRequest($uri, $fields){
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_URL, $uri);
		if(is_array($fields)){
			curl_setopt($ch,CURLOPT_POST, count($fields));
			$fieldsString = '';
			//url-ify the data for the POST
			$ampersand = false;
			foreach($fields as $key=>$value) {
				if($ampersand){
					$fieldsString .= '&';
				}
				$fieldsString .= $key . '=' . $value;
				$ampersand = true;
			}
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fieldsString);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$return = curl_exec($ch);
		curl_close($ch);
	
		return $return;
	}
}