<?php

/*
* Jerry Thumbnail Function
*
* Displays the latest Jerry Thumbnail with proper sizing, etc.
*
* @return string $image_src To be used like <img src="{jerry_thumbnail ...}" />
*/
function smarty_function_jerry_thumbnail ($params, $smarty) {
	$smarty->CI->load->model('publish/content_model');
	$content = $smarty->CI->content_model->get_contents(array('type' => '1', 'sort' => 'content.content_date', 'sort_dir' => 'desc', 'limit' => '1'));
	
	$content = $content[0];
	
	if (isset($params['id'])) {
		$content_2 = $smarty->CI->content_model->get_content($params['id']);
		
		// we only show the thumbnail if this ID is the latest content item, not an archived item
		if ($content_2['id'] != $content['id']) {
			return '';
		}
	}
	
	$smarty->CI->load->helper('image_thumb');
	
	if (!isset($params['img_src'])) {
		return image_thumb($content['feature_image'], $content['image_size'], $content['image_size']);
	}
	else {
		return '<img src="' . image_thumb($content['feature_image'], $content['image_size'], $content['image_size']) . '" alt="" />';
	}
}