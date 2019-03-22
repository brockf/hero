<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Paywall Control Panel
*
* Configure the options for your site paywall
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
	
	function index() {
		$this->load->helper('form');
		$this->load->helper('template_files');
		
		$template_files = template_files();
		
		$data = array(
				'template_files' => $template_files,
				'paywall_auto' => setting('paywall_auto'),
				'paywall_template' => setting('paywall_template')
			);
			
		$this->load->view('paywall_configuration', $data);
	}	
	
	function save () {
		$this->settings_model->update_setting('paywall_auto', $this->input->post('paywall_auto'));
		$this->settings_model->update_setting('paywall_template', $this->input->post('paywall_template'));
		
		$this->notices->SetNotice('Paywall configuration updated successfully.');
		
		redirect('admincp/paywall');
	}
}