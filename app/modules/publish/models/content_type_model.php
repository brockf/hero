<?php

/**
* Content Type Model
*
* Manages content types, including their custom fields
*
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class Content_type_model extends CI_Model
{
	function __construct()
	{
		parent::CI_Model();
	}
	
	/*
	* Create New Content Type
	*
	* Creates a new content type, including the associated table and custom field group
	*
	* @param string $name
	* @param boolean $is_standard Include Title, URL Path, and Topic dropdown?
	* @param boolean $is_privileged Include Restrict Access to Member Group(s) Dropdown?
	*
	* @return int $content_type_id
	*/
	function new_content_type ($name, $is_standard = TRUE, $is_privileged = FALSE) {
		// get system name
		$this->load->helper('clean_string');
		$system_name = clean_string($name);
		
		// make sure table doesn't already exist
		if ($this->db->table_exists($system_name)) {
			die(show_error('There is already a table in the database by the name of ' . $system_name . '.  You should rename your content type to avoid a conflict.'));
		}
		
		// create custom field group
		$this->load->model('custom_fields_model');
		$custom_field_group_id = $this->custom_fields_model->new_group('Content: ' . $name);
		
		$insert_fields = array(
							'content_type_friendly_name' => $name,
							'content_type_system_name' => $system_name,
							'content_type_is_standard' => ($is_standard == TRUE) ? '1' : '0',
							'content_type_is_privileged' => ($is_privileged == TRUE) ? '1' : '0',
							'custom_field_group_id' => $custom_field_group_id
						);
						
		$this->db->insert('content_types', $insert_fields);
		
		$content_type_id = $this->db->insert_id();
		
		// database functions
		$this->load->dbforge();
		
		// add ID, date, edit_date, admin rows
		$this->dbforge->add_field('`' . $system_name . '_id` INT(11) NOT NULL auto_increment PRIMARY KEY');
		$this->dbforge->add_field('`' . $system_name . '_date` DATETIME NOT NULL');
		$this->dbforge->add_field('`' . $system_name . '_modified` DATETIME NOT NULL');
		$this->dbforge->add_field('`' . $system_name . '_user` INT(11) NOT NULL');
		
		// create table
		$this->dbforge->create_table($system_name);
		
		return $content_type_id;
	}
	
	/*
	* Update Content Type
	*
	* Updates a content type
	*
	* @param int $content_type_id
	* @param string $name
	* @param boolean $is_standard Include Title, URL Path, and Topic dropdown?
	* @param boolean $is_privileged Include Restrict Access to Member Group(s) Dropdown?
	*
	* @return boolean TRUE
	*/
	function update_content_type ($content_type_id, $name, $is_standard = TRUE, $is_privileged = FALSE) {
		$update_fields = array(
							'content_type_friendly_name' => $name,
							'content_type_is_standard' => ($is_standard == TRUE) ? '1' : '0',
							'content_type_is_privileged' => ($is_privileged == TRUE) ? '1' : '0'
						);
						
		$this->db->update('content_types', $update_fields, array('content_type_id' => $content_type_id));
		
		return TRUE;
	}
	
	/*
	* Delete Content Type
	*
	* @param int $content_type_id
	*
	* @return boolean TRUE
	*/
	function delete_content_type ($content_type_id) {
		$type = $this->get_content_type($content_type_id);
		
		if (empty($type)) {
			return FALSE;
		}
		
		// delete custom field group
		$this->load->model('custom_fields_model');
		$this->custom_fields_model->delete_group($type['custom_field_group_id']);
		
		/*// delete content from content database
		$this->load->model('content_model');
		$content = $this->content_model->get_contents(array('type' => $type['id']));
		foreach ($content as $item) {
			$this->content_model->delete_content($item['id']);
		}*/
		
		// delete table
		$this->load->dbforge();
		$this->dbforge->drop_table($type['system_name']);
		
		// delete content type
		$this->db->delete('content_types',array('content_type_id' => $type['id']));
		
		return TRUE;
	}
	
	/*
	* Get Content Type
	*
	* @param $content_type_id
	*
	* @return array $content_type
	*/
	function get_content_type ($id) {
		$type = $this->get_content_types(array('id' => $id));
		
		if (empty($type)) {
			return FALSE;
		}
		else {
			return $type[0];
		}
	}
	
	/*
	* Get Content Types
	*
	*/
	function get_content_types ($filters = array()) {
		if (isset($filters['id'])) {
			$this->db->where('content_type_id',$filters['id']);
		}
	
		$this->db->order_by('content_type_friendly_name');
		$result = $this->db->get('content_types');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$types = array();
		foreach ($result->result_array() as $row) {
			$types[] = array(
						'id' => $row['content_type_id'],
						'name' => $row['content_type_friendly_name'],
						'system_name' => $row['content_type_system_name'],
						'is_privileged' => ($row['content_type_is_privileged'] == '1') ? TRUE : FALSE,
						'is_standard' => ($row['content_type_is_standard'] == '1') ? TRUE : FALSE,
						'custom_field_group_id' => $row['custom_field_group_id']
					);
		}
		
		return $types;
	}
}