<?php

/*
* Fieldtype Class
*
* Parent class for fieldtypes, includes shared methods universal to all fieldtypes.
* Each method returns the object for method chaining.
*
* @class Fieldtype
*/
class Fieldtype {
	// general settings
	// of which areas is this field compatible (products, users, collections, forms, content)
	public $compatibility;
	
	// is the fieldtype enabled? (boolean)
	public $enabled;
	
	// what's the fieldtype name?
	public $fieldtype_name;
	
	// describe the fieldtype in a short sentence
	public $fieldtype_description;
	
	// hold a string of a validation error from secondary validate_post() processing, if available
	public $validation_error;
	
	// MySQL column type
	public $db_column;
	
	// field values
	public $id;
	public $type;
	public $value;
	public $label = FALSE;
	public $name;
	public $help = FALSE;
	public $placeholder = FALSE;
	public $required = FALSE;
	public $width = FALSE;
	public $validators = array();
	public $li_attributes = array();
	public $field_classes = array();
	public $data = array();
	public $options = array();
	public $default = FALSE;
	
	// super values in library
	private $loaded_fieldtypes = array();
	
	// global object
	public $CI;
	
	// constructor
	function __construct () {
		$this->CI =& get_instance();
	}
	
	function db_column() {
		return $this->db_column;
	}
	
	// super methods
	
	/*
	* Load Field
	*
	* @param $field_data Either an ID of a custom_field or an array of custom field data from get_custom_fields().
	*					 This can also just be passed an array to generate a field on the fly (say, in a module).
	*
	* @return boolean|object Field object or FALSE
	*/
	function load ($field_data) {
		if (!is_array($field_data)) {
			// get array
			$this->CI->load->model('custom_fields_model');
			$field_data = $this->CI->custom_fields_model->get_custom_field($field_data);
		}
		
		if (empty($field_data)) {
			log_message('error','No field data was passed to Fieldtype::load().');
			
			return FALSE;
		}
		
		if (!$this->load_type($field_data['type'])) {
			return FALSE;
		}
		
		// prep class name for fieldtype object class
		$class_name = $this->class_name_from_type($field_data['type']);
		
		$object = new $class_name;
		
		$object->id($field_data['id'])
			   ->label((isset($field_data['label'])) ? $field_data['label'] : $field_data['friendly_name'])
			   ->name($field_data['name'])
			   ->type($field_data['type'])
			   ->options($field_data['options'])
			   ->help($field_data['help'])
			   ->width($field_data['width'])
			   ->default_value($field_data['default'])
			   ->required($field_data['required'])
			   ->validators((is_array($field_data['validators'])) ? $field_data['validators'] : array())
			   ->data($field_data['data']);
		
		return $object;
	}
	
	function class_name_from_type ($type) {
		$class_name = ucfirst($type) . '_fieldtype';
		
		return $class_name;
	}
	
	function load_type ($type) {
		$class = $this->class_name_from_type($type);
		
		if (!class_exists($class)) {
			// load fieldtype			
			if (!include(APPPATH . '/modules/custom_fields/libraries/fieldtypes/' . strtolower($type) . '.php')) {
				log_message('error','Unable to load fieldtype: ' . $class);
				
				return FALSE;
			}
			else {
				// loaded successfully
				$this->loaded_types[] = strtolower($type);
				
				// assign to this object
				$object_name = strtolower($type);
				$this->$object_name = new $class;
				
				return TRUE;
			}
		}
		
		return TRUE;
	}
	
	function load_all_types () {
		$this->CI->load->helper('directory');
		
		$files = directory_map(APPPATH . '/modules/custom_fields/libraries/fieldtypes/');
		
		foreach ($files as $file) {
			$file = str_replace('.php','',$file);
			
			$this->load_type($file);
		}
		
		return TRUE;
	}
	
	// field methods
	function id ($id) {
		$this->id = $id;
		
		return $this;
	}
	
	function type ($type) {
		$this->type = $type;
		
		return $this;
	}
	
	function default_value ($default) {
		$this->default = $default;
		
		return $this;
	}
	
	function options ($options) {
		$this->options = $options;
		
		return $this;
	}
	
	function data ($data) {
		$this->data = $data;
		
		return $this;
	}
	
	function value ($value) {
		$this->value = $value;
		
		return $this;
	}
	
	function label ($label) {
		$this->label = $label;
		
		return $this;
	}
	
	function name ($name) {
		$this->name = $name;
		
		return $this;
	}
	
	function width ($width) {
		$this->width = $width;
		
		return $this;
	}
	
	function help ($help) {
		$this->help = $help;
		
		return $this;
	}
	
	function placeholder ($placeholder) {
		$this->placeholder = $placeholder;
		
		return $this;
	}
	
	function required ($required = TRUE) {
		$this->required = $required;
		
		return $this;
	}
	
	function validators ($validators = array()) {
		$this->validators = $validators;
		
		return $this;
	}
	
	function li_attribute ($name, $value = FALSE) {
		if (is_array($name)) {
			// we were passed an array, not two parameters
			foreach ($name as $key => $value) {
				$this->li_attributes[$key] = $value;
			}
		}
		elseif (!empty($value)) {
			$this->li_attributes[$name] = $value;
		}
		
		return $this;
	}
	
	function field_class ($name) {
		if (is_array($name)) {
			// we were passed an array, not just one
			foreach ($name as $value) {
				$this->field_classes[] = $value;
			}
		}
		elseif (!empty($name)) {
			$this->field_classes[] = $name;
		}
		
		return $this;
	}
	
	function compile_attributes ($attributes = array()) {
		$return = '';
		
		foreach ($attributes as $k => $v) {
			if ($v !== FALSE and $v !== NULL) {
				$return .= $k . '="' . $v . '" ';
			}
		}
		
		$return = rtrim($return);
		
		return $return;
	}
}