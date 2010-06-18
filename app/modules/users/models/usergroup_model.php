<?php

/**
* Usergroup Model 
*
* Contains all the methods used to create, update, and delete usergroups.
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class Usergroup_model extends CI_Model
{
	function __construct()
	{
		parent::CI_Model();
	}
	
	function new_group ($name) {
		$insert_fields = array(
								'usergroup_name' => $name
							);
												
		$this->db->insert('usergroups',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	function make_default ($group_id) {
		$this->db->update('usergroups',array('default' => $group_id), array('id' => $group_id));
		
		return TRUE;	
	}
	
	function get_default () {
		$this->db->select('usergroup_id');
		$this->db->where('usergroup_default','1');
		
		$result = $this->db->get('usergroups');
		
		$group = $result->row_array();
		
		return $group['usergroup_id'];
	}
	
	function get_usergroups ($filters = array()) {
		$this->db->order_by('usergroup_name');
	
		$result = $this->db->get('usergroups');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$usergroups = array();
		foreach ($result->result_array() as $group) {
			$usergroups[] = array(
								'id' => $group['usergroup_id'],
								'name' => $group['usergroup_name'],
								'default' => ($group['usergroup_default'] == '1') ? TRUE : FALSE
							);
		}
		
		return $usergroups;
	}
}