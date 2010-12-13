<?php

/*
* Custom Field Template Function
*
* Displays a custom field from a standard custom field data array.  It uses the fieldtype library's
* output_frontend() method (returns an isolated field).
*
* @param array $field The data array for the field as created by custom_fields_model
* @param string|array $value (Optional) The current value (arrays for multiselects)
* @param string $default (Optional) Set the default value (particularly useful for disabling defaults on forms that should not reset to defaults)
*
* @return string Field HTML
*/
function smarty_function_custom_field ($params, $smarty) {
	$field = $params['field'];
	
	// we don't throw a nasty error here, because sometimes an empty $custom_fields array gets into the mix and
	// we have to be gentle with this
	if (empty($field)) {
		return FALSE;
	}
	
	// load field as object and display the field
	$smarty->CI->load->library('custom_fields/fieldtype');

	// initialize field
	$field_object =& $smarty->CI->fieldtype->load($field);
	
	// check for error
	if ($field_object === FALSE) {
		show_error('Unable to load custom field: ' . $field['type']);
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
	
	// use default parameter if supplied
	if (isset($params['default'])) {
		$field_object->default_value($params['default']);
	}
	
	// output field
	$html = $field_object->output_frontend();
	
	unset($field_object);
	
	return $html;
}