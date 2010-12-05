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
	private $CI;
	
	// constructor
	function __construct () {
		$this->CI = &get_instance();
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
		
		$object->id = $field_data['id'];
		$object->label = (isset($field_data['label'])) ? $field_data['label'] : $field_data['friendly_name'];
		$object->type = $field_data['type'];
		$object->options = $field_data['options'];
		$object->help = $field_data['help'];
		$object->order = $field_data['order'];
		$object->width = $field_data['width'];
		$object->default = $field_data['default'];
		$object->required = $field_data['required'];
		$object->validators = $field_data['validators'];
		$object->data = $field_data['data'];
		
		return $object;
	}
	
	function class_name_from_type ($type) {
		$class_name = ucfirst($type) . '_fieldtype';
		
		return $class_name;
	}
	
	function load_type ($type) {
		$class = $this->class_name_from_type($type);
	
		if (!in_array(strtolower($type), $this->loaded_fieldtypes)) {
			// load fieldtype			
			if (!$this->CI->load->library('custom_fields/fieldtypes/' . $class)) {
				log_message('error','Unable to load fieldtype: ' . $class);
				
				return FALSE;
			}
			else {
				// loaded successfully
				$this->loaded_types[] = strtolower($type);
				
				return TRUE;
			}
		}
	}
	
	function load_all_types () {
		$this->CI->load->helper('directory');
		
		$files = directory_map('./fieldtypes/');
		
		foreach ($files as $file) {
			$file = str_replace('.php','',$file);
			
			$this->load_type($file);
		}
		
		return TRUE;
	}
	
	// field methods
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
	
	function placeholder ($placeholder) {
		$this->placeholder = $placeholder;
		
		return $this;
	}
	
	function required ($required = TRUE) {
		$this->required = $required;
		
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