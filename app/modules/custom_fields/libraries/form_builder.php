<?php

/*
* Form Builder Class
*
* Deal with multiple Fieldtype objects and build an entire form.  Handles form-wide actions like
* validation.
*
*/
class Form_builder {
	public $CI;
	public $form = array();
	public $validation_errors = array();
	
	function __construct () {
		$this->CI =& get_instance();
	}
	
	function reset() {
		foreach ($this->form as $field) {
			unset($field);
		}
	
		$this->form = array();
		$this->validation_errors = array();
	}
	
	/**
	* Build Form from Group
	*
	* Creates an array in this object of all the fieldtype objects, from a custom field group
	*
	* @param int $custom_field_group_id
	*
	* @return boolean
	*/
	function build_form_from_group ($custom_field_group_id) {
		$this->reset();
		
		$custom_fields = $this->CI->custom_fields_model->get_custom_fields(array('group' => $custom_field_group_id));
		
		$this->CI->load->library('custom_fields/fieldtype');
	
		foreach ($custom_fields as $field) {
			$this->form[] =& $this->CI->fieldtype->load($field);
		}
	
		return TRUE;
	}
	
	/**
	* Build Form from Array
	*
	* Builds the internal form from an array from get_custom_fields()
	*
	* @param array get_custom_fields array
	*
	* @return boolean
	*/
	function build_form_from_array ($custom_fields) {
		$this->reset();
		
		$this->CI->load->library('custom_fields/fieldtype');
	
		foreach ($custom_fields as $field) {
			$this->form[] = $this->CI->fieldtype->load($field);
		}
	
		return TRUE;
	}
	
	function validate_post () {
		// initial rules-based validation
		$this->CI->load->library('form_validation');
		reset($this->form);
		foreach ($this->form as $field) {
			$rules = $field->validation_rules();
			
			if (!empty($rules)) {
				$this->CI->form_validation->set_rules($field->name, $field->label, implode('|',$field->validation_rules()));
			}
		}
		
		if ($this->CI->form_validation->run() === FALSE) {
			$this->validation_errors = array_merge($this->validation_errors(TRUE),explode('||',str_replace(array('<p>','</p>'),array('','||'),validation_errors())));
		}
		
		// secondary additional validation
		reset($this->form);
		foreach ($this->form as $field) {
			if ($field->validate_post() === FALSE) {
				$this->validation_errors[] = $field->validation_error;
			}
		}
		
		if (!empty($this->validation_errors)) {
			return FALSE;
		}
	}
	
	function validation_errors ($array = FALSE) {
		$return = '';
		$errors = array();
		
		// format like the CodeIgniter function if they don't want an array
		foreach ($this->validation_errors as $error) {
			if (empty($error) or strlen($error) < 2) {
				continue;
			}
			
			// always have period at end
			$error = rtrim($error, '.') . '.';
			
			$errors[] = $error;
			
			$return .= '<p> ' . $error . '</p>';
		}
		
		if ($array == TRUE) {
			return $errors;
		}
	
		return $return;
	}
	
	function post_to_array () {
		reset($this->form);
		
		$array = array();
		
		foreach ($this->form as $field) {
			$array[$field->name] = $field->post_to_value();	
		}
		
		return $array;
	}
	
	function set_values ($values = array()) {
		reset($this->form);
		
		foreach ($this->form as $field) {
			$field->value($values[$field->name]);
		}
		
		return TRUE;
	}
	
	function clear_defaults () {
		reset($this->form);
		
		foreach ($this->form as $field) {
			$field->default_value($field->name, FALSE);
		}
		
		return TRUE;
	}
	
	function output_admin () {
		reset($this->form);
		
		$return = '';
		
		foreach ($this->form as $field) {
			$return .= $field->output_admin();
		}
		
		return $return;
	}
}