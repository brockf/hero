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
	
	function NewGroup ($name) {
		$insert_fields = array(
								'usergroup_name' => $name
							);
												
		$this->db->insert('usergroups',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	function MakeDefault ($group_id) {
		$this->db->update('usergroups',array('default' => $group_id), array('id' => $group_id));
		
		return TRUE;	
	}
	
	function GetDefault () {
		$this->db->select('usergroup_id');
		$this->db->where('usergroup_default','1');
		
		$result = $this->db->get('usergroups');
		
		$group = $result->row_array();
		
		return $group['usergroup_id'];
	}
}