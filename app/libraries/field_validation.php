<?php

class Field_validation
{
	function ValidateRequiredGatewayFields($required_fields, $params)
	{
		$CI =& get_instance();
		
		$error = FALSE;
		
		$params = array_keys($params);
		
		foreach($required_fields as $required_value)
		{
			if(!in_array($required_value, $params)) {
					$error = TRUE;
			}
		}
			 
		if($error) {
			die($CI->response->Error(1004));
		} else {
			return TRUE;
		}
	}
	
	function ValidateCountry($country_code)
	{
		$CI =& get_instance();
		
		$CI->db->where('iso2', $country_code);
		$CI->db->or_where('iso3', $country_code);
		$CI->db->or_where('name', $country_code);
		$query = $CI->db->get('countries');
		if($query->num_rows() > 0) {
			return $query->row()->country_id;
		} else {
			return FALSE;
		}
	}
	
	function ValidateEmailAddress($email)
	{
		return preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email);
	}
	
	function ValidateCreditCard($card_number, $gateway)
	{	
		$patterns = array();
		
		if (str_replace(' ','',$card_number) == '0000000000000000') {
			return 'dummy';
		}
		
		if (isset($gateway['accept_amex']) and $gateway['accept_amex'] == 1) {
			$patterns['amex'] = "/^([34|37]{2})([0-9]{13})$/";
		}
		
		if (isset($gateway['accept_discover']) and $gateway['accept_discover'] == 1) {
			$patterns['disc'] = "/^([6011]{4})([0-9]{12})$/";
		}
		
		if (isset($gateway['accept_visa']) and $gateway['accept_visa'] == 1) {
			$patterns['visa'] = "/^([4]{1})([0-9]{12,15})$/";
		}
		
		if (isset($gateway['accept_mc']) and $gateway['accept_mc'] == 1) {
			$patterns['mc'] = "/^([51|52|53|54|55]{2})([0-9]{14})$/";
		}
		
		if (isset($gateway['accept_dc']) and $gateway['accept_dc'] == 1) {
			$patterns['dc'] = "/^([30|36|38]{2})([0-9]{12})$/";
		}
		
		foreach($patterns as $key => $value) {
			if(preg_match($value, $card_number)) {
				return $key;
			}
		}

		return FALSE;
		
	}
	
	function ValidateAmount($amount)
	{
		// remove all commas used to indicate dollars/cents
		$amount = preg_replace('/\,([0-9]{2})$/i','.$1',$amount);
		
		// remove all commas used to indicate groupings
		$amount = str_replace(',','',$amount);
		
		if (!is_numeric($amount)) {
			return FALSE;
		}
		
		if ($amount < 0) {
			return FALSE;
		}
		
		return (float)$amount;
	}
	
	function ValidateDate($date)
	{
		//Check the length of the entered Date value
		if((strlen($date) < 10) OR (strlen($date) > 10)) {
			return FALSE;
		}
		
		//The entered value is checked for proper Date format
		if((substr_count($date, '-')) != 2) {
			return FALSE;
		}
		
		$pos = explode('-', $date);
		$year = $pos[0];
		$result = preg_match('/^\d+$/', $year);
		
		if(!$result) {
			return FALSE;
		}
		
		if(($year < 1900) OR ($year > 2200)) {
			return FALSE;
		}
		
		$month = $pos[1];
		if(($month <= 0) OR ($month > 12)) {
			return FALSE;
		}
		
		$result = preg_match('/^\d+$/', $month);
		
		if(!$result) {
			return FALSE;
		}
		
		$day = $pos[2];
		if(($day <= 0) OR ($day > 31)) {
			return FALSE;
		}
		
		$result = preg_match('/^\d+$/', $day);
		
		if(!$result) {
			return FALSE;
		}
		
		return TRUE;
		
		
	}
}