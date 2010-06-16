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
		
		$this->SetSettings();
	}
	
	function SetSettings () {
		$result = $this->db->get('settings');
		
		foreach ($result->result_array() as $setting) {
			$this->config->set_item($setting['setting_name'], $setting['setting_value']);
		}
		
		return TRUE;
	}
	
	function UpdateSetting ($name, $value) {
		$this->db->update('settings',array('setting_value' => $value), array('setting_name' => $name));
		
		return TRUE;
	}
	
	function NewSetting ($setting_group = '0', $setting_name, $setting_value, $setting_help = '', $setting_time = FALSE, $setting_type = 'text') {
		$insert_fields = array(
							 	'setting_group' => $setting_group,
							 	'setting_name' => $setting_name,
							 	'setting_value' => $setting_value,
							 	'setting_help' => $setting_help,
							 	'setting_time' => ($setting_time == FALSE) ? date('Y-m-d H:i:s') : $setting_time,
							 	'setting_type' => $setting_type
							);				                                
		$this->db->insert('settings',$insert_fields);
		
		return $this->db->insert_id();	
	}
}