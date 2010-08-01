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
	
	// get current value
	if (isset($params['value'])) {
		$value = $params['value'];
	}
	elseif ($field['type'] == 'multiselect' and isset($_POST[$field['name']])) {
		$value = $_POST[$field['name']];
	}
	elseif ($smarty->CI->input->post($field['name'])) {
		// take value from $_POST
		$value = $smarty->CI->input->post($field['name']);
	}
	elseif (isset($field['default']) and !empty($field['default'])) {
		$value = $field['default'];
	}
	else {
		$value = FALSE;
	}
	
	// load form helper
	$smarty->CI->load->helper('form');
	
	if ($field['type'] == 'text') {
		$return = form_input(array(
									'name' => $field['name'],
									'id' => 'field_' . $field['name'],
									'value' => $value,
									'style' => 'width:' . $field['width']
								));
								
		$classes = array('text');
		
		if ($field['required'] == '1') {
			$classes[] = 'required';
		}
		if (in_array('numeric',$field['validators'])) {
			$classes[] = 'number';
		}
		if (in_array('alphanumeric',$field['validators'])) {
			$classes[] = 'alphanumeric';
		}
		
		$return = str_replace('/>',' class="' . implode(' ', $classes) . '" />', $return);
		
		return $return;
	}
	elseif ($field['type'] == 'password') {
		$return = form_password(array(
									'name' => $field['name'],
									'id' => 'field_' . $field['name'],
									'value' => $value,
									'style' => 'width:' . $field['width']
								));
								
		$classes = array('text','password');
		
		if ($field['required'] == '1') {
			$classes[] = 'required';
		}
		if (in_array('numeric',$field['validators'])) {
			$classes[] = 'number';
		}
		if (in_array('alphanumeric',$field['validators'])) {
			$classes[] = 'alphanumeric';
		}
		
		$return = str_replace('/>',' class="' . implode(' ', $classes) . '" />', $return);
		
		return $return;
	}
	elseif ($field['type'] == 'textarea') {
		$return = form_textarea(array(
									'name' => $field['name'],
									'id' => 'field_' . $field['name'],
									'value' => $value,
									'style' => 'width:' . $field['width']
								));
								
		$classes = array('text');
		
		if ($field['required'] == '1') {
			$classes[] = 'required';
		}
		if (in_array('numeric',$field['validators'])) {
			$classes[] = 'number';
		}
		if (in_array('alphanumeric',$field['validators'])) {
			$classes[] = 'alphanumeric';
		}
		
		$return = str_replace('<textarea','<textarea class="' . implode(' ', $classes) . '"', $return);
		
		return $return;
	}
	elseif ($field['type'] == 'select') {
		$return = form_dropdown($field['name'], $field['options'], $value);
								
		$classes = array();
		
		if ($field['required'] == '1') {
			$classes[] = 'required';
		}
		
		$return = str_replace('<select','<select id="field_' . $field['name'] . '" class="' . implode(' ', $classes) . '"', $return);
		
		return $return;
	}
	elseif ($field['type'] == 'multiselect') {
		$return = form_dropdown($field['name'] . '[]', $field['options'], $value);
								
		$classes = array();
		
		if ($field['required'] == '1') {
			$classes[] = 'required';
		}
		
		$return = str_replace('<select','<select id="field_' . $field['name'] . '" class="' . implode(' ', $classes) . '"', $return);
		
		return $return;
	}
	elseif ($field['type'] == 'radio') {
		$return = '';
		foreach ($field['options'] as $option) {
			$return .= '<span class="option">' . form_radio(array(
														'name' => $field['name'],
														'id' => 'field_' . $field['name'],
														'value' => $option['value']
														)) . '&nbsp;' . $option['name'] . '&nbsp;&nbsp;</span>';
		}
		
		return $return;
	}
	elseif ($field['type'] == 'checkbox') {
		$return = form_checkbox(array(
									'name' => $field['name'],
									'id' => 'field_' . $field['name'],
									'value' => '1'
								));
								
		return $return;
	}
	elseif ($field['type'] == 'file') {
		$return = form_upload(array(
									'name' => $field['name'],
									'id' => 'field_' . $field['name'],
									'value' => $value,
									'style' => 'width:' . $field['width']
								));
								
		$classes = array('upload');
		
		if ($field['required'] == '1') {
			$classes[] = 'required';
		}
		
		$return = str_replace('/>',' class="' . implode(' ', $classes) . '" />', $return);
		
		return $return;
	}
	elseif ($field['type'] == 'date') {
		// get value
		/*$value = strtotime($value);
		$value = explode('-',date('Y-m-d', $value));
		$selected_day = $value[2];
		$selected_month = $value[1];
		$selected_year = $value[0];*/
		$selected_day = '15';
		$selected_month = '2';
		$selected_year = '2010';
	
		// we are creating 3 dropdowns here
		// day
		$options = array();
		for ($i = 1; $i <= 31; $i++) {
			$options[str_pad($i, 2, "0", STR_PAD_LEFT)] = $i;
		}
		
		$return = form_dropdown($field['name'] . '_day', $options, $selected_day);
		
		// month
		$options = array();
		for ($i = 1; $i <= 12; $i++) {
        	$return[$i] = date('m - M',mktime(1, 1, 1, $i, 1, 2010));
        }
        
        $return .= form_dropdown($field['name'] . '_month', $options, $selected_month);
		
		// year
		$options = array();
	    for ($i = (date('Y') - 100); $i <= (date('Y') + 100); $i++) {
        	$options[$i] = $i;
        }
        
        $return = form_dropdown($field['name'] . '_year', $options, $selected_year);
								
		$classes = array();
		
		if ($field['required'] == '1') {
			$classes[] = 'required';
		}
		
		$return = str_replace('<select','<select class="' . implode(' ', $classes) . '" />', $return);
		
		return $return;
	}
	
	return;
}