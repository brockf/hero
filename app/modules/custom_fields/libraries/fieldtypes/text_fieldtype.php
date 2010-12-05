<?php

/*
* Text Fieldtype
*
* @extends Fieldtype
* @class Text_fieldtype
*/

class Text_fieldtype extends Fieldtype {
	function __construct () {
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'Text';
		$this->fieldtype_description = 'Description';
	}
	
	function output_shared () {
		// set defaults
		if ($this->width == FALSE) {
			$this->width = '275px';
		}
		
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		if (in_array('numeric', $field['validators'])) {
			$this->field_class('number');
		}
		
		if (in_array('alphanumeric', $field['validators'])) {
			$this->field_class('alphanumeric');
		}
		
		// prep final attributes	
		$placeholder = ($this->placeholder !== FALSE) ? ' placeholder="' . $this->placeholder . '" ' : '';
		
		$attributes = array(
						'type' => 'text',
						'name' => $this->name,
						'value' => $this->value,
						'placeholder' => $this->placeholder,
						'style' => 'width: ' . $this->width,
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
						<input ' . $attributes . ' />
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
		$return = '<input ' . $attributes . ' />';
					
		return $return;
	}
	
	function validate_post () {
		// nothing extra to validate here other than the rulers in $this->validators
		return TRUE;
	}
	
	function field_form () {
		// build fieldset with admin_form which is used when editing a field of this type
	}
	
	function field_form_process () {
		// build array for database
	}
}