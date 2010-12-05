<?php

/*
* Custom Field Template Function
*
* Displays a custom field from a standard custom field data array
*
* @param array $field The data array for the field as created by custom_fields_model
* @param string|array $value The current value (arrays for multiselects)
*
* @return string Field HTML
*/
function smarty_function_custom_field ($params, $smarty, $template) {
	$field = $params['field'];
	
	// load field as object and display the field
	$smarty->CI->load->library('custom_fields/fieldtype');

	// initialize field
	$field_object = $smarty->CI->fieldtype->load($field);
	
	// check for error
	if ($field_object === FALSE) {
		$smarty->trigger_error('Unable to load custom field: ' . $field['type']);
	}
	
	// add value from parameter if it's there
	if (isset($params['value'])) {
		$field_object->value($params['value']);
	}
	else {
		// older templates are missing the "value" parameter declaration, let's do another check
		$values = ($smarty->CI->input->get('values')) ? unserialize(query_value_decode($smarty->CI->input->get('values'))) : array();
		
		if (isset($values[$field_object->name])) {
			$field_object->value($values[$field_object->name]);
		}
	}
	
	// output field
	return $field_object->output_frontend();
}