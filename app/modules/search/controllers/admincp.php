<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Search Control Panel
*
* Configure the options for your site search
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
		$this->load->model('publish/content_type_model');
		$content_types = $this->content_type_model->get_content_types(array('is_standard' => TRUE));
		
		// prep summary field options for each type
		$this->load->model('custom_fields_model');
		$field_options = array();
		
		foreach ($content_types as $type) {
			$field_options[$type['id']] = array();
			
			$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
			$field_options[$type['id']]['0'] = 'Do not include a summary with this contents\' results.';
			$field_options[$type['id']]['content_title'] = 'Title';
			$field_options[$type['id']]['content_date'] = 'Date Created';
			$field_options[$type['id']]['content_modified'] = 'Date Modified';
			$field_options[$type['id']]['link_url_path'] = 'URL Path';
			foreach ($custom_fields as $field) {
				$field_options[$type['id']][$field['name']] = $field['friendly_name'];
			}
		}
		
		$data = array(
						'content_types' => $content_types,
						'field_options' => $field_options,
						'form_action' => site_url('admincp/search/save')
					);
		$this->load->view('search_configuration', $data);	
	}
	
	function save () {
		// content types
		$this->load->model('publish/content_type_model');
		$content_types = $this->content_type_model->get_content_types();
		
		$search_content_types = array();
		
		foreach ($content_types as $type) {
			if ($this->input->post('content_type_' . $type['id']) == '1') {
				$search_content_types[$type['id']] = $this->input->post('summary_field_' . $type['id']);
			}
		}
		
		$this->settings_model->update_setting('search_content_types',serialize($search_content_types));
		
		// products
		$search_products = ($this->input->post('search_products') == '1') ? '1' : '0';
		
		$this->settings_model->update_setting('search_products', $search_products);
		
		// delay
		$this->settings_model->update_setting('search_delay', $this->input->post('search_delay'));
		
		// trim
		$this->settings_model->update_setting('search_trim', $this->input->post('search_trim'));
		
		$this->notices->SetNotice('Search configuration updated successfully.');
		
		redirect('admincp/search');
	}
}