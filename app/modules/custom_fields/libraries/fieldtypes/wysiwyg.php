<?php

/*
* WYSIWYG Fieldtype
*
* @extends Fieldtype
* @class WYSIWYG_Fieldtpye
*/

class WYSIWYG_Fieldtpye extends Fieldtype {
	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','products','collections');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'WYSIWYG Textarea';
		$this->fieldtype_description = 'A textarea that includes an HTML editor.';
		$this->validation_error = '';
		$this->db_column = 'TEXT';
	}
	
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
		
		// get CKEditor to show
		if (!defined('INCLUDE_CKEDITOR')) {
			define('INCLUDE_CKEDITOR','TRUE');
		}
		
		$help = ($this->help == FALSE) ? '' : '<div class="help">' . $this->help . '</div>';
		
		// build HTML
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>
						<textarea ' . $attributes . '>' . htmlspecialchars($this->value) . '</textarea>
						' . $help . '
					</li>';
					
		return $return;
	}
	
	function output_frontend () {
		return FALSE;
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