<?php

/**
* Custom Fields Model
*
* Supports many areas of the app by providing a universal format for custom fields,
* their validation, and management.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

// define the module for updates
include_once(APPPATH . 'modules/custom_fields/custom_fields.php');

class Custom_fields_model extends CI_Model {
	/**
	* @var array Holds previous get_custom_fields calls in memory
	*/
	public $cache;
	
	/**
	* @var string The full path to upload custom file uploads to
	*/
	var $upload_directory;
	
	function __construct() {
		parent::__construct();
		
		// specify the upload directory, will be created if it doesn't exist
		$this->upload_directory = setting('path_custom_field_uploads');
	}
	
	/**
	* Reset Order for a Field Group
	*
	* @param int $custom_field_group
	*
	*/
	public function reset_order ($custom_field_group) {
		$this->db->update('custom_fields',array('custom_field_order' => '0'), array('custom_field_group' => $custom_field_group));
	}
	
	/**
	* Update Order
	*
	* @param int $field_id
	* @param int $new_order
	*/
	public function update_order ($field_id, $new_order) {
		$this->db->update('custom_fields',array('custom_field_order' => $new_order), array('custom_field_id' => $field_id));
	}
	
	/**
	* Get Custom Field
	*
	* @param int $custom_field_id
	*
	* @return array $custom_field or FALSE
	*/
	public function get_custom_field($custom_field_id) {
		$return = $this->get_custom_fields(array('id' => $custom_field_id));
		
		if (empty($return)) {
			return FALSE;
		}
		else {
			return $return[0];
		}
	}
	
	/**
	* Get Custom Fields
	*
	* Retrieves custom fields ordered by custom_field_order, with caching
	* 
	* @param int $filters['group'] The custom field group
	* @param int $filters['id'] A custom field ID
	*
	* @return array $fields The custom fields
	*/
	public function get_custom_fields ($filters = array()) {
		if (isset($this->cache[base64_encode(serialize($filters))])) {
			return $this->cache[base64_encode(serialize($filters))];
		}
	
		if (isset($filters['group'])) {
			$this->db->where('custom_field_group',$filters['group']);
		}
		
		if (isset($filters['id'])) {
			$this->db->where('custom_field_id',$filters['id']);
		}
		
		$this->db->order_by('custom_field_order','ASC');
		
		$result = $this->db->get('custom_fields');
		
		$fields = array();
		foreach ($result->result_array() as $field) {
			$fields[] = array(
							'id' => $field['custom_field_id'],
							'group_id' => $field['custom_field_group'],
							'friendly_name' => $field['custom_field_friendly_name'],
							'name' => $field['custom_field_name'],
							'type' => $field['custom_field_type'],
							'options' => (!empty($field['custom_field_options'])) ? unserialize($field['custom_field_options']) : array(),
							'help' => (!empty($field['custom_field_help_text'])) ? $field['custom_field_help_text'] : FALSE,
							'order' => $field['custom_field_order'],
							'width' => (!empty($field['custom_field_width'])) ? $field['custom_field_width'] : FALSE,
							'default' => (!empty($field['custom_field_default'])) ? $field['custom_field_default'] : FALSE,
							'required' => ($field['custom_field_required'] == 1) ? TRUE : FALSE,
							'validators' => (!empty($field['custom_field_validators'])) ? unserialize($field['custom_field_validators']) : array(),
							'data' => (!empty($field['custom_field_data'])) ? unserialize($field['custom_field_data']) : array()
						);
		}
		
		// cache for later
		$this->cache[base64_encode(serialize($filters))] = $fields;
		
		return $fields;
	}
	
	/**
	* New Custom Field
	*
	* Create new custom field record and modify the database
	*
	* @param int $group The field group id
	* @param string $name The label used for the field (will be converted automatically to a system-friendly name)
	* @param string $type Either "text", "textarea", "password", "wysiwyg", "select", "multiselect", "radio", "checkbox", or "file"
	* @param array|string $options A string of newline-separated values like (test=value\nvalue2, etc.) or an array like array(array(name => 'test', value => 'test2')) etc. (default: array())
	* @param string $default Default selected value (default: '')
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%") (default: '')
	* @param string $help A string of help text (default: '')
	* @param boolean $required TRUE to require the field for submission (default: FALSE)
	* @param array $validators One or more validators values in an array: whitespace, email, alphanumeric, numeric, domain (default: array())
	* @param string|boolean $db_table The database table to add the field to, else FALSE (default: FALSE)
	* @param array $data Array of additional data which should be associated with this field (default: array())
	*
	* @return int $custom_field_id
	*/
	public function new_custom_field ($group, $name, $type, $options = array(), $default = '', $width = '', $help = '', $required = FALSE, $validators = array(), $db_table = FALSE, $data = array()) {
		$options = $this->format_options($options);
		
		// calculate system name
		$this->load->helper('clean_string');
		$system_name = clean_string($name);
		
		if (strlen($system_name) > 50) {
			$system_name = substr($system_name, 0, 50);
		}
		
		// get next order
		$this->db->where('custom_field_group',$group);
		$this->db->order_by('custom_field_order','DESC');
		$result = $this->db->get('custom_fields');
		
		if ($result->num_rows() > 0) {
			$last_field = $result->row_array();
			$order = $last_field['custom_field_order'] + 1;
		}
		else {
			$order = '1';
		}
		
		$insert_fields = array(
							'custom_field_group' => $group,
							'custom_field_name' => $system_name,
							'custom_field_friendly_name' => $name,
							'custom_field_order' => $order,
							'custom_field_type' => $type,
							'custom_field_default' => $default,
							'custom_field_width' => $width,
							'custom_field_options' => (empty($options)) ? serialize(array()) : serialize($options),
							'custom_field_required' => ($required == FALSE) ? '0' : '1',
							'custom_field_validators' => (empty($validators)) ? serialize(array()) : serialize($validators),
							'custom_field_help_text' => $help,
							'custom_field_data' => (!empty($data)) ? serialize($data) : ''
						);
						
		$this->db->insert('custom_fields',$insert_fields);
		
		$insert_id = $this->db->insert_id();
		
		// modify DB structure?
		if ($db_table != FALSE) {
			$this->load->dbforge();
			
			$db_type = $this->get_type($type);
			
			$this->dbforge->add_column($db_table, array($system_name => array('type' => $db_type)));
		}
		
		return $insert_id;
	}
	
	/**
	* Get appropriate database field type
	*
	* @param string $type
	*
	* @return string database field type
	*/
	private function get_type ($type) {
		$CI =& get_instance();
		
		$CI->load->library('custom_fields/fieldtype');
		$CI->fieldtype->load_type($type);
		$db_type = $CI->fieldtype->$type->db_column();
		
		return $db_type;
	}
	
	/**
	* Update Custom Field
	*
	* Updates custom field records as well as modifies the appropriate database
	*
	* @param int $custom_field_id The custom field ID to edit
	* @param int $group The field group id
	* @param string $name The label used for the field (will be converted automatically to a system-friendly name)
	* @param string $type Either "text", "textarea", "password", "wysiwyg", "select", "multiselect", "radio", "checkbox", or "file"
	* @param array|string $options A string of newline-separated values like (test=value\nvalue2, etc.) or an array like array(array(name => 'test', value => 'test2')) etc.
	* @param string $default Default selected value
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%")
	* @param string $help A string of help text
	* @param boolean $required TRUE to require the field for submission
	* @param array $validators One or more validators values in an array: whitespace, email, alphanumeric, numeric, domain
	* @param string|boolean $db_table The database table to add the field to, else FALSE
	* @param array $data Array of additional data which should be associated with this field
	*
	* @return boolean TRUE
	*/
	public function update_custom_field ($custom_field_id, $group, $name, $type, $options = array(), $default, $width, $help, $required = FALSE, $validators = array(), $db_table = FALSE, $data = array()) {
		$options = $this->format_options($options);
		
		// we may need the old system name
		if ($db_table != FALSE) {
			$old_system_name = $this->get_system_name($custom_field_id);
		}
		
		// calculate system name
		$this->load->helper('clean_string');
		$system_name = clean_string($name);
		
		if (strlen($system_name) > 50) {
			$system_name = substr($system_name, 0, 50);
		}
		
		$update_fields = array(
							'custom_field_group' => $group,
							'custom_field_name' => $system_name,
							'custom_field_friendly_name' => $name,
							'custom_field_type' => $type,
							'custom_field_options' => serialize($options),
							'custom_field_default' => $default,
							'custom_field_width' => $width,
							'custom_field_required' => ($required == FALSE) ? '0' : '1',
							'custom_field_validators' => serialize($validators),
							'custom_field_help_text' => $help,
							'custom_field_data' => (!empty($data)) ? serialize($data) : ''
						);
						
		$this->db->update('custom_fields',$update_fields,array('custom_field_id' => $custom_field_id));
		
		// modify DB structure?
		if ($db_table != FALSE) {
			$this->load->dbforge();
			
			$db_type = $this->get_type($type);
			
			$this->dbforge->modify_column($db_table, array($old_system_name => array('name' => $system_name, 'type' => $db_type)));
		}
		
		return TRUE;
	}
	
	/**
	* Delete Custom Field
	*
	* Delete custom field record and modify database
	*
	* @param int $id The ID of the field
	* @param string|boolean $db_table The database table to reflect the changes, else FALSE
	*
	* @return boolean TRUE
	*/
	public function delete_custom_field ($id, $db_table = FALSE) {
		if ($db_table != FALSE) {
			$this->load->dbforge();
			
			$system_name = $this->get_system_name($id);
			
			$this->dbforge->drop_column($db_table, $system_name);
		}
		
		$this->db->delete('custom_fields',array('custom_field_id' => $id));
		
		return TRUE;
	}
	
	/**
	* Get System Name
	*
	* Gets the system name for a field, by ID
	* 
	* @param int $id The custom field ID
	*
	* @return string The custom field system/table name
	*/
	public function get_system_name ($id) {
		$this->db->select('custom_field_name');
		$this->db->where('custom_field_id',$id);
		$result = $this->db->get('custom_fields');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		else {
			$field = $result->row_array();
			
			return $field['custom_field_name'];
		}
	}
	
	/**
	* New Custom Field Group
	*
	* Creates a custom field group
	*
	* @param string $name
	*
	* @return int $custom_field_group_id
	*/
	public function new_group ($name) {
		$insert_fields = array(
								'custom_field_group_name' => $name
							);
		
		$this->db->insert('custom_field_groups',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Delete Group
	*
	* Deletes the custom field group as well as all fields in it
	*
	* @param int $group_id
	*/
	public function delete_group ($id, $db_table = FALSE) {
		// delete fields, if possible
		if (!empty($db_table)) {
			$fields = $this->get_custom_fields(array('group' => $id));
			if (!empty($fields)) {
				foreach ($fields as $field) {
					$this->delete_custom_field($field['id'], $db_table);
				}
			}
		}
		else {
			$this->db->delete('custom_fields',array('custom_field_group' => $id));
		}
		
		$this->db->delete('custom_field_groups',array('custom_field_group_id' => $id));
		
		return;
	}
	
	/**
	* Format the options array
	*
	* @param string|array $options Either a newline-separated field of values, or an array of pre-formatted values
	* 
	* @return array Array of options with a series of array(name=>value) arrays holding each option
	*/
	private function format_options ($options) {
		// format $options into a series of name/value child arrays
		if (is_array($options)) {
			// we'll trust it's in good order
		}
		else {
			$new_options = explode("\n",$options);
			$options = array();
			if (!empty($new_options)) {
				foreach ($new_options as $option) {
					if (!empty($option)) {
						if (!strstr($option,'=')) {
							$option .= '=';
						}
						
						list($o_name,$o_value) = explode('=',$option);
						
						if (empty($o_value)) {
							$o_value = $o_name;
						}
						
						$options[] = array(
										'name' => $o_name,
										'value' => $o_value
									);
					}
				}
			}
		}
		
		return $options;
	}
}