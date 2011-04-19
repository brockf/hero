<?php

/**
* Get Content in Topic
*
* Loads content item(s) from specified topic(s)
*
* @param string $var Variable name in array
* @param int $topic The topic ID like "X" or "X|Y|Z"
* @param string $sort The column to sort by
* @param string $sort_dir The direction, ASC or DESC, to sort
* @param int $limit The number of items to pull
* @param int $offset Database offset
*
*/

function smarty_block_content_in_topic ($params, $tagdata, &$smarty, &$repeat) {
	if (!isset($params['topic'])) {
		show_error('You must specify a "topic" parameter for template {content_in_topic} calls.');
	}
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {content_in_topic} calls.  This parameter specifies the variable name for the returned array.');
	}
	else {
		// deal with filters
		$filters = array();
		
		// param: topic
		if (strpos($params['topic'],'|') !== FALSE) {
			$topics = explode('|', $params['topic']);
			$filters['topic'] = $topics;
		}
		else {
			$filters['topic'] = $params['topic'];
		}
		
		// param: sort
		if (isset($params['sort'])) {
			$filters['sort'] = $params['sort'];
		}
		
		// param: sort_dir
		if (isset($params['sort_dir'])) {
			$filters['sort_dir'] = $params['sort_dir'];
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
			$smarty->CI->load->model('publish/content_model');
			$content = $smarty->CI->content_model->get_contents($filters);
		}
		else {
			$content = FALSE;
		}
		
		$smarty->CI->smarty->block_loop($data_name, $content, (string)$params['var'], $repeat);
	}		
	
	echo $tagdata;
}