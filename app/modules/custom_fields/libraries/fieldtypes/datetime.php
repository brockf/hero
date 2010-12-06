<?php

/*
* Datetime Fieldtype
*
* @extends Fieldtype
* @class Datetime_fieldtype
*/

class Datetime_fieldtype extends Fieldtype {
	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'Date &amp; Time';
		$this->fieldtype_description = 'Date with time.';
		$this->validation_error = '';
		$this->db_column = 'DATETIME';
	}
	
	function output_shared () {
		// set defaults
		if ($this->width == FALSE) {
			$this->width = '150px';
		}
		
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		$this->field_class('text');
		$this->field_class('datetime');
		
		return;
	}
	
	function output_admin () {
		if ($this->CI->input->post($this->name) == FALSE) {
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
						'value' => date('Y-m-d',strtotime($this->value)),
						'placeholder' => $this->placeholder,
						'style' => 'width: ' . $this->width,
						'class' => implode(' ', $this->field_classes)
						);
		
		// compile attributes
		$attributes = $this->compile_attributes($attributes);
		
		$help = ($this->help == FALSE) ? '' : '<div class="help">' . $this->help . '</div>';
		
		// build HTML
		$this->CI->load->helper('form');
		
		$options = array();
		for ($i = 1; $i <= 12; $i++) {
			$options[str_pad($i,2,'0',STR_PAD_LEFT)] = $i;
		}
		$hour_field = form_dropdown($this->name . '_hour', $options, !empty($this->value) ? date('h', strtotime($this->value)) : date('h'));
		
		$options = array();
		for ($i = 1; $i <= 59; $i++) {
			$options[str_pad($i,2,'0',STR_PAD_LEFT)] = str_pad($i,2,'0',STR_PAD_LEFT);
		}
		$minute_field = form_dropdown($this->name . '_minute', $options, !empty($this->value) ? date('i', strtotime($this->value)) : date('i'));
		
		$options = array('am' => 'am', 'pm' => 'pm');
		$ampm_field = form_dropdown($this->name . '_ampm', $options, !empty($this->value) ? date('a', strtotime($this->value)) : date('a'));
		
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>
						<input ' . $attributes . ' /> ' . $hour_field . ' ' . $minute_field . ' ' . $ampm_field . '
						' . $help . '
					</li>';
					
		return $return;
	}
	
	function output_frontend () {
		if (empty($this->value)) {
			if (empty($_POST) and !empty($this->default)) {
				// no POST, let's use the default
				$this->value($this->default);
			}
			elseif ($this->CI->input->post($this->name . '_day') != FALSE) {
				// build value from 3 fields
				$value = $this->CI->input->post($this->name . '_year') . '-' . $this->CI->input->post($this->name . '_month') . '-' . $this->CI->input->post($this->name . '_day') . ' ' . $this->CI->input->post($this->name . '_hour') . ':' . $this->CI->input->post($this->name . '_minute') . ' ' . $this->CI->input->post($this->name . '_ampm');
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
		$start = date('Y') - 100;
		$end = date('Y') + 100;
		for ($i = $start; $i <= $end; $i++) {
			$options[$i] = $i;
		}
		$year_field = form_dropdown($this->name . '_year', $options, !empty($this->value) ? date('Y', strtotime($this->value)) : date('Y'));
		
		$options = array();
		for ($i = 1; $i <= 12; $i++) {
			$options[str_pad($i,2,'0',STR_PAD_LEFT)] = $i;
		}
		$hour_field = form_dropdown($this->name . '_hour', $options, !empty($this->value) ? date('h', strtotime($this->value)) : date('h'));
		
		$options = array();
		for ($i = 1; $i <= 59; $i++) {
			$options[str_pad($i,2,'0',STR_PAD_LEFT)] = str_pad($i,2,'0',STR_PAD_LEFT);
		}
		$minute_field = form_dropdown($this->name . '_minute', $options, !empty($this->value) ? date('i', strtotime($this->value)) : date('i'));
		
		$options = array('am' => 'am', 'pm' => 'pm');
		$ampm_field = form_dropdown($this->name . '_ampm', $options, !empty($this->value) ? date('a', strtotime($this->value)) : date('a'));
		
		$return = $day_field . ' ' . $month_field . ' ' . $year_field . '&nbsp;&nbsp;' . $hour_field . ' ' . $minute_field . ' ' . $ampm_field;
					
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
		if ($this->CI->input->post($this->name . '_day') !== FALSE) {
			// separate date and time fields
			return date('Y-m-d H:i:00', strtotime($this->CI->input->post($this->name . '_year') . '-' . $this->CI->input->post($this->name . '_month') . '-' . $this->CI->input->post($this->name . '_day') . ' ' . $this->CI->input->post($this->name . '_hour') . ':' . $this->CI->input->post($this->name . '_minute') . ' ' . $this->CI->input->post($this->name . '_ampm')));
		}
		else {
    		// one date field + time fields
			return date('Y-m-d H:i:00', strtotime($this->CI->input->post($this->name) . ' ' . $this->CI->input->post($this->name . '_hour') . ':' . $this->CI->input->post($this->name . '_minute') . ' ' . $this->CI->input->post($this->name . '_ampm')));
		}
	}
	
	function field_form () {
		// build fieldset with admin_form which is used when editing a field of this type
	}
	
	function field_form_process () {
		// build array for database
	}
}