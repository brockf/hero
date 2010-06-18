<?php

class Custom_fields_model extends CI_Model {
	function __construct() {
		parent::CI_Model();
	}
	
	function new_custom_field ($group, $name, $type, $options = array(), $help, $required = FALSE, $validators = array(), $db_table = FALSE) {
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
	
	function update_custom_field ($custom_field_id, $group, $name, $type, $options = array(), $help, $required = FALSE, $validators = array(), $db_table = FALSE) {
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
			
			$db_type = $this->get_type($type);
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