<?php

/*
* Select Fieldtype
*
* @extends Fieldtype
* @class Select_fieldtype
*/

class Select_fieldtype extends Fieldtype {
	/**
	* Constructor
	*
	* Assign basic properties to this fieldtype, useful in listing available fieldtypes.
	* Also defines the MySQL column format for fields of this type.
	*/
	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'Select Dropdown';
		$this->fieldtype_description = 'Select one of many options in a dropdown list.';
		$this->validation_error = '';
		$this->db_column = 'VARCHAR(150)';
	}
	
	/**
	* Output Shared
	*
	* Perform actions shared between admin- and frontend-outputs.  Assigns classes to the object.
	*
	* @return void
	*/
	function output_shared () {
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		$this->field_class('select');

		return;		
	}
	
	/**
	* Output Admin
	*
	* Returns the field with it's <label> in an <li> suitable for the admin forms.
	*
	* @return string $return The HTML to be included in a form
	*/
	function output_admin () {
		if ($this->value === FALSE and $this->CI->input->post($this->name) == FALSE) {
			$this->value($this->default);
		}
	
		$this->output_shared();
		
		$help = ($this->help == FALSE) ? '' : '<div class="help">' . $this->help . '</div>';
		
		// build HTML
		$this->CI->load->helper('form');
		
		$options = array();
		foreach ($this->options as $option) {
			$options[$option['value']] = $option['name'];
		}
		
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>
						' . form_dropdown($this->name, $options, $this->value) . '
						' . $help . '
					</li>';
					
		return $return;
	}
	
	/**
	* Output Frontend
	*
	* Returns the isolated field.  Likely called from the {custom_field} template function.
	*
	* @return string $return The HTML to be included in a form.
	*/
	function output_frontend () {
		if ($this->value === FALSE) {
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
			$options[$option['value']] = $option['name'];
		}
		
		$return = form_dropdown($this->name, $options, $this->value);
					
		return $return;
	}
	
	/**
	* Validation Rules
	*
	* Return an array of CodeIgniter form_validation rules for this fieldtype.  These are used
	* by form_builder to run a validation across all fields at once using CodeIgniter.
	*
	* @return array $rules
	*/
	function validation_rules () {
		$rules = array();
		
		// check required
		if ($this->required == TRUE) {
			$rules[] = 'required';
		}
		
		return $rules;
	}
	
	/**
	* Validate Post
	*
	* This validation is outside of CodeIgniter's form_validation library.  It is run specifically
	* for this field after it passes the major form_validation check.  Not all fieldtypes
	* will require it.  If an error is found, it should be stored in $this->validation_error
	* (using $this->label to refer to the field) and should return FALSE so that the form
	* processor in form_builder knows there was an error.  It will pull the error from
	* $this->validation_error.
	*
	* @return boolean
	*/
	function validate_post () {
		// nothing extra to validate here other than the rulers in $this->validators
		return TRUE;
	}
	
	/**
	* Post to Value
	*
	* Convert the $_POST value to the value that should be inserted into the database.
	*
	* @return string $db_value
	*/
	function post_to_value () {
		return $this->CI->input->post($this->name);
	}
	
	/**
	* Field Form
	*
	* Build the form that will be used to add/edit fields of this type.
	* 
	* @return string $form Built using form_builder.
	*/
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
	
	/**
	* Field Form Process
	*
	* Process the submission of $this->field_form() and return an array of data to be used in custom_fields_model->new_custom_field().
	*
	* Available keys for the returned array: name, type, default (string/array), help, required, validators (array), data (array), 
	*										 options (array), width
	*
	* @return array
	*/
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