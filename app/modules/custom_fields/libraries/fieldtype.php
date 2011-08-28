<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Fieldtype Class
*
* Parent class for fieldtypes, includes shared methods universal to all fieldtypes.
* The super methods are useful for loading fields, loading fieldtype classes etc.
* The field-specific methods are useful for manipulating a specific field object.
* Each field-specific method returns the object for method chaining.
*
* @class Fieldtype
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
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
	public $value = FALSE;
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
	
	/**
	* Super Methods
	*
	* These methods are meant to be used as part of the Fieldtype library (e.g., $this->fieldtype->load_all_types()).
	* They will be inherited by each specific fieldtype library (e.g., "Text_fieldtype") but there's no real use
	* for them there.
	*/
	
	/**
	* Create a new field object
	*
	* If you are building a form and want to use the premade Fieldtypes, this is a great way to create
	* an object of a specific fieldtype and then begin assigning a label, name, value, help text, etc.
	* with specific ->label(), ->name(), etc. methods.  It's a way to programmatically build forms.
	* Typically, this will be made even easier by using the form_builder library to compile the field
	* objects into one big form, and then use output_admin() to output the entire form.
	*
	* @param $type
	*
	* @return object Field object
	*/
	public function create ($type) {
		if (!$this->load_type($type)) {
			return FALSE;
		}
		
		// prep class name for fieldtype object class
		$class_name = $this->class_name_from_type($type);
		
		$object = new $class_name;
		
		return $object;
	}
	
	/*
	* Load Field
	*
	* Load a premade field from either an array built by get_custom_fields(), or by a specific custom_field_id.
	* It creates the field object and assigns all known parameters to it automatically.
	* The field object is returned for further manipulation.
	*
	* @param int|array $field_data Either an ID of a custom_field or an array of custom field data from get_custom_fields().
	*					 This can also just be passed an array to generate a field on the fly (say, in a module).
	*
	* @return boolean|object Field object or FALSE
	*/
	public function load ($field_data) {
		if (!is_array($field_data)) {
			// get array
			$this->CI->load->model('custom_fields_model');
			$field_data = $this->CI->custom_fields_model->get_custom_field($field_data);
		}
		
		if (empty($field_data)) {
			log_message('error','No field data was passed to Fieldtype::load().');
			
			return FALSE;
		}
		
		$object = $this->create($field_data['type']);
		
		if ($object === FALSE) {
			return FALSE;
		}
		
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
	
	/**
	* Class Name from Type
	*
	* Generate the Fieldtype library class name (e.g., Text_fieldtype) from the shorthand type name/filename (e.g., "text")
	*
	* @param string $type The shorthand type name
	*
	* @return string The expected, formatted class name for this type
	*/
	private function class_name_from_type ($type) {
		$class_name = ucfirst($type) . '_fieldtype';
		
		return $class_name;
	}
	
	/**
	* Load Type
	*
	* Load a fieldtype as an object of this library.  This allows it to be used for this::create() and this::load()
	* field-building methods.  This method is called automatically by each method for that purpose.
	* It may also be used to simply load the library as an object of this library, so that one can access
	* fieldtype-library specific properties like text_fieldtype::fieldtype_description, ..::enabled, etc.
	*
	* @param string $type The shorthand type name/filename of the fieldtype (e.g, "select")
	*
	* @return boolean TRUE upon successful load
	*/
	public function load_type ($type) {
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
	
	/**
	* Load All Fieldtypes
	*
	* Loads all fieldtypes with this::load_type() based on their existence in the libraries/fieldtypes/ folder.
	*
	* @return boolean TRUE upon success
	*/
	public function load_all_types () {
		$files = $this->get_fieldtype_filenames();
		
		foreach ($files as $file) {
			$this->load_type($file);
		}
		
		return TRUE;
	}
	
	/**
	* Get Fieldtype Options
	*
	* Load all fieldtypes, then return an array of type => fieldtype_name for each available fieldtype.
	* Used for generating the <select> boxes in the add/edit custom field form.
	*
	* @return array $options in form array('type' => 'fieldtype_name')
	*/
	public function get_fieldtype_options () {
		$this->load_all_types();
		
		$options = array();
		foreach ($this->loaded_types as $type) {
			$options[$type] = $this->$type->fieldtype_name;
		}
		
		return $options;
	}
	
	/**
	* Get Fieldtype Filenames
	*
	* Read the fieldtype library directory for all possible fieldtype PHP files.
	*
	* @return array $files
	*/
	private function get_fieldtype_filenames () {
		$this->CI->load->helper('directory');
		
		$files = directory_map(APPPATH . '/modules/custom_fields/libraries/fieldtypes/');
		
		foreach ($files as $key => $file) {
			$files[$key] = str_replace('.php','',$file);
		}
		
		return $files;
	}
	
	/**
	* Field-Specific Methods
	*
	* These methods are methods that are meant to be inherited by each Fieldtype.  They are used to assign
	* values to properties of the field like "label", "name", "validators", etc.  They are really just meant to
	* be shared across each fieldtype to help save some time in making fieldtypes.
	*
	* Each method returns the fieldtype object for method chaining.
	*/
	
	/**
	* DB Column
	*
	* @return string 
	*/
	public function db_column() {
		return $this->db_column;
	}
	
	/**
	* Set ID
	*
	* @param string $id
	*
	* @return object $fieldtype_object
	*/
	public function id ($id) {
		$this->id = $id;
		
		return $this;
	}
	
	/**
	* Set Type
	*
	* @param string $type
	*
	* @return object $fieldtype_object
	*/
	public function type ($type) {
		$this->type = $type;
		
		return $this;
	}
	
	/**
	* Set Default Value
	*
	* @param string|array $default
	*
	* @return object $fieldtype_object
	*/
	public function default_value ($default) {
		$this->default = $default;
		
		return $this;
	}
	
	/**
	* Set Options
	*
	* @param array $options
	*
	* @return object $fieldtype_object
	*/
	public function options ($options) {
		$this->options = $options;
		
		return $this;
	}
	
	/**
	* Set Data
	*
	* @param array $data
	*
	* @return object $fieldtype_object
	*/
	public function data ($data) {
		$this->data = $data;
		
		return $this;
	}
	
	/**
	* Set Current Value
	*
	* @param string|array|boolean $value
	*
	* @return object $fieldtype_object
	*/
	public function value ($value) {
		$this->value = $value;
		
		return $this;
	}
	
	/**
	* Set Label
	*
	* @param string $label
	*
	* @return object $fieldtype_object
	*/
	public function label ($label) {
		$this->label = $label;
		
		return $this;
	}
	
	/**
	* Set Name
	*
	* @param string $name
	*
	* @return object $fieldtype_object
	*/
	public function name ($name) {
		$this->name = $name;
		
		return $this;
	}
	
	/**
	* Set Width
	*
	* @param string $width
	*
	* @return object $fieldtype_object
	*/
	public function width ($width) {
		$this->width = $width;
		
		return $this;
	}
	
	/**
	* Set Help Text
	*
	* @param string $help
	*
	* @return object $fieldtype_object
	*/
	public function help ($help) {
		$this->help = $help;
		
		return $this;
	}
	
	/**
	* Set Placeholder Text
	*
	* @param string $placeholder
	*
	* @return object $fieldtype_object
	*/
	public function placeholder ($placeholder) {
		$this->placeholder = $placeholder;
		
		return $this;
	}
	
	/**
	* Set Is Required
	*
	* @param boolean $required
	*
	* @return object $fieldtype_object
	*/
	public function required ($required = TRUE) {
		$this->required = $required;
		
		return $this;
	}
	
	/**
	* Set Validators
	*
	* @param array $validators
	*
	* @return object $fieldtype_object
	*/
	public function validators ($validators = array()) {
		$this->validators = $validators;
		
		return $this;
	}
	
	/**
	* Set <li> Attribute
	*
	* @param string $name
	* @param string $value
	*
	* @return object $fieldtype_object
	*/
	public function li_attribute ($name, $value = FALSE) {
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
	
	/**
	* Set Field Class
	*
	* @param string $name
	*
	* @return object $fieldtype_object
	*/
	public function field_class ($name) {
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
	
	/**
	* Compile Attributes
	*
	* Compiles an array of attributes (e.g, array('name' => 'test', 'width' => '100px')) into an HTML
	* declaration: e.g., 'name="test" width="100px"'
	*
	* @param array $attributes
	*
	* @return string $attributes_line
	*/
	public function compile_attributes ($attributes = array()) {
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