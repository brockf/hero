<?php

/*
* Date Fieldtype
*
* @extends Fieldtype
* @class Date_fieldtype
*/

class Date_fieldtype extends Fieldtype {
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
		$this->fieldtype_name = 'Date';
		$this->fieldtype_description = 'Date selector (no time).';
		$this->validation_error = '';
		$this->db_column = 'DATE';
	}
	
	/**
	* Output Shared
	*
	* Perform actions shared between admin- and frontend-outputs.  Assigns classes to the field object,
	* and assigns a width if not set.
	*
	* @return void
	*/
	function output_shared () {
		// set defaults
		if ($this->width == FALSE) {
			$this->width = '80px';
		}
		
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		$this->field_class('text');
		$this->field_class('date');
		$this->field_class('datepick');
		
		return;
	}
	
	/**
	* Output Admin
	*
	* Returns the field with it's <label> in an <li> suitable for the admin forms.
	* Uses the datepicker.
	*
	* @return string $return The HTML to be included in a form
	*/
	function output_admin () {
		if (empty($this->value) and $this->CI->input->post($this->name) == FALSE) {
			$this->value($this->default);
		}
		
		// get datepicker to load
		if (!defined('INCLUDE_DATEPICKER')) {
			define('INCLUDE_DATEPICKER','TRUE');
		}
		
		$this->output_shared();
		
		// prep final attributes	
		$placeholder = ($this->placeholder !== FALSE) ? ' placeholder="' . $this->placeholder . '" ' : '';
		
		$attributes = array(
						'type' => 'text',
						'name' => $this->name,
						'value' => (!empty($this->value) and $this->value != "0000-00-00") ? date('Y-m-d',strtotime($this->value)) : '',
						'placeholder' => $this->placeholder,
						'style' => 'width: ' . $this->width,
						'class' => implode(' ', $this->field_classes)
						);
		
		// compile attributes
		$attributes = $this->compile_attributes($attributes);
		
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
		if (empty($this->value)) {
			if (empty($_POST) and !empty($this->default)) {
				// no POST, let's use the default
				$this->value($this->default);
			}
			elseif ($this->CI->input->post($this->name . '_day') != FALSE) {
				// build value from 3 fields
				$value = $this->CI->input->post($this->name . '_year') . '-' . $this->CI->input->post($this->name . '_month') . '-' . $this->CI->input->post($this->name . '_day');
				$this->value($value);
			}
		}
		
		$this->output_shared();
		
		// build HTML
		$this->CI->load->helper('form');
		
		$day_field = array(
						'name' => $this->name . '_day',
						'placeholder' => $this->placeholder,
						'style' => 'width: ' . $this->width,
						'class' => implode(' ', $this->field_classes)
						);
						
		// compile attributes
		$options = array();
		for ($i = 1; $i <= 31; $i++) {
			$options[str_pad($i,2,'0',STR_PAD_LEFT)] = $i;
		}
		$day_field = form_dropdown($this->name . '_day', $options, !empty($this->value) ? date('d', strtotime($this->value)) : '01');
		
		$options = array();
		for ($i = 1; $i <= 12; $i++) {
			$options[str_pad($i,2,'0',STR_PAD_LEFT)] = date('M', strtotime('2010-' . $i . '-01'));
		}
		$month_field = form_dropdown($this->name . '_month', $options, !empty($this->value) ? date('m', strtotime($this->value)) : '01');
		
		$options = array();
		
		if (isset($this->data['future_only']) and $this->data['future_only'] == TRUE) {
			$start = date('Y');
		}
		else {
			$start = date('Y') - 100;
		}
		$end = date('Y') + 100;
		for ($i = $start; $i <= $end; $i++) {
			$options[$i] = $i;
		}
		$year_field = form_dropdown($this->name . '_year', $options, !empty($this->value) ? date('Y', strtotime($this->value)) : date('Y'));
		
		$return = $day_field . ' ' . $month_field . ' ' . $year_field;
					
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
		if (isset($this->data['future_only']) and $this->data['future_only'] == TRUE) {
			if ((strtotime($this->post_to_value()) + (24*60*60)) < time()) {
				$this->validation_error = $this->label . ' must be in the future.';
				return FALSE;
			}
		}
		
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
		if ($this->CI->input->post($this->name . '_day') !== FALSE) {
			return date('Y-m-d', strtotime($this->CI->input->post($this->name . '_year') . '-' . $this->CI->input->post($this->name . '_month') . '-' . $this->CI->input->post($this->name . '_day')));
		}
		else {
			return $this->CI->input->post($this->name);
		}
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
		
	    $help = $this->CI->form_builder->add_field('textarea');
	    $help->label('Help Text')
	    	 ->name('help')
	    	 ->width('500px')
	    	 ->height('80px')
	    	 ->help('This help text will be displayed beneath the field.  Use it to guide the user in responding correctly.');
	    	 
	   	$future = $this->CI->form_builder->add_field('checkbox');
	   	$future->label('Future Only')
	   	       ->name('future_only')
	   	       ->help('Only allow future dates?');
	    
	    if (!empty($edit_id)) {
	    	$this->CI->load->model('custom_fields_model');
	    	$field = $this->CI->custom_fields_model->get_custom_field($edit_id);
	    	
	    	$help->value($field['help']);
	    	if (isset($field['data']['future_only'])) {
	    		$future->value($field['data']['future_only']);
	    	}
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
					'help' => $this->CI->input->post('help'),
					'data' => array('future_only' => ($this->CI->input->post('future_only')) ? TRUE : FALSE)
				);
	}
}