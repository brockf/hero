<?php

/*
* Checkbox Fieldtype
*
* @extends Fieldtype
* @class Checkbox_fieldtype
*/

class Checkbox_fieldtype extends Fieldtype {
	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'Checkbox';
		$this->fieldtype_description = 'A single on/off checkbox.';
		$this->validation_error = '';
		$this->db_column = 'TINYINT(1)';
	}
	
	function output_shared () {
		$this->field_class('checkbox');
	
		$attributes = array(
						'type' => 'checkbox',
						'name' => $this->name,
						'value' => '1',
						'class' => implode(' ', $this->field_classes)
						);
						
		if (!empty($this->value)) {
			$attributes['checked'] = 'checked';
		}
		
		// compile attributes
		$attributes = $this->compile_attributes($attributes);
		
		return $attributes;
	}
	
	function output_admin () {
		if (empty($_POST)) {
			$this->value($this->default);
		}
	
		$attributes = $this->output_shared();
		
		$help = ($this->help == FALSE) ? '' : '<div class="help">' . $this->help . '</div>';
		
		// build HTML
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>
						<input ' . $attributes . ' />
						' . $help . '
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
		$return = '<input ' . $attributes . ' />';
					
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
		return ($this->CI->input->post($this->name) != FALSE) ? '1' : '0';
	}
	
	function field_form () {
		// build fieldset with admin_form which is used when editing a field of this type
	}
	
	function field_form_process () {
		// build array for database
	}
}