<?php

/**
* Restricted Content
*
* Displays the content between the tags if the user is in groups specified by
* "in_group" and/or not in groups specified by "not_in_group".
*
* @param int|string $in_group The group ID to show content for (e.g., "1"), or series of group ID's (e.g., "1|2|3)
* @param int|string $not_in_group The group ID to show content for if the user isn't in it (e.g., "1"), or series of group IDs (e.g., "1|2|3")
*
* @return string $content Content between {restricted}{/restricted} tags
*/

function smarty_block_restricted ($params, $tagdata, &$smarty, &$repeat){
	if (!$repeat) {		
		// by default, content is hidden
		$show_content = FALSE;
		$return = '';
		
		if (isset($params['in_group'])) {
			if (!is_array($params['in_group'])) {
				$groups = (strpos($params['in_group'], '|') !== FALSE) ? explode('|', $params['in_group']) : $params['in_group'];
			}
			else {
				$groups = $params['in_group'];
			}
			
			if ($smarty->CI->user_model->in_group($groups)) {
				// do we have a not_in_group, though?
				
				if (isset($params['not_in_group'])) {
					// yes, so we have to verify this
					if (!is_array($params['not_in_group'])) {
						$groups = (strpos($params['not_in_group'], '|') !== FALSE) ? explode('|', $params['not_in_group']) : $params['not_in_group'];
					}
					else {
						$groups = $params['not_in_group'];
					}
					
					if ($smarty->CI->user_model->not_in_group($groups)) {
						$show_content = TRUE;
					}
				}
				else {
					// nope, so we're good
					$show_content = TRUE;
				}
			}
		}
		elseif (isset($params['not_in_group'])) {
			// we only have a not_in_group call
			if (!is_array($params['not_in_group'])) {
				$groups = (strpos($params['not_in_group'], '|') !== FALSE) ? explode('|', $params['not_in_group']) : $params['not_in_group'];
			}
			else {
				$groups = $params['not_in_group'];
			}
			
			if ($smarty->CI->user_model->not_in_group($groups)) {
				$show_content = TRUE;
			}
		}
		
		if ($show_content === TRUE) {
			$return = $tagdata;
		}
				
		return $return;
	}
}