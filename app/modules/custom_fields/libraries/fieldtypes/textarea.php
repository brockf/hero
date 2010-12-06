<?php

/*
* Textarea Fieldtype
*
* @extends Fieldtype
* @class Textarea_fieldtype
*/

class Textarea_fieldtype extends Fieldtype {
	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'Textarea';
		$this->fieldtype_description = 'Multiple lines of text.';
		$this->validation_error = '';
		$this->db_column = 'TEXT';
	}
	
	function output_shared () {
		// set defaults
		if ($this->width == FALSE) {
			$this->width = '275px';
		}
		
		if (!isset($this->data['height']) or $this->data['height'] == FALSE) {
			$this->data['height'] = '80px';
		}
		
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		$this->field_class('textarea');
		
		// add validator names to class list
		foreach ($this->validators as $validator) {
			$this->field_class($validator);
		}
		
		// prep final attributes	
		$placeholder = ($this->placeholder !== FALSE) ? ' placeholder="' . $this->placeholder . '" ' : '';
		
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
		if ($this->CI->input->post($this->name) == FALSE) {
			$this->value($this->default);
		}
	
		$attributes = $this->output_shared();
		
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
		if (empty($this->value)) {
			if ($this->CI->input->post($this->name) == FALSE) {
				$this->value($this->default);
			}
			elseif ($this->CI->input->post($this->name) != FALSE) {
				$this->value($this->CI->input->post($this->name));
			}
		}
		
		$attributes = $this->output_shared();
		
		// build HTML
		$return = '<textarea ' . $attributes . '>' . htmlspecialchars($this->value) . '</textarea>';
					
		return $return;
	}
	
	function validation_rules () {
		$rules = array();
		
		// run $this->validators
		if (!empty($this->validators)) {
			foreach ($this->validators as $validator) {
				if ($validator == 'whitespace') {
					$rules[] = 'trim';
				}
				elseif ($validator == 'alphanumeric') {
					$rules[] = 'alpha_numeric';
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