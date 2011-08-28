<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Form Builder Class
*
* Deal with multiple Fieldtype objects and build an entire form.  Handles form-wide actions like
* validation.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/
class Form_builder {
	// global CI object
	public $CI;
	
	// each Fieldtype object is stored in order as a value of this array
	public $form = array();
	
	// if there are errors in validation, they are stored in this array
	public $validation_errors = array();
	
	function __construct () {
		$this->CI =& get_instance();
	}
	
	/**
	* Reset Form
	*
	* Remove/unset all field objects in this form.  Clear validation errors.
	*
	* @return void 
	*/
	public function reset() {
		foreach ($this->form as $field) {
			unset($field);
		}
	
		$this->form = array();
		$this->validation_errors = array();
	}
	
	/**
	* Build Form from Group
	*
	* Creates an array in this object of all the fieldtype objects, from a custom field group
	*
	* @param int $custom_field_group_id
	*
	* @return boolean 
	*/
	public function build_form_from_group ($custom_field_group_id) {
		$this->reset();
		
		$custom_fields = $this->CI->custom_fields_model->get_custom_fields(array('group' => $custom_field_group_id));
		
		$this->CI->load->library('custom_fields/fieldtype');
	
		if (empty($custom_fields)) {
			return FALSE;
		}
	
		foreach ($custom_fields as $field) {
			$this->form[] =& $this->CI->fieldtype->load($field);
		}
	
		return TRUE;
	}
	
	/**
	* Build Form from Array
	*
	* Builds the internal form from an array from get_custom_fields()
	*
	* @param array get_custom_fields array
	*
	* @return boolean 
	*/
	public function build_form_from_array ($custom_fields) {
		$this->reset();
		
		if (empty($custom_fields)) {
			return FALSE;
		}
		
		$this->CI->load->library('custom_fields/fieldtype');
	
		foreach ($custom_fields as $field) {
			$this->form[] = $this->CI->fieldtype->load($field);
		}
	
		return TRUE;
	}
	
	/**
	* Add Field
	*
	* Manually add a fieldtype object to this form
	*
	* @param string $type 
	*
	* @return object Field object
	*/
	public function add_field ($type) {
		$this->CI->load->library('custom_fields/fieldtype');
		
		$field_object = $this->CI->fieldtype->create($type);
		
		$this->form[] =& $field_object;
		
		return $field_object;
	}
	
	/**
	* Validate Post
	*
	* When there is a form active in this library, this method can be used to validate the $_POST submission.
	* First, it will gather rules dynamically from each Fieldtype object and run them with CodeIgniter's
	* form_validation library.  Then, it will check for specific atypical validation routines with each field.
	*
	* If errors are found, they are stored as an array at $this->validation_errors so they are easy to grab.
	* They can be grabbed with $this->validation_errors().
	*
	* @return boolean TRUE if no errors, FALSE if errors
	*/
	public function validate_post () {
		// initial rules-based validation
		$this->CI->load->library('form_validation');
		reset($this->form);
		
		$has_rules = FALSE;
		foreach ($this->form as $field) {
			$rules = $field->validation_rules();
			
			if (!empty($rules)) {
				$has_rules = TRUE;
				$this->CI->form_validation->set_rules($field->name, $field->label, implode('|',$rules));
			}
		}
		
		if ($has_rules === TRUE and $this->CI->form_validation->run() === FALSE) {
			$this->validation_errors = array_merge($this->validation_errors(TRUE),explode('||',str_replace(array('<p>','</p>'),array('','||'),validation_errors())));
		}
		
		// secondary additional validation
		reset($this->form);
		foreach ($this->form as $field) {
			if ($field->validate_post() === FALSE) {
				$this->validation_errors[] = $field->validation_error;
			}
		}
		
		if (!empty($this->validation_errors)) {
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	* Get Validation Errors
	*
	* Return validation errors, either as a paragraph (like CI's validation_errors())
	* or as an array.
	*
	* @param boolean $array Set to TRUE to retrieve only the array.  (default: FALSE).
	*
	* @return string|array 
	*/
	public function validation_errors ($array = FALSE) {
		$return = '';
		$errors = array();
		
		// format like the CodeIgniter function if they don't want an array
		foreach ($this->validation_errors as $error) {
			if (empty($error) or strlen($error) < 2) {
				continue;
			}
			
			// always have period at end
			$error = rtrim($error, '.') . '.';
			
			$errors[] = $error;
			
			$return .= '<p> ' . $error . '</p>';
		}
		
		if ($array == TRUE) {
			return $errors;
		}
	
		return $return;
	}
	
	/**
	* Post to Array
	* 
	* Convert the current $_POST submission into a nice array of data
	* ready for insert into a database (via a model method, most likely).
	* Each field will have a key in the array and a corresponding value
	* built by the Fieldtype's post_to_value() method.
	*
	* @return array 
	*/
	public function post_to_array () {
		reset($this->form);
		
		$array = array();
		
		foreach ($this->form as $field) {
			$array[$field->name] = $field->post_to_value();	
		}
		
		return $array;
	}
	
	/**
	* Set Values
	*
	* If you have an array of data corresponding to an entire form (e.g., a "title", "description", and "date"),
	* you can assign each bit of data to it's corresponding field's ->value parameter by loading the whole
	* array here.
	*
	* @param array $values
	*
	* @return boolean TRUE
	*/
	public function set_values ($values = array()) {
		reset($this->form);
		
		foreach ($this->form as $field) {
			$field->value($values[$field->name]);
		}
		
		return TRUE;
	}
	
	/**
	* Clear Defaults
	*
	* Sometimes, like when we are editing an existing entry, we don't want to use default values.
	*
	* @return void 
	*/
	public function clear_defaults () {
		reset($this->form);
		
		foreach ($this->form as $field) {
			$field->default_value($field->name, FALSE);
		}
		
		return;
	}
	
	/**
	* Output Admin
	*
	* Return a compiled string of each Fieldtype object's output_admin() method.  i.e., a form
	* generated automatically.
	*
	* @return string HTML for the form (a series of <li> elements)
	*/
	public function output_admin () {
		reset($this->form);
		
		$return = '';
		
		foreach ($this->form as $field) {
			$return .= $field->output_admin();
		}
		
		return $return;
	}
}