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
		$this->fieldtype_name = 'Radio Button';
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
	
	function field_form ($edit_id = FALSE) {
		// build fieldset with admin_form which is used when editing a field of this type
		$this->CI->load->library('custom_fields/form_builder');
		$this->CI->form_builder->reset();
		$options = $this->CI->form_builder->add_field('textarea');
		$options->label('Options')
			  ->name('options')
			  ->width('500px')
			  ->height('150px')
			  ->required(TRUE)
			  ->help('Enter each option on a newline. If you want the option database value to be different than the option the user actually selects, enter it in the format of "Name=Value".');
		
		$default = $this->CI->form_builder->add_field('text');
		$default->label('Default Selection')
	          ->name('default');
	          
	    $help = $this->CI->form_builder->add_field('textarea');
	    $help->label('Help Text')
	    	 ->name('help')
	    	 ->width('500px')
	    	 ->height('80px')
	    	 ->help('This help text will be displayed beneath the field.  Use it to guide the user in responding correctly.');
	          
	    $required = $this->CI->form_builder->add_field('checkbox');
	    $required->label('Required Field')
	    	  ->name('required')
	    	  ->help('If checked, this field must not be empty for a successful form submission.');
	    	  
	    if (!empty($edit_id)) {
	    	$this->CI->load->model('custom_fields_model');
	    	$field = $this->CI->custom_fields_model->get_custom_field($edit_id);
	    	
	    	// format options
	    	if (isset($field['options'])) {
				$options_text = '';
				foreach ($field['options'] as $option) {
					if ($option['value'] != $option['name']) {
						$options_text .= $option['name'] . '=' . $option['value'] . "\n";
					}
					else {
						$options_text .= $option['name'] . "\n";
					}
				}
			}
			else {
				$options_text = '';
			}
			$field['options'] = $options_text;
	    	
	    	$options->value($field['options']);
	    	$default->value($field['default']);
	    	$help->value($field['help']);
	    	$required->value($field['required']);
	    }	  
	          
		return $this->CI->form_builder->output_admin();      
	}
	
	function field_form_process () {
		// build array for database
		
		// $options will be automatically serialized by the custom_fields_model::new_custom_field() method
		
		return array(
					'name' => $this->CI->input->post('name'),
					'type' => $this->CI->input->post('type'),
					'default' => $this->CI->input->post('default'),
					'options' => $this->CI->input->post('options'),
					'help' => $this->CI->input->post('help'),
					'required' => ($this->CI->input->post('required')) ? TRUE : FALSE
				);
	}
}