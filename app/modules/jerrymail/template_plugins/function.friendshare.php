<?php

/*
* Friendshare Template Function
*
* Sends a piece of content to a friend via the Friendshare_model
*
* @return Redirect after send, possibly with error notice
*/
function smarty_function_friendshare ($params, $smarty, $template) {
	if (!$smarty->CI->input->post('friendshare_submit')) {
		return FALSE;
	}

	$smarty->CI->load->model('jerrymail/friendshare_model');
	
	if ($smarty->CI->input->post('content_id')) {
		// validate the post, we are trying to send to a friend
		$smarty->CI->load->library('form_validation');
		$smarty->CI->form_validation->set_rules('email','Email','valid_email|required');
		$smarty->CI->form_validation->set_rules('content_id','Content ID','required|is_natural');
		
		if ($smarty->CI->form_validation->run() !== FALSE) {
			$smarty->CI->friendshare_model->send($smarty->CI->input->post('content_id'), $smarty->CI->input->post('email'));
			return '<div>Thank you for sharing this article with a friend!</div>';
		}
	}
}