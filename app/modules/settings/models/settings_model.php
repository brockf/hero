<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Settings Model 
*
* Contains all the methods used to update system settings.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Settings_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		
		$this->set_settings();
	}
	
	/**
	* Make Writeable Folder
	*
	* This function creates a writeable folder and places a blank index.html file into it
	* It throws errors upon failure.
	*
	* @param string $path The filepath
	* @param boolean $no_access Set to TRUE to write a .htaccess file which will deny all access to this folder directly
	*
	* @return boolean
	*/
	function make_writeable_folder ($path = '', $no_access = FALSE) {
		if (!is_dir($path)) {
			if (mkdir($path, setting('write_mode'))) {
				if (!chmod($path, setting('write_mode'))) {
					die(show_error('Failed to CHMOD: ' . $path));
				}
				
				$this->load->helper('file');
				
				if (!is_writable($path)) {
					die(show_error('Folder appeared to get created but it\'s not writable: ' . $path));
				}
				
				if ($no_access == TRUE) {
					write_file($path . '/index.html','');
					write_file($path . '/.htaccess',"<Files *>\nDeny from all\n</Files>");
				}
			}
			else {
				die(show_error('Cannot create folder: ' . $path));
			}
		}
		
		if (!is_writable($path)) {
			die(show_error('Despite our best efforts, this folder could not be created or written to: ' . $path));
		}
		else {
			return TRUE;
		}
	}
	
	/**
	* Auto-set Settings
	*
	* Takes all settings from the `settings` table and places them in the active
	* $this->config array
	*
	* @return boolean TRUE upon completion
	*/
	function set_settings () {
		$result = $this->db->get('settings');
		
		foreach ($result->result_array() as $setting) {
			$this->config->set_item($setting['setting_name'], $setting['setting_value']);
		}
		
		return TRUE;
	}
	
	/**
	* Update Setting
	*
	* Updates a setting's value by name
	*
	* @param string $name The current name
	* @param string $value The new setting value
	*
	* @return boolean
	*/
	function update_setting ($name, $value) {
		if (empty($name)) {
			return FALSE;
		}
		
		$this->db->update('settings',array('setting_value' => $value), array('setting_name' => $name));
		
		// update current settings during this runtime
		$this->config->set_item($name, $value);
		
		return TRUE;
	}
	
	/**
	* Delete Setting
	*
	* Deletes a setting
	*
	* @param string $name
	*
	* @return void
	*/
	function delete_setting ($name) {
		$this->db->delete('settings', array('setting_name' => $name));
		$this->config->set_item($name, FALSE);
	}
	
	/**
	* New Setting
	*
	* Creates a new setting
	*
	* @param int $setting_group The setting group ID
	* @param string $setting_name The name of the setting
	* @param string $setting_value The default value of the setting
	* @param string $setting_help The help text for the setting (default: '')
	* @param string $setting_type The type of setting it is (options: toggle, textarea, text) (default: text)
	* @param string $setting_options A serialized array of options for toggle settings (default: '')
	* @param date $setting_time The time of the creation of the setting (default: FALSE)
	* @param boolean $setting_hidden Is the setting hidden from the normal Settings manager? (default: FALSE)
	*
	* @return int $setting_id
	*/
	function new_setting ($setting_group = '0', $setting_name, $setting_value, $setting_help = '', $setting_type = 'text', $setting_options = '', $setting_time = FALSE, $setting_hidden = FALSE) {
		$insert_fields = array(
							 	'setting_group' => $setting_group,
							 	'setting_name' => $setting_name,
							 	'setting_value' => $setting_value,
							 	'setting_help' => $setting_help,
							 	'setting_update_date' => ($setting_time == FALSE) ? date('Y-m-d H:i:s') : $setting_time,
							 	'setting_type' => $setting_type,
							 	'setting_options' => $setting_options,
							 	'setting_hidden' => ($setting_hidden == TRUE) ? '1' : '0'
							);				                                
		$this->db->insert('settings',$insert_fields);
		
		// update current settings during this runtime
		$this->config->set_item($setting_name, $setting_value);
		
		return $this->db->insert_id();	
	}
	
	/**
	* Get Setting
	*
	* Gets a setting by name
	*
	* @param string $name Setting name
	*
	* @return boolean|string Setting value, FALSE if it doesn't exist
	*/
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
	
	/**
	* Get Settings
	*
	* @param int $filters['group_id'] Setting group ID
	* @param string $filters['name'] The setting name
	* @param boolean $filters['show_hidden'] Show hidden settings?  Default: TRUE
	* @param string $filters['sort'] Field to sort by
	* @param string $filters['sort_dir'] ASC or DESC
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
		
		if (isset($filters['show_hidden']) and $filters['show_hidden'] == FALSE) {
			$this->db->where('setting_hidden','0');
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
	
	/**
	* Get Setting Groups
	*
	* @param string $filters['sort'] Field to sort by
	* @param string $filters['sort_dir'] ASC or DESC
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