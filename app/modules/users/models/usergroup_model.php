<?php

/**
* Usergroup Model 
*
* Contains all the methods used to create, update, and delete usergroups.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Usergroup_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* New Usergroup
	*
	* @param string $name The usergroup name
	*
	* @return int $usergroup_id
	*/
	function new_group ($name) {
		$insert_fields = array(
								'usergroup_name' => $name
							);
												
		$this->db->insert('usergroups',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Update Usergroup
	*
	* @param int $group_id The group ID
	* @param string $name The new group name
	*
	* @return boolean TRUE
	*/
	function update_group ($group_id, $name) {
		$update_fields = array(
								'usergroup_name' => $name
							);
												
		$this->db->update('usergroups',$update_fields, array('usergroup_id' => $group_id));
		
		return TRUE;
	}
	
	/**
	* Make Default Group
	*
	* Sets the default usergroup for new registrations
	*
	* @param int $group_id The usergroup to set it to
	*
	* @return boolean TRUE
	*/
	function make_default ($group_id) {
		$this->db->update('usergroups',array('usergroup_default' => '0'));
	
		$this->db->update('usergroups',array('usergroup_default' => '1'), array('usergroup_id' => $group_id));
		
		return TRUE;	
	}
	
	/**
	* Delete Usergroup
	*
	* @param int $group_id
	*
	* @return boolean TRUE
	*/
	function delete_group ($group_id) {
		$users = $this->user_model->get_users(array('group' => $group_id));
		foreach ($users as $user) {
			$this->user_model->remove_group($user['id'], $group_id);
		}
	
		$this->db->delete('usergroups',array('usergroup_id' => $group_id));
		
		return TRUE;
	}
	
	/**
	* Get Default Usergroup
	*
	* @return int $group_id The default usergroup ID
	*/
	function get_default () {
		$this->db->select('usergroup_id');
		$this->db->where('usergroup_default','1');
		
		$result = $this->db->get('usergroups');
		
		$group = $result->row_array();
		
		return $group['usergroup_id'];
	}
	
	/**
	* Get Usergroup
	* 
	* @param int $group_id The usergroup ID to retrieve
	*
	* @return array|boolean Array of group data, else FALSE
	*/
	function get_group ($group_id) {
		$return = $this->get_usergroups(array('id' => $group_id));
		
		if (empty($return)) {
			return FALSE;
		}
		else {
			return $return[0];
		}
	}
	
	/**
	* Get Usergroups
	*
	* @param $filters['id'] The group ID
	*
	* return array Array of group data, else FALSE
	*/
	function get_usergroups ($filters = array()) {
		if (isset($filters['id'])) {
			$this->db->where('usergroup_id',$filters['id']);
		}
	
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