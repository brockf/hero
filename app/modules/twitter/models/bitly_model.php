<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Bitly Model 
*
* Contains all the methods used to shorten a url
*
* @author Jose Vargas
* @copyright Jose Vargas
* @package Electric Framework
*
*/

class Bitly_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function shorten_url($url){
		//get Bit.ly Url
	
		/** bitly Config **/
		$creds = $this->getCreds();
		
		if($creds == false){
			return false;
		}
		
		if(!preg_match('/^http/',$url)){
			$url = 'http://' . $url;
		}
	
		$url = urlencode($url);
		
		$c = curl_init();
		$bitly = "http://api.bit.ly/v3/shorten";
		$uriParams = "login=" . $creds['bitly_id'] . "&apikey=" . $creds['bitly_api_key'] . "&longUrl=" . $url . "&format=txt";
		curl_setopt($c, CURLOPT_URL,  $bitly . '?'. $uriParams);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		$returnData = curl_exec ($c);
		curl_close ($c);
		
		$shortUrl = trim($returnData);
		
		if(preg_match('/^http/', $shortUrl)){
			return $shortUrl;
		}
		return false;
	}

	function getCreds(){
		$this->load->model('settings/settings_model', 'settings');
		
		$settings = $this->settings->get_setting('bitly_id');
		$results['bitly_id'] = $settings['value'];
		$settings = $this->settings->get_setting('bitly_api_key');
		$results['bitly_api_key'] = $settings['value'];
		
		if(empty($results['bitly_id']))
		{
			$results = false;
		} elseif (empty($results['bitly_api_key'])){
			$results = false;
		}
		return $results;
	}
}
?>