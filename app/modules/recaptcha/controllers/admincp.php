<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Twitter Control Panel
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
	
	function index () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Settings');
		$form->value_row('&nbsp;','<div style="float:left; width: 600px">Enter your Recaptcha Keys (e.g., 6Ldr1gwTAAAAAPhx68fHH4xxxxxxxxxxxxxxxxxx).  <br />Edit your form.thtml form to post to {url path="recaptcha/form/submit"} <br />Then enter {recaptcha} in the form template before the submit button to begin protecting your forms with ReCaptcha 2.</div>');
								   
		$form->text('Site Key','recaptcha_site_key', $this->config->item('recaptcha_site_key'));
		$form->text('Secret Key','recaptcha_secret_key', $this->config->item('recaptcha_secret_key'));
	
		$data = array(
					'form_title' => 'Recaptcha Spam Protection',
					'form_action' => site_url('admincp/recaptcha/post_configure'),
					'form' => $form->display(),
					'form_button' => 'Save Configuration'
				);
	
		$this->load->view('generic', $data);
	}
	
	function post_configure () {
		$this->settings_model->update_setting('recaptcha_site_key', $this->input->post('recaptcha_site_key'));
		$this->settings_model->update_setting('recaptcha_secret_key', $this->input->post('recaptcha_secret_key'));
		
		$this->notices->SetNotice('Recaptcha Site Keys configuration saved successfully.');
			
		return redirect('admincp/recaptcha');
	}
}