<?php

/*
* Custom Fields Model
*
* Supports many areas of the app by providing a universal format for custom fields,
* their validation, and management.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Custom_fields_model extends CI_Model {
	/*
	* @var array Holds previous get_custom_fields calls in memory
	*/
	var $cache;
	
	/*
	* @var string The full path to upload custom file uploads to
	*/
	var $upload_directory;
	
	function __construct() {
		parent::CI_Model();
		
		// specify the upload directory, will be created if it doesn't exist
		$this->upload_directory = setting('path_custom_field_uploads');
	}
	
	/*
	* Reset Order for a Field Group
	*
	* @param int $custom_field_group
	*
	*/
	
	function reset_order ($custom_field_group) {
		$this->db->update('custom_fields',array('custom_field_order' => '0'), array('custom_field_group' => $custom_field_group));
	}
	
	/*
	* Update Order
	*
	* @param int $field_id
	* @param int $new_order
	*/
	
	function update_order ($field_id, $new_order) {
		$this->db->update('custom_fields',array('custom_field_order' => $new_order), array('custom_field_id' => $field_id));
	}
	
	/*
	* Get Rules
	* 
	* Generates a CodeIgniter form_validation array for custom fields based on the field group ID
	*
	* @param int $field_group_id
	*
	* @return array $rules CodeIgniter rules
	*/
	function get_validation_rules ($field_group_id) {
		$fields = $this->get_custom_fields(array('group' => $field_group_id));
		
		$this->load->helper('valid_domain');
		
		$return = array();
		
		foreach ($fields as $field) {
			$rules = array();
			
			if ($field['type'] != 'file' and isset($field['validators'])) {
				foreach ($field['validators'] as $validator) {
					if ($validator == 'whitespace') {
						$rules[] = $validator['trim'];
					}
					elseif ($validator == 'alphanumeric') {
						$rules[] = $validator['alpha_numeric'];
					}
					elseif ($validator == 'numeric') {
						$rules[] = $validator['numeric'];
					}
					elseif ($validator == 'domain') {
						$rules[] = $validator['valid_domain'];
					}
				}
				
				if (!empty($rules)) {
					$return[] = array(
									'field' => $field['name'],
									'label' => $field['friendly_name'],
									'rules' => $rules
								);
				}
			}
		}
		
		return $return;
	}
	
	/*
	* Validate Files
	* 
	* Secondary validation of any form that may have files in it,
	* as they can't be handled by CodeIgniter
	*
	* @param int $field_group_id
	*
	* @return boolean TRUE upon success
	*/
	function validate_files ($field_group_id) {
		$fields = $this->get_custom_fields(array('group' => $field_group_id));
		
		$this->load->helper('file_extension');
		
		foreach ($fields as $field) {
			if ($field['type'] == 'file') {
				if (!empty($field['validators']) and is_array($field['validators'])) {
					if (is_uploaded_file($_FILES[$field['name']]['tmp_name']) and !in_array(file_extension($_FILES[$field['name']]['name']),$field['validators'])) {
						return FALSE;
					}
				}
			}
		}
		
		return TRUE;
	}
	
	/*
	* Post to Array
	*
	* Convert all custom field data for a field group from POST into an array
	*
	* @param int $field_group_id
	*
	* @return array Field data
	*/
	function post_to_array($field_group_id) {
		if (empty($field_group_id)) {
			return array();
		}
	
		$fields = $this->get_custom_fields(array('group' => $field_group_id));
		
		$array = array();
		foreach ($fields as $field) {
			if ($field['type'] == 'multiselect') {
				$array[$field['name']] = serialize($this->input->post($field['name']));
			}
			elseif ($field['type'] == 'file') {
				// do the upload
				if (isset($_FILES[$field['name']]) and is_uploaded_file($_FILES[$field['name']]['tmp_name'])) {
					$CI =& get_instance();
					$CI->settings_model->make_writeable_folder($this->upload_directory,FALSE);
					
					$config = array();
					$config['upload_path'] = $this->upload_directory;
					$config['allowed_types'] = '*';
					$config['encrypt_name'] = TRUE;
					
					// upload class may already be loaded
					if (isset($this->upload)) {
						$this->upload->initialize($config);
					}
					else {
						$this->load->library('upload', $config);
					}
					
					// do upload
					if (!$this->upload->do_upload($field['name'])) {
						die(show_error($this->upload->display_errors()));
					}
					
					$filename = $this->upload->file_name;
					
					// reset filename in case we use the uploader again
					$this->upload->file_name = '';
					
					$array[$field['name']] = str_replace(FCPATH,'',$this->upload_directory . $filename);
				}
			}
			else {
				$array[$field['name']] = $this->input->post($field['name']);
			}
		}
		
		return $array;
	}
	
	/*
	* Get Custom Field
	*
	* @param int $custom_field_id
	*
	* @return boolean|array $custom_field or FALSE
	*/
	function get_custom_field($custom_field_id) {
		$return = $this->get_custom_fields(array('id' => $custom_field_id));
		
		if (empty($return)) {
			return FALSE;
		}
		else {
			return $return[0];
		}
	}
	
	/*
	* Get Custom Fields
	*
	* Retrieves custom fields ordered by custom_field_order, with caching
	* 
	* @param $filters['group'] The custom field group
	* @param $filters['id'] A custom field ID
	*
	* @return array $fields The custom fields
	*/
	function get_custom_fields ($filters = array()) {
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
							'friendly_name' => $field['custom_field_friendly_name'],
							'name' => $field['custom_field_name'],
							'type' => $field['custom_field_type'],
							'options' => (!empty($field['custom_field_options'])) ? unserialize($field['custom_field_options']) : array(),
							'help' => $field['custom_field_help_text'],
							'order' => $field['custom_field_order'],
							'width' => $field['custom_field_width'],
							'default' => $field['custom_field_default'],
							'required' => ($field['custom_field_required'] == 1) ? TRUE : FALSE,
							'validators' => (!empty($field['custom_field_validators'])) ? unserialize($field['custom_field_validators']) : array()
						);
		}
		
		// cache for later
		$this->cache[base64_encode(serialize($filters))] = $fields;
		
		return $fields;
	}
	
	/*
	* New Custom Field
	*
	* Create new custom field record and modify the database
	*
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
	*
	* @return int $custom_field_id
	*/
	function new_custom_field ($group, $name, $type, $options = array(), $default, $width, $help, $required = FALSE, $validators = array(), $db_table = FALSE) {
		$options = $this->format_options($options);
		
		// calculate system name
		$this->load->helper('clean_string');
		$system_name = clean_string($name);
		
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
							'custom_field_options' => serialize($options),
							'custom_field_required' => ($required == FALSE) ? '0' : '1',
							'custom_field_validators' => serialize($validators),
							'custom_field_help_text' => $help
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
	
	/*
	* Get appropriate database field type
	*
	* @param string $type
	*
	* @return string database field type
	*/
	function get_type ($type) {
		switch($type) {
			case 'textarea':
				$db_type = 'TEXT';
				break;
			case 'select':
			case 'checkbox':
			case 'radio':
				$db_type = 'VARCHAR(100)';
				break;
			case 'date':
				$db_type = 'DATE';
				break;
			default:
				$db_type = 'VARCHAR(250)';
		}
		
		return $db_type;
	}
	
	/*
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
	*
	* @return boolean TRUE
	*/
	function update_custom_field ($custom_field_id, $group, $name, $type, $options = array(), $default, $width, $help, $required = FALSE, $validators = array(), $db_table = FALSE) {
		$options = $this->format_options($options);
		
		// we may need the old system name
		if ($db_table != FALSE) {
			$old_system_name = $this->get_system_name($custom_field_id);
		}
		
		// calculate system name
		$this->load->helper('clean_string');
		$system_name = clean_string($name);
		
		// get next order
		$this->db->where('custom_field_group',$group);
		$this->db->order_by('custom_field_order','DESC');
		$result = $this->db->get('custom_fields');
		
		if ($result->num_rows() > 0) {
			$last_field = $result->row_array();
			$order = $last_field['custom_field_order'];
		}
		else {
			$order = '1';
		}
		
		$update_fields = array(
							'custom_field_group' => $group,
							'custom_field_name' => $system_name,
							'custom_field_friendly_name' => $name,
							'custom_field_order' => $order,
							'custom_field_type' => $type,
							'custom_field_options' => serialize($options),
							'custom_field_default' => $default,
							'custom_field_width' => $width,
							'custom_field_required' => ($required == FALSE) ? '0' : '1',
							'custom_field_validators' => serialize($validators),
							'custom_field_help_text' => $help
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
	
	/*
	* Delete Custom Field
	*
	* Delete custom field record and modify database
	*
	* @param int $id The ID of the field
	* @param string|boolean The database table to reflect the changes, else FALSE
	*
	* @return boolean TRUE
	*/
	function delete_custom_field ($id, $db_table = FALSE) {
		if ($db_table != FALSE) {
			$this->load->dbforge();
			
			$system_name = $this->get_system_name($id);
			
			$this->dbforge->drop_column($db_table, $system_name);
		}
		
		$this->db->delete('custom_fields',array('custom_field_id' => $id));
		
		return TRUE;
	}
	
	/*
	* Get System Name
	*
	* Gets the system name for a field, by ID
	* 
	* @param int $id The custom field ID
	*
	* @return string The custom field system/table name
	*/
	function get_system_name ($id) {
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
	
	/*
	* New Custom Field Group
	*
	* Creates a custom field group
	*
	* @param string $name
	*
	* @return int $custom_field_group_id
	*/
	function new_group ($name) {
		$insert_fields = array(
								'custom_field_group_name' => $name
							);
		
		$this->db->insert('custom_field_groups',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/*
	* Delete Group
	*
	* Deletes the custom field group as well as all fields in it
	*
	* @param int $group_id
	*/
	function delete_group ($id) {
		$this->db->delete('custom_field_groups',array('custom_field_group_id' => $id));
		$this->db->delete('custom_fields',array('custom_field_group' => $id));
		
		return TRUE;
	}
	
	/*
	* Format the options array
	*
	* @param string|array $options Either a newline-separated field of values, or an array of pre-formatted values
	* 
	* @return array Array of options with a series of array(name=>value) arrays holding each option
	*/
	function format_options ($options) {
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