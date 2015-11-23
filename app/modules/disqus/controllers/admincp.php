<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Disqus Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->admin_navigation->parent_active('configuration');
	}
	
	function index() {
		$this->load->library('custom_fields/form_builder');
		$shortname = $this->form_builder->add_field('text')
							->name('disqus_shortname')
							->label('Disqus Shortname')
							->validators(array('alpha_numeric','trim'))
							->value(setting('disqus_shortname'))
							->help('Don\'t have a shortname?  Register your site at <a href="http://www.disqus.com">Disqus</a>.')
							->required(TRUE);
		 
		$data = array(
					'form_title' => 'Disqus Configuration',
					'form_action' => site_url('admincp/disqus/post_config'),
					'form' => $this->form_builder->output_admin(),
					'form_button' => 'Save Configuration',
					'disqus_shortname' => setting('disqus_shortname')
				);
	
		$this->load->view('generic', $data);
	}
	
	function post_config () {
		$this->settings_model->update_setting('disqus_shortname',$this->input->post('disqus_shortname'));
		
		$this->notices->SetNotice('Disqus configuration saved.');
		
		redirect('admincp/disqus');
	}
}