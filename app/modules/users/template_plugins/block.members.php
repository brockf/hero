<?php

/**
* Members Listing Template Plugin
*
* Lists all the members of the site
*
* @param string $var Variable name for each member's data
* @param string $[any_custom_field_name]
* @param string $username
* @param string $name
* @param string $email
* @param int $group
* @param string $id
*/

function smarty_block_members ($params, $tagdata, &$smarty, &$repeat){
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {members} calls.  This parameter specifies the variable name for the returned array.');
	}
	else {
		$filters = array();
		
		if (isset($params['id'])) {
			$filters['id'] = $params['id'];
		}
		
		if (isset($params['username'])) {
			$filters['username'] = $params['username'];
		}
		
		if (isset($params['name'])) {
			$filters['name'] = $params['name'];
		}
		
		if (isset($params['email'])) {
			$filters['email'] = $params['email'];
		}
		
		if (isset($params['sort'])) {
			$filters['sort'] = $params['sort'];
		}
		
		if (isset($params['sort_dir'])) {
			$filters['sort_dir'] = $params['sort_dir'];
		}
		
		if (isset($params['group'])) {
			$filters['group'] = $params['group'];
		}
		
		if (isset($params['limit'])) {
			$filters['limit'] = $params['limit'];
		}
		
		if (isset($params['offset'])) {
			$filters['offset'] = $params['offset'];
		}
		
		// custom field params
		$fields = $smarty->CI->user_model->get_custom_fields();
		
		if (!empty($fields)) {
			foreach ($fields as $field) {
				if (isset($params[$field['name']])) {
					$filters[$field['name']] = $params[$field['name']];
				}
			}
		}
		
		// initialize block loop
		$data_name = $smarty->CI->smarty->loop_data_key($filters);
		
		if ($smarty->CI->smarty->loop_data($data_name) === FALSE) {
			// make content request
			$smarty->CI->load->model('users/user_model');
			$users = $smarty->CI->user_model->get_users($filters);
			
			// assign count variable
			if (isset($filters['limit'])) { unset($filters['limit']); }
			if (isset($filters['offset'])) { unset($filters['offset']); }	
			
			$total_users = $smarty->CI->user_model->get_users($filters);
			$total_users = !empty($total_users) ? count($total_users) : 0;
			$smarty->assign('members_total_count', $total_users);
		}
		else {
			$users = FALSE;
		}
		
		$smarty->CI->smarty->block_loop($data_name, $users, (string)$params['var'], $repeat);
			
		echo $tagdata;
	}
}