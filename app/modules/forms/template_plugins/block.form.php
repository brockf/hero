<?php

/**
* Form Template Plugin
*
* Display a published form in a template
*
* @param string $var
* @param int $id
*/

function smarty_block_form ($params, $tagdata, &$smarty, &$repeat){
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {form} calls.  This parameter specifies the variable name for the returned array.');
	}
	elseif (!isset($params['id'])) {
		show_error('You are missing the "id" parameter for the {form} template tag.');
	}
	else {
		$variables = array();
		
		// get the form
		$smarty->CI->load->model('forms/form_model');
		$form = $smarty->CI->form_model->get_form($params['id']);
		
		
		if (empty($form)) {
			return show_error('Form does not exist.');
		}
		
		// we don't want non values, so we'll fill the $values array with empty placeholders
		$smarty->CI->load->model('custom_fields_model');
		$fields = $smarty->CI->custom_fields_model->get_custom_fields(array('group' => $form['custom_field_group_id']));
		
		foreach ($fields as $field) {
			$values[$field['name']] = '';
		}
	
		$smarty->assign($params['var'], $form);
				
		echo $tagdata;
	}
}