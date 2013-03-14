<?php

/*
* WYSIWYG Fieldtype
*
* @extends Fieldtype
* @class Wysiwyg_Fieldtype
*/

class Wysiwyg_Fieldtype extends Fieldtype {
	/**
	* Constructor
	*
	* Assign basic properties to this fieldtype, useful in listing available fieldtypes.
	* Also defines the MySQL column format for fields of this type.
	*/
	function __construct () {
		parent::__construct();

		$this->compatibility = array('publish','products','collections');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'WYSIWYG Textarea';
		$this->fieldtype_description = 'A textarea that includes an HTML editor.';
		$this->validation_error = '';
		$this->db_column = 'TEXT';
	}

	/**
	* Set Height
	*
	* Textareas can carry a height attribute.  This can be set dynamically here.  It is stored
	* in the data array and so can also be set via the $this->data(array('height' => '100px')); means.
	*
	* @param string $height
	*
	* @return object $fieldtype_object
	*/
	function height ($height) {
		$this->data['height'] = $height;

		return $this;
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
			$this->width = '750px';
		}

		if (!isset($this->data['height']) or $this->data['height'] == FALSE) {
			$this->data['height'] = '140px';
		}

		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}

		$this->field_class('wysiwyg');

		// add validator names to class list
		foreach ($this->validators as $validator) {
			$this->field_class($validator);
		}

		// prep final attributes
		$attributes = array(
						'type' => 'textarea',
						'name' => $this->name,
						'style' => 'width: ' . $this->width . '; height: ' . $this->data['height'],
						'id' => $this->name,
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
		if (empty($this->value) and $this->CI->input->post($this->name) == FALSE) {
			$this->value($this->default);
		}

		if (!in_array('complete',$this->field_classes) or (isset($this->data['use_basic']) and $this->data['use_basic'] == TRUE)) {
			$this->field_class('basic');
		}
		else {
			$this->field_class('complete');
		}

		$attributes = $this->output_shared();

		// get CKEditor to show
		if (!defined('INCLUDE_CKEDITOR')) {
			define('INCLUDE_CKEDITOR','TRUE');
		}

		$help = ($this->help == FALSE) ? '' : '<div class="help" style="margin-left: 0">' . $this->help . '</div>';

		// build HTML
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>
						<div style="float: left; width: "' . $this->width . '">
						<textarea ' . $attributes . '>' . htmlspecialchars($this->value) . '</textarea>
						' . $help . '
						</div>
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
		return FALSE;
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

		// run $this->validators
		if (!empty($this->validators)) {
			foreach ($this->validators as $validator) {
				if ($validator == 'whitespace') {
					$rules[] = 'trim';
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

		$default = $this->CI->form_builder->add_field('textarea');
		$default->label('Default Value')
	          ->name('default')
	          ->width('500px');

	    $help = $this->CI->form_builder->add_field('textarea');
	    $help->label('Help Text')
	    	 ->name('help')
	    	 ->width('500px')
	    	 ->height('80px')
	    	 ->help('This help text will be displayed beneath the field.  Use it to guide the user in responding correctly.');

	    $required = $this->CI->form_builder->add_field('checkbox');
	    $required->label('Required Field')
	    	  ->name('required')
	    	  ->help('If checked, this box must not be empty for the form to be processed.');

	   	$basic = $this->CI->form_builder->add_field('checkbox');
	   	$basic->label('Use Basic Editor')
	   		  ->name('use_basic')
	   		  ->help('The "Basic" editor doesn\'t have all of the features of the WYSIWYG editor, but is more appropriate when you just want
	   		          basic HTML stylings, images, links, etc.');

	   	$validators = $this->CI->form_builder->add_field('multicheckbox');
	   	$validators->label('Validators')
	   			   ->name('validators')
	   			   ->options(
	   			   		array(
	   			   			array('name' => 'Trim whitespace from around response', 'value' => 'trim')
	   			   		)
	   			   );

	    if (!empty($edit_id)) {
	    	$this->CI->load->model('custom_fields_model');
	    	$field = $this->CI->custom_fields_model->get_custom_field($edit_id);

	    	$default->value($field['default']);
	    	$help->value($field['help']);
	    	$validators->value($field['validators']);
	    	$required->value($field['required']);

	    	if (isset($field['data']['use_basic']) and $field['data']['use_basic'] == TRUE) {
	    		$basic->value(TRUE);
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
					'default' => $this->CI->input->post('default'),
					'help' => $this->CI->input->post('help'),
					'validators' => $this->CI->input->post('validators'),
					'required' => ($this->CI->input->post('required')) ? TRUE : FALSE,
					'data' => array('use_basic' => ($this->CI->input->post('use_basic') ? TRUE : FALSE))
				);
	}
}