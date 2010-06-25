<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Custom Fields Control Panel
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
		
		$this->navigation->module_link('Go Back','javascript:history.go(-1)');
	}
	
	/*
	* Introduction to custom fields, with individual links
	*/
	function index () {
		$this->load->view('custom_fields.php');
	}
	
	/*
	* Re-position a custom field group
	*
	* @param int $custom_field_group Custom field group ID
	* @param string $return_url A URL-encoded, base64_encoded, site_url() call (e.g., url_encode(base64_encode(site_url('admincp/'))))
	*
	* @return view
	*/
	function order($custom_field_group, $return_url) {
		$return_url = base64_decode(urldecode($return_url));
		
		// load model
		$this->load->model('custom_fields_model');
		
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $custom_field_group));
		
		if (empty($custom_fields)) {
			die(show_error('No custom fields in this group.'));
		}
		
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Custom Fields',array('sortable'));
		$form->custom_fields($custom_fields);
		
		$data = array(
						'form' => $form->display(),
						'return_url' => urlencode(base64_encode($return_url)),
						'field_group_id' => $custom_field_group
					);
	
		$this->load->view('arrange_fields.php', $data);
	}
	
	function save ($return_url) {
		redirect(base64_decode(urldecode($return_url)));
	}
	
	/*
	* Save Order
	*/
	function save_order ($custom_field_group)
	{
		$this->load->model('custom_fields_model');
		
		// reset all custom field orders
		$this->custom_fields_model->reset_order($custom_field_group);
		
		$count = 1;
		foreach ($_POST['row'] as $field_id) {
			$this->custom_fields_model->update_order($field_id, $count);
			
			$count++;
		}
	}
}
