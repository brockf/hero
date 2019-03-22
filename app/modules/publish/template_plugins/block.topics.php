<?php

/**
* Topics
*
* Displays a list of site topics
*
* @param string $var Variable name in array
* @param int $id The topic ID
* @param int $parent The parent topic ID
* @param string $sort The column to sort by (default: Topic Name)
* @param string $sort_dir The direction, ASC or DESC, to sort (default: ASC)
* @param int $limit The number of items to pull
* @param int $offset Database offset
*
*/

function smarty_block_topics ($params, $tagdata, &$smarty, &$repeat){
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {topics} calls.  This parameter specifies the variable name for the returned array.');
	}
	// deal with filters
	$filters = array();
	
	// param: id
	if (isset($params['id'])) {
		$filters['id'] = $params['id'];
	}
	
	// param: sort
	if (isset($params['sort'])) {
		$filters['sort'] = $params['sort'];
	}
	
	// param: sort_dir
	if (isset($params['sort_dir'])) {
		$filters['sort_dir'] = $params['sort_dir'];
	}
	
	// param: parent
	if (isset($params['parent'])) {
		$filters['parent'] = $params['parent'];
	}
	
	// param: limit
	if (isset($params['limit'])) {
		$filters['limit'] = $params['limit'];
	}
	
	// param: offset
	if (isset($params['offset'])) {
		$filters['offset'] = $params['offset'];
	}
	
	// initialize block loop
	$data_name = $smarty->CI->smarty->loop_data_key($filters);
	
	if ($smarty->CI->smarty->loop_data($data_name) === FALSE) {
		// make content request
		$smarty->CI->load->model('publish/topic_model');
		$topics = $smarty->CI->topic_model->get_topics($filters);
	}
	else {
		$topics = FALSE;
	}
	
	$smarty->CI->smarty->block_loop($data_name, $topics, (string)$params['var'], $repeat);
			
	echo $tagdata;
}