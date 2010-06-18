<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->navigation->parent_active('members');
	}
	
	function data () {
		$this->navigation->parent_active('configuration');
		
		$this->navigation->module_link('Add Custom Field',site_url('admincp/users/data_add'));
		
		$this->load->model('admincp/dataset','dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%'
							),
						array(
							'name' => 'Human Name',
							'width' => '25%'
							),
						array(
							'name' => 'System Name',
							'width' => '25%'
							),
						array(
							'name' => 'Type',
							'type' => 'text',
							'width' => '20%'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('user_model','get_custom_fields');
		$this->dataset->base_url(site_url('admincp/users/data'));
		$this->dataset->rows_per_page(1000);
		
		// total rows
		$total_rows = $this->db->get('user_fields')->num_rows(); 
		$this->dataset->total_rows($total_rows);
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/users/data_delete');
		
		$this->load->view('data.php');
	}
	
	function data_add () {
		$this->navigation->parent_active('configuration');
		
		$data = array(
						'field' => array(),
						'form_title' => 'New Member Data Field',
						'form_action' => site_url('admincp/users/post_data/new')
					);
	
		$this->load->view('data_form.php', $data);
	}
	
	function post_data ($action, $id = FALSE) {
		if ($this->input->post('name') == '') {
			$this->notices->SetError('Field name is a required field.');
			$error = true;
		}
		
		if ($this->input->post('type') != 'text' and $this->input->post('type') != 'textarea' and trim($this->input->post('options')) == '') {
			$this->notices->SetError('You must specify field options.');
			$error = true;
		}
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/users/data_add');
				return false;
			}
			else {
				redirect('admincp/users/data_edit/' . $id);
			}	
		}
		
		// build validators
		$validators = array();
		
		if ($this->input->post('validate_email') == '1') { $validators[] = 'email'; }
		if ($this->input->post('validate_whitespace') == '1') { $validators[] = 'whitespace'; }
		if ($this->input->post('validate_alphanumeric') == '1') { $validators[] = 'alphanumeric'; }
		if ($this->input->post('validate_numeric') == '1') { $validators[] = 'numeric'; }
		if ($this->input->post('validate_domain') == '1') { $validators[] = 'domain'; }
		
		// build required
		$required = ($this->input->post('required') == '1') ? TRUE : FALSE;
		
		if ($action == 'new') {
			$email_id = $this->user_model->new_custom_field(
																$this->input->post('name'),
																$this->input->post('type'),
																$this->input->post('options'),
																$this->input->post('help'),
																$this->input->post('billing_equiv'),
																$required,
																$validators
															);
			
			$this->notices->SetNotice('Field added successfully.');
		}
		else {
			$this->user_model->update_custom_field(
												$id,
												$this->input->post('name'),
												$this->input->post('type'),
												$this->input->post('options'),
												$this->input->post('help'),
												$this->input->post('billing_equiv'),
												$required,
												$validators
											);
															
			$this->notices->SetNotice('Field edited successfully.');
		}
		
		redirect('admincp/users/data');
		
		return TRUE;
	}
	
	function data_edit ($id) {
		$this->navigation->parent_active('configuration');
		
		$field = $this->user_model->get_custom_field($id);
		
		$data = array(
						'field' => $field,
						'form_title' => 'Edit Member Data Field',
						'form_action' => site_url('admincp/users/post_data/edit/' . $field['id'])
					);
	
		$this->load->view('data_form.php', $data);
	}
	
	/**
	* Delete Custom Fields
	*
	* Delete gateways as passed from the dataset
	*
	* @param string Hex'd, base64_encoded, serialized array of user_field ID's
	* @param string Return URL for Dataset
	*
	* @return bool Redirects to dataset
	*/
	function data_delete ($fields, $return_url) {
		$this->load->library('asciihex');
		
		$fields = unserialize(base64_decode($this->asciihex->HexToAscii($fields)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($fields as $field) {
			$this->user_model->delete_custom_field($field);
		}
		
		$this->notices->SetNotice('Field(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
}