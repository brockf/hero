<?php

/*
* Text Fieldtype
*
* @extends Fieldtype
* @class Text_fieldtype
*/

class Text_fieldtype extends Fieldtype {
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
		$this->fieldtype_name = 'Text';
		$this->fieldtype_description = 'A single line of text.';
		$this->validation_error = '';
		$this->db_column = 'VARCHAR(250)';
	}
	
	/**
	* Output Shared
	*
	* Perform actions shared between admin- and frontend-outputs.  Compile attributes of this
	* fieldtype object into an HTML attribute line.
	*
	* @return string $attributes
	*/
	function output_shared () {
		// set defaults
		if ($this->width == FALSE) {
			$this->width = '275px';
		}
		
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		$this->field_class('text');
		
		// add validator names to class list
		foreach ($this->validators as $validator) {
			$this->field_class($validator);
		}
		
		// prep final attributes	
		$placeholder = ($this->placeholder !== FALSE) ? ' placeholder="' . $this->placeholder . '" ' : '';
		
		$attributes = array(
						'type' => 'text',
						'name' => $this->name,
						'id' => $this->name,
						'value' => htmlspecialchars($this->value),
						'placeholder' => $this->placeholder,
						'style' => 'width: ' . $this->width,
						'class' => implode(' ', $this->field_classes)
						);
		
		// compile attributes
		$attributes = $this->compile_attributes($attributes);
		
		return $attributes;
	}
	
	/**
	* Output Admin
	*
	* Returns the field with it's <label> in an <li> suitable for the admin forms.
	*
	* @return string $return The HTML to be included in a form
	*/
	function output_admin () {
		if (empty($this->value) and $this->CI->input->post($this->name) === FALSE) {
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
	
	/**
	* Output Frontend
	*
	* Returns the isolated field.  Likely called from the {custom_field} template function.
	*
	* @return string $return The HTML to be included in a form.
	*/
	function output_frontend () {
		if ($this->value === FALSE) {
			if ($this->CI->input->post($this->name) == FALSE) {
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
		
		$this->CI->load->helper('valid_domain');
		
		// run $this->validators
		if (!empty($this->validators)) {
			foreach ($this->validators as $validator) {
				if ($validator == 'whitespace') {
					$rules[] = 'trim';
				}
				elseif ($validator == 'alphanumeric') {
					$rules[] = 'alpha_numeric';
				}
				elseif ($validator == 'numeric') {
					$rules[] = 'numeric';
				}
				elseif ($validator == 'domain') {
					$rules[] = 'valid_domain';
				}
				elseif ($validator == 'email') {
					$rules[] = 'valid_email';
				}
				elseif ($validator == 'strip_tags') {
					$rules[] = 'strip_tags';
				}
			}
		}
		
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
		
		$default = $this->CI->form_builder->add_field('text');
		$default->label('Default Value')
	          ->name('default');
	          
	    $help = $this->CI->form_builder->add_field('textarea');
	    $help->label('Help Text')
	    	 ->name('help')
	    	 ->width('500px')
	    	 ->height('80px')
	    	 ->help('This help text will be displayed beneath the field.  Use it to guide the user in responding correctly.');
	    	 
	   	$width = $this->CI->form_builder->add_field('text');
	   	$width->label('Width')
	   	 	  ->name('width')
	   	 	  ->width('75px')
	   	 	  ->default_value('250px')
	   		  ->help('Enter the width of this field.');
	    	 
	    $required = $this->CI->form_builder->add_field('checkbox');
	    $required->label('Required Field')
	    	  ->name('required')
	    	  ->help('If checked, this box must not be empty for the form to be processed.');
	    	  
	   	$validators = $this->CI->form_builder->add_field('multicheckbox');
	   	$validators->label('Validators')
	   			   ->name('validators')
	   			   ->options(
	   			   		array(
	   			   			array('name' => 'Trim whitespace from around response', 'value' => 'trim'),
	   			   			array('name' => 'Strip HTML tags', 'value' => 'strip_tags'),
	   			   			array('name' => 'Only alphanumeric characters', 'value' => 'alpha_numeric'),
	   			   			array('name' => 'Only numbers', 'value' => 'numeric'),
	   			   			array('name' => 'Must be a valid domain (e.g., "yahoo.com")', 'value' => 'valid_domain'),
	   			   			array('name' => 'Must be a valid email address (e.g., "test@example.com")', 'value' => 'valid_email')
	   			   		)
	   			   );
	    	  
	    if (!empty($edit_id)) {
	    	$this->CI->load->model('custom_fields_model');
	    	$field = $this->CI->custom_fields_model->get_custom_field($edit_id);
	    	
	    	$default->value($field['default']);
	    	$help->value($field['help']);
	    	$validators->value($field['validators']);
	    	$required->value($field['required']);
	    	$width->value($field['width']);
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
					'help' => $this->CI->input->post('help'),
					'validators' => $this->CI->input->post('validators'),
					'required' => ($this->CI->input->post('required')) ? TRUE : FALSE,
					'width' => $this->CI->input->post('width')
				);
	}
}