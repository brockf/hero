<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Settings Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->navigation->parent_active('configuration');
	}

	function index ()
	{
		$groups = $this->settings_model->get_setting_groups(array('sort' => 'setting_group_name'));
		
		foreach ($groups as $key => $group) {
			$settings[$group['id']] = $this->settings_model->get_settings(array('group_id' => $group['id'], 'show_hidden' => FALSE, 'sort' => 'setting_name'));
			
			if (empty($settings[$group['id']])) {
				unset($groups[$key]);
			}
		}
		reset($groups);
		
		$data = array(
					'settings' => $settings,
					'groups' => $groups
			);
		
		$this->load->view('settings.php', $data);
	}
	
	function save ()
	{
		$current = $this->settings_model->get_setting($this->input->post('name'));
		
		$value = $this->input->post('value');
		$value = urldecode($value);
		
		$this->settings_model->update_setting($this->input->post('name'),$value);
		
		if ($current['type'] == 'textarea') {
			$value = nl2br($value);
		}
		
		echo $value;
	}
	
	function save_toggle ()
	{
		die();
		$current = $this->settings_model->get_setting($this->input->post('name'));
		
		$new_value = ($current['value'] == '1') ? '0' : '1';
		
		$this->settings_model->update_setting($this->input->post('name'),$new_value);
		
		$setting = $this->settings_model->get_setting($this->input->post('name'));
		
		echo $setting['toggle_value'];
	}
}