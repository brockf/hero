<?php

/*
* Multicheckbox Fieldtype
*
* @extends Fieldtype
* @class Multicheckbox_fieldtype
*/

class Multicheckbox_fieldtype extends Fieldtype {
	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'Multicheckbox Dropdown';
		$this->fieldtype_description = 'Check one or many options in a list of checkboxes.';
		$this->validation_error = '';
		$this->db_column = 'TEXT';
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
		
		// we may be passed a serialized array
		if (@is_array(unserialize($this->value))) {
			$this->value = unserialize($this->value);
		}
		
		$this->output_shared();
		
		$help = ($this->help == FALSE) ? '' : '<div class="help">' . $this->help . '</div>';
		
		// build HTML
		$this->CI->load->helper('form');
		
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>
						<div style="float: left; width: 500px">';
						
		foreach ($this->options as $option) {
			$checked = ((is_array($this->value) and in_array($option['value'], $this->value)) or (!is_array($this->value) and $this->value == $option['value'])) ? ' checked="checked" ' : '';
			$return .= '<div class="check_option"><input type="checkbox" name="' . $this->name . '[]" value="' . $option['value'] . '" ' . $checked . ' /> ' . $option['name'] . '</div>';	
		}
		
		$return .= '				</div>
						' . $help . '
					</li>';
					
		return $return;
	}
	
	function output_frontend () {
		if (empty($this->value)) {
			if (empty($_POST)) {
				$this->value($this->default);
			}
			elseif (isset($_POST[$this->name])) {
				$this->value($_POST[$this->name]);
			}
		}
		
		// we may be passed a serialized array
		if (@is_array(unserialize($this->value))) {
			$this->value = unserialize($this->value);
		}
		
		$attributes = $this->output_shared();
		
		// build HTML
		$this->CI->load->helper('form');
		
		$return = '';
		
		foreach ($this->options as $option) {
			$checked = ((is_array($this->value) and in_array($option['value'], $this->value)) or (!is_array($this->value) and $this->value == $option['value'])) ? ' checked="checked" ' : '';
			$return .= '<div class="check_option"><input type="checkbox" name="' . $this->name . '[]" value="' . $option['value'] . '" ' . $checked . ' /> ' . $option['name'] . '</div>';	
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
		$array = $this->CI->input->post($this->name);
		
		if (!is_array($array)) {
			$array = array($array);
		}
		
		return serialize($array);
	}
	
	function field_form ($edit_id = FALSE) {
		// build fieldset with admin_form which is used when editing a field of this type
		$this->CI->load->library('custom_fields/form_builder');
		$this->CI->form_builder->reset();
		$options = $this->CI->form_builder->add_field('textarea');
		$options->label('Options')
			  ->name('options')
			  ->width('500px')
			  ->required(TRUE)
			  ->height('150px')
			  ->help('Enter each option on a newline. If you want the option database value to be different than the option the user actually selects, enter it in the format of "Name=Value".');
		
		$default = $this->CI->form_builder->add_field('textarea');
		$default->label('Default Selection(s)')
	          ->name('default')
	          ->help('To select multiple values by default, enter each value on a newline.');
	          
	    $help = $this->CI->form_builder->add_field('textarea');
	    $help->label('Help Text')
	    	 ->name('help')
	    	 ->width('500px')
	    	 ->height('80px')
	    	 ->help('This help text will be displayed beneath the field.  Use it to guide the user in responding correctly.');
	          
	    $required = $this->CI->form_builder->add_field('checkbox');
	    $required->label('Required Field')
	    	  ->name('required')
	    	  ->help('If checked, at least one checkbox must be checked a successful form submission.');
	    	  
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
	    	
	    	// format default
	    	if (@is_array(unserialize($field['default']))) {
	    		$field['default'] = implode("\n", unserialize($field['default']));
	    	}
	    	
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
		
		// we may need to serialize the default options
		if (strpos($this->CI->input->post('default')) !== FALSE) {
			$defaults = explode("\n", $this->CI->input->post('default'));
			$final_defaults = array();
			foreach ($defaults as $default) {
				$default = trim($default);
				
				if (!empty($default)) {
					$final_defaults[] = $default;
				}
			}
			
			$final_defaults = serialize($final_defaults);
		}
		else {
			$final_defaults = $this->CI->input->post('default');
		}
		
		return array(
					'name' => $this->CI->input->post('name'),
					'type' => $this->CI->input->post('type'),
					'default' => $final_defaults,
					'options' => $this->CI->input->post('options'),
					'help' => $this->CI->input->post('help'),
					'required' => ($this->CI->input->post('required')) ? TRUE : FALSE
				);
	}
}