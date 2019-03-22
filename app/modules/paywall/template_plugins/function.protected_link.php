<?php

/**
* Protected Link Template Function
*
* Creates a unique, encrypted URL for a resource to protect it.
*
* @param $url A relative/absolute URL resource
* @param $groups An array of group ID's, or a single group ID, to restrict access to
*
* @return string $protected_link
*/
function smarty_function_protected_link ($params, $smarty) {
	if (!isset($params['url']) or empty($params['url'])) {
		return 'invalid url parameter';
	}
	elseif (!isset($params['groups'])) {
		return 'invalid groups parameter';
	}
	
	// prep $groups
	if (is_string($params['groups'])) {
		if (strpos($params['groups'],'|') !== FALSE) {
			$params['groups'] = explode('|', $params['groups']);
		}
		else {
			$params['groups'] = array($params['groups']);
		}
	}
	
	if (empty($params['groups'])) {
		$params['groups'] = array();
	}
	
	// create serialized version of our data so we can see if we already have a link in the database
	$filename = isset($params['filename']) ? $params['filename'] : null;
	$serialized = serialize(array('url' => $params['url'], 'groups' => $params['groups'], 'filename' => $filename));

	// check links database
	$result = $smarty->CI->db->select('link_url_path')
							 ->where('link_parameter',$serialized)
							 ->where('link_module','paywall')
							 ->get('links');
							 
	if ($result->num_rows() > 0) {
		return site_url($result->row()->link_url_path);
	}							 
	else {
		// we must create the link
		$smarty->CI->load->model('link_model');
		$smarty->CI->load->helper('string');
		// 28 is long enough to suggest sophistication, but short enough to be reasonable...
		$string = random_string('alnum',28);
		
		// make sure it's unique
		$url_path = $smarty->CI->link_model->get_unique_url_path($string);
		
		$smarty->CI->link_model->new_link($url_path, FALSE, 'Auto-Protected Link', 'Protected Link', 'paywall', 'protected_link', 'redirect', $serialized);
		
		// start with URL
		return site_url($url_path);
	}
}