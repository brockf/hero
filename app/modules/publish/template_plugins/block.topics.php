<?php

/**
* Topics
*
* Displays a list of site topics
*
* @param int $id The topic ID
* @param int $parent The parent topic ID
* @param string $sort The column to sort by (default: Topic Name)
* @param string $sort_dir The direction, ASC or DESC, to sort (default: ASC)
* @param int $limit The number of items to pull
* @param int $offset Database offset
*
*/

function smarty_block_topics ($params, $tagdata, $smarty, $repeat){
	if (!$repeat) {		
		// deal with filters
		$filters = array();
		
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
		
		// make content request
		$smarty->CI->load->model('publish/topic_model');
		$topics = $smarty->CI->topic_model->get_topics($filters);
		
		$return = '';
		
		if (!empty($topics)) {
			// output to $return
			foreach ($topics as $topic) {
				$return .= $smarty->parse_string($tagdata, $topic);
			}	
		}
				
		return $return;
	}
}