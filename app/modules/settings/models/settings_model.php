<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Settings Model 
*
* Contains all the methods used to create, update, and delete settings.
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class Settings_model extends CI_Model
{
	function __construct()
	{
		parent::CI_Model();
		
		$this->set_settings();
	}
	
	function set_settings () {
		$result = $this->db->get('settings');
		
		foreach ($result->result_array() as $setting) {
			$this->config->set_item($setting['setting_name'], $setting['setting_value']);
		}
		
		return TRUE;
	}
	
	function update_setting ($name, $value) {
		$this->db->update('settings',array('setting_value' => $value), array('setting_name' => $name));
		
		return TRUE;
	}
	
	function new_setting ($setting_group = '0', $setting_name, $setting_value, $setting_help = '', $setting_time = FALSE, $setting_type = 'text') {
		$insert_fields = array(
							 	'setting_group' => $setting_group,
							 	'setting_name' => $setting_name,
							 	'setting_value' => $setting_value,
							 	'setting_help' => $setting_help,
							 	'setting_update_date' => ($setting_time == FALSE) ? date('Y-m-d H:i:s') : $setting_time,
							 	'setting_type' => $setting_type
							);				                                
		$this->db->insert('settings',$insert_fields);
		
		return $this->db->insert_id();	
	}
	
	function get_setting ($name) {
		$settings = $this->get_settings(array('name' => $name));
		
		if (empty($settings)) {
			return FALSE;
		}
		else {
			foreach ($settings as $setting_id => $setting) {
				return $setting;
			}
		}
	}
	
	/*
	* Get Settings
	*
	* @param $filters['group_id'] Setting group ID
	* @param $filters['name'] The setting name
	* @param $filters['sort'] Field to sort by
	* @param $filters['sort_dir'] ASC or DESC
	*
	* @return array $settings
	*/
	function get_settings ($filters = array()) {
		if (isset($filters['group_id'])) {
			$this->db->where('setting_group',$filters['group_id']);
		}
		
		if (isset($filters['name'])) {
			$this->db->where('setting_name',$filters['name']);
		}
		
		if (isset($filters['sort'])) {
			$sort_dir = (isset($filters['sort_dir']) and $filters['sort_dir'] == 'DESC') ? 'DESC' : 'ASC';
			$this->db->order_by($filters['sort'], $sort_dir);
		}
		
		$result = $this->db->join('settings_groups','settings_groups.setting_group_id = settings.setting_group');
		
		$result = $this->db->get('settings');
		
		$settings = array();
		foreach ($result->result_array() as $setting) {
			// options array
			if (!empty($setting['setting_options'])) {
				$setting['setting_options'] = unserialize($setting['setting_options']);
			}
		
			$settings[$setting['setting_id']] = array(
								'id' => $setting['setting_id'],
								'name' => $setting['setting_name'],
								'group_id' => $setting['setting_group'],
								'group_name' => $setting['setting_group_name'],
								'value' => $setting['setting_value'],
								'help' => $setting['setting_help'],
								'last_update' => $setting['setting_update_date'],
								'type' => $setting['setting_type'],
								'options' => $setting['setting_options'],
								'toggle_value' => ($setting['setting_type'] != 'toggle') ? FALSE : $setting['setting_options'][$setting['setting_value']]
							);
		}
		
		return $settings;
	}
	
	/*
	* Get Setting Groups
	*
	* @param $filters['sort'] Field to sort by
	* @param $filters['sort_dir'] ASC or DESC
	*
	* @return array $groups
	*/
	function get_setting_groups ($filters = array()) {
		if (isset($filters['sort'])) {
			$sort_dir = (isset($filters['sort_dir']) and $filters['sort_dir'] == 'DESC') ? 'DESC' : 'ASC';
			$this->db->order_by($filters['sort'], $sort_dir);
		}
		
		$result = $this->db->get('settings_groups');
		
		$groups = array();
		
		foreach ($result->result_array() as $group) {
			$groups[$group['setting_group_id']] = array(
								'id' => $group['setting_group_id'],
								'name' => $group['setting_group_name'],
								'help' => $group['setting_group_help']
							);
		}
		
		return $groups;
	}
}