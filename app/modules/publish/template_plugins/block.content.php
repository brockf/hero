<?php

/**
* Get Content
*
* Loads content item(s) from the content
*
* @param int $type The content type (required)
* @param int|array $topic The topic ID like "X" or "X|Y|Z"
* @param string $sort The column to sort by
* @param string $sort_dir The direction, ASC or DESC, to sort
* @param int $limit The number of items to pull
* @param string $date_format The format (in PHP date() style) to use for dates
*
*/

function smarty_block_content ($params, $tagdata, $smarty, $repeat){
	if (!$repeat) {		
		/*
		* Get Contents
		*
		* Gets content by filters
		* If an ID or Type ID is present in filters, it will retrieve all content data from the specific content table
		*
		* @param date $filters['start_date'] Only content after this date
		* @param date $filters['end_date'] Only content before this date
		* @param string $filters['author_like'] Only content created by this user (by username, text search)
		* @param int $filters['type'] Only content of this type
		* @param int $filters['id']
		* @param int $filters['topic']
		*
		* @return array|boolean Array of content, or FALSE
		*/
		
		if (!isset($params['type']) or empty($params['type'])) {
			$return = 'You must specify a "type" parameter for template {content} calls.';
		}
		else {
			// deal with filters
			$filters = array();
			
			// param: topic
			if (isset($params['topic'])) {
				if (strpos($params['topic'],'|') !== FALSE) {
					$topics = explode('|', $params['topic']);
					$filters['topic'] = $topics;
				}
				else {
					$filters['topic'] = $params['topic'];
				}
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
			
			// param: date_format
			if (isset($params['date_format'])) {
				$filters['date_format'] = $params['date_format'];
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