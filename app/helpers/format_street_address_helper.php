<?php

/**
* Format Street Address
*
* Takes an array of street address elements and outputs an HTML formatted address
*
* @param array $address
* @return string address
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function format_street_address ($address = array()) {
	// field standardization
	if (isset($address['address_1']) and !empty($address['address_1'])) {
		$address['address'] = $address['address_1'];
	}
	
	if (isset($address['state']) and !empty($address['state'])) {
		$address['region'] = $address['state'];
	}
	
	if (isset($address['zip']) and !empty($address['zip'])) {
		$address['postal_code'] = $address['zip'];
	}
	elseif (isset($address['zip_code']) and !empty($address['zip_code'])) {
		$address['postal_code'] = $address['zip_code'];
	}
	
	// do the format
	$return = '';
	if (isset($address['first_name']) and !empty($address['first_name'])) {
		$return .= $address['first_name'];
	}
	
	if (isset($address['last_name']) and !empty($address['last_name'])) {
		$return .= ' ' . $address['last_name'];
	}
	
	if (isset($address['first_name']) and !empty($address['first_name']) or isset($address['last_name']) and !empty($address['last_name'])) {
		$return .= "\n";
	}
	
	if (isset($address['company']) and !empty($address['company'])) {
		$return .= $address['company'] . "\n";
	}
	
	if (isset($address['address']) and !empty($address['address'])) {
		$return .= $address['address'] . "\n";
	}
	
	if (isset($address['address_2']) and !empty($address['address_2'])) {
		$return .= $address['address_2'] . "\n";
	}
	
	if (isset($address['city']) and !empty($address['city'])) {
		$return .= $address['city'];
		
		if (isset($address['region']) and !empty($address['region'])) {
			$return .= ', ' . $address['region'] . "\n";
		}
	}
	elseif (isset($address['region']) and !empty($address['region'])) {
		$return .= $address['region'] . "\n";
	}
	
	if (isset($address['country']) and !empty($address['country'])) {
		// should we get the full country name?
		if (strlen($address['country']) == 2) {
			// yes, let's do it
			$CI =& get_instance();
			$CI->db->select('name');
			$CI->db->where('iso2',$address['country']);
			$result = $CI->db->get('countries');
			
			if ($result->num_rows() == 1) {
				$address['country'] = $result->row()->name;
			}
		}
	
		$return .= $address['country'];
		
		if (isset($address['postal_code']) and !empty($address['postal_code'])) {
			$return .= '&nbsp;&nbsp;' . $address['postal_code'];
		}
	}
	elseif (isset($address['postal_code']) and !empty($address['postal_code'])) {
		$return .= $address['postal_code'];
	}
	
	if (isset($address['phone_number']) and !empty($address['phone_number'])) {
		$return .= "\n" . $address['phone_number'];
	}
	
	return nl2br($return);
}