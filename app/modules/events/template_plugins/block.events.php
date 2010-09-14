<?php

/**
* Get Content
*
* Loads content item(s) from the content database
*
* @param int $type The content type (required)
* @param int $id The content ID
* @param int|array $topic The topic ID like "X" or "X|Y|Z"
* @param string $keyword Perform a fulltext search on the database
* @param string $sort The column to sort by
* @param string $sort_dir The direction, ASC or DESC, to sort
* @param int $limit The number of items to pull
* @param int $offset Database offset
*
*/

function smarty_block_content ($params, $tagdata, $smarty, $repeat) {
	if (!$repeat) {		
		if (!isset($params['id']) and (!isset($params['type']) or empty($params['type']))) {
			$return = 'You must specify a "type" parameter for template {content} calls.';
		}
		else {
			// deal with filters
			$filters = array();
			
			// param: topic
			if (isset($params['topic']) and !empty($params['topic'])) {
				if (strpos($params['topic'],'|') !== FALSE) {
					$topics = explode('|', $params['topic']);
					$filters['topic'] = $topics;
				}
				else {
					$filters['topic'] = $params['topic'];
				}
			}
			
			// param: keyword
			if (isset($params['keyword']) and !empty($params['keyword'])) {
				$filters['keyword'] = $params['keyword'];
			}
			
			// param: type
			if (isset($params['type'])) {
				$filters['type'] = $params['type'];
			}
			
			// param: id
			if (isset($params['id'])) {
				$filters['id'] = $params['id'];
			}
			
			// param: sort
			if (isset($params['sort']) and !empty($params['sort'])) {
				$filters['sort'] = $params['sort'];
			}
			
			// param: sort_dir
			if (isset($params['sort_dir']) and !empty($params['sort_dir'])) {
				$filters['sort_dir'] = $params['sort_dir'];
			}
			
			// param: limit
			if (isset($params['limit']) and !empty($params['limit'])) {
				$filters['limit'] = $params['limit'];
			}
			
			// param: offset
			if (isset($params['offset'])) {
				$filters['offset'] = $params['offset'];
			}
			
			// make content request
			$smarty->CI->load->model('events/events_model');
			$content = $smarty->CI->events_model->get_events($filters);
			
			$return = '';
			
			if (!empty($content)) {
				// output to $return
				foreach ($content as $item) {
					$return .= $smarty->parse_string($tagdata, $item);
				}	
			}
			
			// assign count variables
			$smarty->assign('content_count', count($content));
			
			if (isset($filters['limit'])) { unset($filters['limit']); }
			if (isset($filters['offset'])) { unset($filters['offset']); }	
			
			$total_content = $smarty->CI->content_model->get_contents($filters);
			$smarty->assign('content_total_count', (!empty($total_content)) ? count($total_content) : 0);
		}
				
		return $return;
	}
}