<?php

/*
* Radio Fieldtype
*
* @extends Fieldtype
* @class Radio_fieldtype
*/

class Radio_fieldtype extends Fieldtype {
	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'Radio button';
		$this->fieldtype_description = 'Select one of many radio buttons in a set.';
		$this->validation_error = '';
		$this->db_column = 'VARCHAR(100)';
	}
	
	function output_shared () {
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		$this->field_class('radio');
		
		// prep final attributes	
		$attributes = array(
						'type' => 'radio',
						'name' => $this->name,
						'class' => implode(' ', $this->field_classes)
						);
		
		// compile attributes
		$attributes = $this->compile_attributes($attributes);
		
		return $attributes;
	}
	
	function output_admin () {
		if (empty($this->value) and $this->CI->input->post($this->name) == FALSE) {
			$this->value($this->default);
		}
	
		$attributes = $this->output_shared();
		
		$help = ($this->help == FALSE) ? '' : '<div class="help">' . $this->help . '</div>';
		
		// build HTML
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>';
						
		foreach ($this->options as $option) {
			$checked = ($this->value == $option['value']) ? 'checked="checked"' : '';
			$return .= '<input ' . $attributes . ' value="' . $option['value'] . '" ' . $checked . ' /> ' . $option['name'] . '&nbsp;&nbsp;&nbsp;';
		}
		
		$return .= ' ' . $help . '
					</li>';
					
		return $return;
	}
	
	function output_frontend () {
		if (empty($this->value)) {
			if (empty($_POST)) {
				$this->value($this->default);
			}
			elseif ($this->CI->input->post($this->name) != FALSE) {
				$this->value($this->CI->input->post($this->name));
			}
		}
		
		$attributes = $this->output_shared();
		
		// build HTML
		$return = '';
		
		foreach ($this->options as $option) {
			$checked = ($this->value == $option['value']) ? 'checked="checked"' : '';
			$return .= '<input ' . $attributes . ' value="' . $option['value'] . '" ' . $checked . ' /> ' . $option['name'] . '&nbsp;&nbsp;&nbsp;';
		}
					
		return $return;
	}
	
	function validation_rules () {
		$rules = array();
		
		// check required
		if ($this->required == TRUE) {
			$rules[] = 'required';
		}
		
		return $rules;
	}
	
	function validate_post () {
		// nothing extra to validate here other than the rulers in $this->validators
		return TRUE;
	}
	
	function post_to_value () {
		return $this->CI->input->post($this->name);
	}
	
	function field_form () {
		// build fieldset with admin_form which is used when editing a field of this type
	}
	
	function field_form_process () {
		// build array for database
	}
}