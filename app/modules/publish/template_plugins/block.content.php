<?php

/**
* Get Content
*
* Loads content item(s) from the content database
*
* @param string $var Variable name in array
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

function smarty_block_content ($params, $tagdata, &$smarty, &$repeat) {
	if (!isset($params['id']) and (!isset($params['type']) or empty($params['type']))) {
		show_error('You must specify a "type" parameter for template {content} calls if you are not specifying an "id" parameter.');
	}
	elseif (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {content} calls.  This parameter specifies the variable name for the returned array.');
	}
	else {
		// type may not be the ID, but the system name
		if (isset($params['type'])) {
			if (!is_numeric($params['type'])) {
				$smarty->CI->load->model('publish/content_type_model');
				$type = $smarty->CI->content_type_model->get_content_types(array('system_name' => $params['type']));
				$type = $type[0];
			}
			else {
				$smarty->CI->load->model('publish/content_type_model');
				$type = $smarty->CI->content_type_model->get_content_type($params['type']);
			}

			// we have a type, right?
			if (empty($type) or !isset($type)) {
				show_error('Could not load content type data for "' . $params['type'] . '"');
			}

			// load the proper type ID, if not numeric
			$params['type'] = $type['id'];
		}

		// deal with filters
		$filters = array();

		if (isset($type['custom_field_group_id'])) {
			// deal with custom fields first
			$smarty->CI->load->model('custom_fields_model');
			$custom_fields = $smarty->CI->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));

			if (isset($custom_fields) and is_array($custom_fields)) {
				foreach ($custom_fields as $field) {
					if (isset($params[$field['name']])) {
						if (isset($type['system_name'])) {
							$filters[$type['system_name'] . '.' . $field['name']] = $params[$field['name']];
						}
						else {
							$filters[$field['name']] = $params[$field['name']];
						}
					}
					elseif (isset($type['system_name']) and isset($params[$type['system_name'] . '.' . $field['name']])) {
						$filters[$type['system_name'] . '.' . $field['name']] = $params[$type['system_name'] . '.' . $field['name']];
					}
				}
				reset($custom_fields);
			}
		}

		// param: author_id (by id)
		if (isset($params['author_id']) and !empty($params['author_id']))
		{
			$filters['author'] = $params['author_id'];
		}

		// param: author_name
		if (isset($params['author_name']) and !empty($params['author_name']))
		{
			$filters['author_like'] = $params['author_name'];
		}

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

		// param: on_date
		if (isset($params['on_date'])) {
			$filters['start_date'] = date('Y-m-d 00:00:00', strtotime($params['on_date']));
			$filters['end_date'] = date('Y-m-d 23:59:59', strtotime($params['on_date']));
		}

		// param: after_date
		if (isset($params['after_date']))
		{
			$filters['start_date'] = date('Y-m-d 00:00:00', strtotime($params['after_date']));
			$filters['allow_future'] = true;
		}

		// param: before_Date
		if (isset($params['before_date']))
		{
			$filters['end_date'] = date('Y-m-d 00:00:00', strtotime($params['before_date']));
		}

		// param: allow_future
		if (isset($params['allow_future']))
		{
			$filters['allow_future'] = true;
		}

		// initialize block loop
		$data_name = $smarty->CI->smarty->loop_data_key($filters);

		if ($smarty->CI->smarty->loop_data($data_name) === FALSE) {
			// make content request
			$smarty->CI->load->model('publish/content_model');
			$content = $smarty->CI->content_model->get_contents($filters);

			// assign count variables
			$smarty->assign('content_count', count($content));

			if (isset($filters['limit'])) { unset($filters['limit']); }
			if (isset($filters['offset'])) { unset($filters['offset']); }

			$total_content = $smarty->CI->content_model->count_content($filters);
			$smarty->assign('content_total_count', $total_content);
		}
		else {
			$content = FALSE;
		}

		$smarty->CI->smarty->block_loop($data_name, $content, (string)$params['var'], $repeat);
	}

	echo $tagdata;
}