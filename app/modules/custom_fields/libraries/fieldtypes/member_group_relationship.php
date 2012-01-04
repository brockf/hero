<?php

/**
* Member Group Relationship Fieldtype
*
* @extends Fieldtype
* @class Member_group_relationship_fieldtype
*/

class Member_group_relationship_fieldtype extends Fieldtype {
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
		$this->fieldtype_name = 'Member Group Relationship';
		$this->fieldtype_description = 'Select one or more member group from a list.';
		$this->validation_error = '';
		$this->db_column = 'TEXT';
	}
	
	/**
	* Output Shared
	*
	* Perform actions shared between admin- and frontend-outputs.
	*
	* @return void
	*/
	function output_shared () {
		$this->field_class('select');
		$this->field_class('relationship');
		
		// add validator names to class list
		foreach ($this->validators as $validator) {
			$this->field_class($validator);
		}
		
		if ($this->data['allow_multiple'] == TRUE) {
			$field = $this->create('multiselect');
		}
		else {
			$field = $this->create('select');
		}
		
		// build options
		$this->CI->load->model('users/usergroup_model');
		$groups = $this->CI->usergroup_model->get_usergroups();
		
		$options = array();
		
		if ($this->required != TRUE) {
			// add an empty option
			$options[] = array('name' => '', 'value' => '');
		}
		
		if (!empty($groups)) {
			foreach ($groups as $group) {
				$options[] = array('name' => $group['name'], 'value' => $group['id']);
			}
		}
		
		$field->label($this->label);
		$field->name($this->name);
		$field->value($this->value);
		$field->default_value($this->default);
		$field->options($options);
		$field->required($this->required);
		$field->validators($this->validators);
		$field->placeholder($this->placeholder);
		$field->id($this->id);
		
		return $field;
	}
	
	/**
	* Output Admin
	*
	* Returns the field with it's <label> in an <li> suitable for the admin forms.
	* We will actually wrap the (multi)select fieldtype.
	*
	* @return string $return The HTML to be included in a form
	*/
	function output_admin () {
		$field = $this->output_shared();
		
		return $field->output_admin();
	}
	
	/**
	* Output Frontend
	*
	* Returns the isolated field.  Likely called from the {custom_field} template function.
	*
	* @return string $return The HTML to be included in a form.
	*/
	function output_frontend () {
		$field = $this->output_shared();
		
		return $field->output_frontend();
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
		if ($this->data['allow_multiple'] == TRUE) {
			$array = $this->CI->input->post($this->name);
		
			if (!is_array($array)) {
				$array = array($array);
			}
			
			return serialize($array);
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
		
		// gather content types
	    $this->CI->load->model('publish/content_type_model');
	    $content_types = $this->CI->content_type_model->get_content_types();
	    
	    $options = array();
	    foreach ($content_types as $type) {
	    	$options[] = array('name' => $type['name'], 'value' => $type['id']);
	    }
	   			   
	    $allow_multiple = $this->CI->form_builder->add_field('checkbox');
	    $allow_multiple->label('Allow Multiple Relationships')
			    	  ->name('allow_multiple')
			    	  ->help('If checked, the user can select one or many member groups from the list.');
			    	  
		
		// build options
		$this->CI->load->model('users/usergroup_model');
		$groups = $this->CI->usergroup_model->get_usergroups();
		
		$options = array();
		
		if (!empty($groups)) {
			foreach ($groups as $group) {
				$options[] = array('name' => $group['name'], 'value' => $group['id']);
			}
		}
			    	  
		$default = $this->CI->form_builder->add_field('select');
		$default->label('Default Value')
	          ->name('default')
	          ->options($options);
	          
	    $help = $this->CI->form_builder->add_field('textarea');
	    $help->label('Help Text')
	    	 ->name('help')
	    	 ->width('500px')
	    	 ->height('80px')
	    	 ->help('This help text will be displayed beneath the field.  Use it to guide the user in responding correctly.');
	   			   
	   	$required = $this->CI->form_builder->add_field('checkbox');
	    $required->label('Required Field')
	    	  ->name('required')
	    	  ->help('If checked, a selection must be made form to be processed.');

	    	  
	    if (!empty($edit_id)) {
	    	$this->CI->load->model('custom_fields_model');
	    	$field = $this->CI->custom_fields_model->get_custom_field($edit_id);
	    	
	    	$default->value($field['default']);
	    	$help->value($field['help']);
	    	$required->value($field['required']);
	    	$field_name->value($field['data']['field_name']);
	    	$content_type->value($field['data']['content_type']);
	    	$allow_multiple->value($field['data']['allow_multiple']);
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
					'data' => array(
								'allow_multiple' => ($this->CI->input->post('allow_multiple')) ? TRUE : FALSE
							),
					'required' => ($this->CI->input->post('required')) ? TRUE : FALSE
				);
	}
}