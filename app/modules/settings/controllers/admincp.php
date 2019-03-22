<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Settings Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->admin_navigation->parent_active('configuration');
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
		
		$this->load->view('settings', $data);
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
		$current = $this->settings_model->get_setting($this->input->post('name'));
		
		$new_value = ($current['value'] == '1') ? '0' : '1';
		
		$this->settings_model->update_setting($this->input->post('name'),$new_value);
		
		$setting = $this->settings_model->get_setting($this->input->post('name'));
		
		echo $setting['toggle_value'];
	}
	
	function modules () {
		$this->load->library('dataset');
			
			$columns = array(
						array(
							'name' => 'Module Name',
							'width' => '30%'
							),
						array(
							'name' => 'Status',
							'width' => '15%'
							),
						array(
							'name' => 'Version',
							'width' => '20%'
							),
						array(
							'name' => '',
							'width' => '35%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('modules/module_model','get_modules');
		$this->dataset->base_url(site_url('admincp/settings/modules'));
		
		// initialize the dataset
		$this->dataset->initialize();
		
		// get protected core modules
		$this->config->load('core_modules');
		$core_modules = $this->config->item('core_modules');
		
		// get settings
		$settings = $this->settings_model->get_settings(array('group_id' => '1', 'show_hidden' => TRUE, 'sort' => 'setting_name'));
		
		// only show modules_ settings
		foreach ($settings as $key => $setting) {
			if (strpos($setting['name'],'modules_') !== 0) {
				unset($settings[$key]);
			}
		}
				
		$data = array(
					'core_modules' => $core_modules,
					'settings' => $settings
				);
				
		return $this->load->view('modules', $data);
	}
	
	function modules_settings () {
		$auto_install = ($this->input->post('auto_install')) ? '1' : '0';
		
		$this->settings_model->update_setting('modules_auto_install', $auto_install);
		
		return redirect('admincp/settings/modules');
	}
	
	function module_uninstall ($module) {
		return $this->load->view('module_uninstall_confirm', array('module' => $module));
	}
	
	function module_uninstall_confirm () {
		$module = $this->input->post('module');
		
		// get protected core modules
		$this->config->load('core_modules');
		$core_modules = $this->config->item('core_modules');
		
		if (!empty($module) and !in_array($module, $core_modules)) {
			$this->module_model->uninstall($module);
		}
		
		return redirect('admincp/settings/modules');
	}
	
	function module_install ($module) {
		$this->module_model->install($module);
		
		return redirect('admincp/settings/modules');
	}
}