<?php

/*
* Select Fieldtype
*
* @extends Fieldtype
* @class Select_fieldtype
*/

class Select_fieldtype extends Fieldtype {
	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'Select Dropdown';
		$this->fieldtype_description = 'Select one of many options in a dropdown list.';
		$this->validation_error = '';
		$this->db_column = 'VARCHAR(150)';
	}
	
	function output_shared () {
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		$this->field_class('select');

		return;		
	}
	
	function output_admin () {
		if (empty($this->value) and $this->CI->input->post($this->name) == FALSE) {
			$this->value($this->default);
		}
	
		$this->output_shared();
		
		$help = ($this->help == FALSE) ? '' : '<div class="help">' . $this->help . '</div>';
		
		// build HTML
		$this->CI->load->helper('form');
		
		$options = array();
		foreach ($this->options as $option) {
			$options[$option['value']] = $option['value'];
		}
		
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>
						' . form_dropdown($this->name, $options, $this->value) . '
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
		$this->CI->load->helper('form');
		
		$options = array();
		foreach ($this->options as $option) {
			$options[$option['value']] = $option['value'];
		}
		
		$return = form_dropdown($this->name, $options, $this->value);
					
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