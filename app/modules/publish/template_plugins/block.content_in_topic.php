<?php

/**
* Get Content in Topic
*
* Loads content item(s) from specified topic(s)
*
* @param int $topic The topic ID like "X" or "X|Y|Z"
* @param string $sort The column to sort by
* @param string $sort_dir The direction, ASC or DESC, to sort
* @param int $limit The number of items to pull
* @param int $offset Database offset
*
*/

function smarty_block_content_in_topic ($params, $tagdata, $smarty, $repeat){
	if (!$repeat) {		
		if (!isset($params['topic'])) {
			$return = 'You must specify a "topic" parameter for template {content_in_topic} calls.';
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
			
			// make content request
			$smarty->CI->load->model('publish/content_model');
			$content = $smarty->CI->content_model->get_contents($filters);
			
			$return = '';
			
			if (!empty($content)) {
				// output to $return
				foreach ($content as $item) {
					$return .= $smarty->parse_string($tagdata, $item);
				}	
			}
		}
				
		return $return;
	}
}