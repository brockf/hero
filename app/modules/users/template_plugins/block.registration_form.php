<?php

/**
* Registration Form Template Plugin
*
* Assists in the creation of registration forms
*
* @param string $return The relative or absolute URL to return to after registering
*/

function smarty_block_registration_form ($params, $tagdata, $smarty, $repeat){
	if (!$repeat) {	
		$variables = array();
		
		// get return URL
		if (isset($params['return']) and !empty($params['return'])) {
			$variables['return'] = query_value_encode($params['return']);
		}
		else {
			$variables['return'] = '';
		}
		
		// form action
		$variables['form_action'] = site_url('users/post_registration');
		
		// populated values
		$variables['first_name'] = $smarty->CI->input->post('firstname');
		$variables['last_name'] = $smarty->CI->input->post('last_name');
		$variables['email'] = $smarty->CI->input->post('email');
		$variables['username'] = $smarty->CI->input->post('username');
		
		$smarty->CI->load->model('custom_fields_model');
		$custom_fields = $smarty->CI->custom_fields_model->get_custom_fields(array('group' => '1'));
		
		foreach ($custom_fields as $field) {
			$variables[$field['name']] = $smarty->CI->input->post($field['name']);
		}
						
		$return .= $smarty->parse_string($tagdata, $variables);
				
		return $return;
	}
}