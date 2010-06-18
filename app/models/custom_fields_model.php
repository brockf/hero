<?php

class Custom_fields_model extends CI_Model {
	var $cache;
	var $upload_directory;

	function __construct() {
		parent::CI_Model();
		
		$this->upload_directory = BASEPATH . 'writeable/custom_uploads/';
	}
	
	/*
	* Get Rules
	* 
	* Generates a CodeIgniter form_validation array for custom fields based on the group ID
	*
	* @param int $field_group_id
	* @return array $rules CodeIgniter rules */
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
		$fields = $this->get_custom_fields(array('group' => $field_group_id));
		
		$array = array();
		foreach ($fields as $field) {
			if ($field['type'] == 'multiselect') {
				$array[$field['name']] = serialize($this->input->post($field['name']));
			}
			elseif ($field['type'] == 'file') {
				// do the upload
				if (is_uploaded_file($_FILES[$field['name']]['tmp_name'])) {
					if (!is_dir($this->upload_directory)) {
						mkdir($this->upload_directory);
						chmod($this->upload_directory,'0755');
					}
					
					if (!is_writable($this->upload_directory)) {
						die(show_error('Custom field upload directory is not writeable.  Create /writeable/custom_fields and CHMOD 0755 or 0777 to fix.'));
					}
					
					$filename = time() . get_ext($_FILES[$field['name']]['name']);
				
					move_uploaded_file($_FILES[$field['name']]['tmp_name'],$this->upload_directory . $filename);
					
					$array[$field['name']] = str_replace(BASEPATH,'',$this->upload_directory . $filename);
				}
			}
			else {
				$array[$field['name']] = $this->input->post($field['name']);
			}
		}
		
		return $array;
	}
	
	function get_custom_fields ($filters = array()) {
		if (isset($this->cache[base64_encode(serialize($filters))])) {
			return $this->cache[base64_encode(serialize($filters))];
		}
	
		if (isset($filters['group'])) {
			$this->db->where('custom_field_group',$filters['group']);
		}
		
		$this->db->order_by('custom_field_order','ASC');
		
		$result = $this->db->get('custom_fields');
		
		$fields = array();
		foreach ($result->result_array() as $field) {
			$fields[] = array(
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
			$order = $last_field['custom_field_order'];
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
			default:
				$db_type = 'VARCHAR(250)';
		}
		
		return $db_type;
	}
	
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
	
	function delete_custom_field ($id, $db_table = FALSE) {
		if ($db_table != FALSE) {
			$this->load->dbforge();
			
			$system_name = $this->get_system_name($id);
			
			$this->dbforge->drop_column($db_table, $system_name);
		}
		
		$this->db->delete('custom_fields',array('custom_field_id' => $id));
		
		return TRUE;
	}
	
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