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

function smarty_block_restricted ($params, $tagdata, $smarty, $repeat){
	if (!$repeat) {		
		
				
		return $return;
	}
}