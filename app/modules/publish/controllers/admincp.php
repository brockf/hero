<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Content Control Panel
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
				
		$this->navigation->parent_active('publish');
	}
	
	function types () {
		$this->navigation->module_link('New Content Type',site_url('admincp/publish/type_new'));
		
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'width' => '5%'),
						array(
							'name' => 'Name',
							'width' => '40%'),
						array(
							'name' => 'Standard Content',
							'width' => '15%',
							),
						array(
							'name' => 'Member Privileges',
							'width' => '15%'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);
		
		$this->dataset->columns($columns);
		$this->dataset->datasource('content_type_model','get_content_types');
		$this->dataset->base_url(site_url('admincp/publish/types'));
		$this->dataset->rows_per_page(1000);

		// total rows
		$total_rows = $this->db->get('content_types')->num_rows(); 
		$this->dataset->total_rows($total_rows);

		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/publish/types_delete');
		
		$this->load->view('content_types');
	}
	
	function types_delete ($types, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('content_type_model');
		
		$types = unserialize(base64_decode($this->asciihex->HexToAscii($types)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($types as $type) {
			$this->content_type_model->delete_content_type($type);
		}
		
		$this->notices->SetNotice('Content type(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function type_new () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('New Content Type');
		$form->text('Name', 'name', '', 'Enter the name for this type of content.', TRUE, 'e.g., News Articles', TRUE);
		$form->fieldset('Options');
		$form->checkbox('Standard page fields?', 'is_standard', '1', TRUE, 'If checked, each content item will have the following fields: "Title", "URL Path", and "Topic".  These are standard items which allow ' . setting('app_name') . ' to display this content as an individual web page, include in blog/topic listings, etc.');
		$form->checkbox('Restrict to certain member groups?', 'is_privileged', '1', TRUE, 'If checked, you will be able to specify the member group(s) that have permissions to see this content (or make it public).');
		
		$data = array(
					'form' => $form->display(),
					'form_title' => 'Create New Content Type',
					'form_action' => site_url('admincp/publish/post_type/new'),
					'action' => 'new'
					);
		
		$this->load->view('type_form.php',$data);
	}
	
	function type_edit ($id) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($id);
		
		if (empty($type)) {
			die(show_error('No content type exists by that ID.'));
		}
	
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('New Content Type');
		$form->text('Name', 'name', $type['name'], 'Enter the name for this type of content.', TRUE, 'e.g., News Articles', TRUE);
		$form->fieldset('Options');
		$form->checkbox('Standard page fields?', 'is_standard', '1', $type['is_standard'], 'If checked, each content item will have the following fields: "Title", "URL Path", and "Topic".  These are standard items which allow ' . setting('app_name') . ' to display this content as an individual web page, include in blog/topic listings, etc.');
		$form->checkbox('Restrict to certain member groups?', 'is_privileged', '1', $type['is_privileged'], 'If checked, you will be able to specify the member group(s) that have permissions to see this content (or make it public).');
		
		$data = array(
					'form' => $form->display(),
					'form_title' => 'Edit Content Type',
					'form_action' => site_url('admincp/publish/post_type/edit/' . $type['id']),
					'action' => 'edit'
					);
		
		$this->load->view('type_form.php',$data);
	}
	
	function post_type ($action = 'new', $id = false) {		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('name','Name','required|trim');
		
		if ($this->form_validation->run() === FALSE) {
			$this->notices->SetError('You must include a content type name.');
			$error = TRUE;
		}	
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/publish/type_new');
				return FALSE;
			}
			else {
				redirect('admincp/publish/type_edit/' . $id);
				return FALSE;
			}	
		}
		
		$this->load->model('content_type_model');
		
		if ($action == 'new') {
			$type_id = $this->content_type_model->new_content_type(
																$this->input->post('name'),
																($this->input->post('is_standard') == '1') ? TRUE : FALSE,
																($this->input->post('is_privileged') == '1') ? TRUE : FALSE
															);
															
			$this->notices->SetNotice('Content type added successfully.');
			
			redirect('admincp/publish/type_fields/' . $type_id);
		}
		else {
			$this->content_type_model->update_content_type(
													$id,
													$this->input->post('name'),
													($this->input->post('is_standard') == '1') ? TRUE : FALSE,
													($this->input->post('is_privileged') == '1') ? TRUE : FALSE
												);
												
			$this->notices->SetNotice('Content type updated successfully.');
			
			redirect('admincp/publish/types');
		}
		
		return TRUE;
	}
	
	function type_fields ($id) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($id);
		
		if (empty($type)) {
			die(show_error('No content type exists with this ID.'));
		}
	
		$this->navigation->module_link('Add Field',site_url('admincp/publish/type_field_add/' . $type['id']));
		$this->navigation->module_link('Preview &amp; Arrange Fields',site_url('admincp/custom_fields/order/' . $type['custom_field_group_id'] . '/' . urlencode(base64_encode(site_url('admincp/publish/type_fields/' . $type['id'])))));
		
		$this->load->library('dataset');
		
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
		$this->dataset->datasource('custom_fields_model','get_custom_fields', array('group' => $type['custom_field_group_id']));
		$this->dataset->base_url(site_url('admincp/publish/type_fields/' . $type['id']));
		$this->dataset->rows_per_page(1000);
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/publish/type_fields_delete/' . $type['id']);
		
		$data = array(
				'type' => $type
		);
		
		$this->load->view('type_fields.php', $data);
	}
	
	function type_field_add ($id) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($id);
	
		$data = array(
						'field' => array(),
						'type' => $type,
						'form_title' => 'New Field',
						'form_action' => site_url('admincp/publish/post_type_field/new')
					);
	
		$this->load->view('type_field_form.php', $data);
	}
	
	function post_type_field ($action, $id = FALSE) {
		if ($this->input->post('name') == '') {
			$this->notices->SetError('Field name is a required field.');
			$error = TRUE;
		}
		
		if (in_array($this->input->post('type'),array('select','radio')) and trim($this->input->post('options')) == '') {
			$this->notices->SetError('You must specify field options.');
			$error = TRUE;
		}
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/publish/type_field_add/' . $this->input->post('content_type_id'));
				return false;
			}
			else {
				redirect('admincp/publish/type_field_edit/' . $this->input->post('content_type_id') . '/' . $id);
			}	
		}
		
		// build validators
		$validators = array();
		
		if ($this->input->post('file')) {
			if ($this->input->post('validate_email') == '1') { $validators[] = 'email'; }
			if ($this->input->post('validate_whitespace') == '1') { $validators[] = 'whitespace'; }
			if ($this->input->post('validate_alphanumeric') == '1') { $validators[] = 'alphanumeric'; }
			if ($this->input->post('validate_numeric') == '1') { $validators[] = 'numeric'; }
			if ($this->input->post('validate_domain') == '1') { $validators[] = 'domain'; }
		}
		else {
			$validators = explode(' ',$this->input->post('file_validation'));
		}
		
		// build required
		$required = ($this->input->post('required') == '1') ? TRUE : FALSE;
		
		$this->load->model('custom_fields_model');
		$this->load->model('content_type_model');
		
		$type = $this->content_type_model->get_content_type($this->input->post('content_type_id'));
		
		if ($action == 'new') {
			$field_id = $this->custom_fields_model->new_custom_field(
																$type['custom_field_group_id'],
																$this->input->post('name'),
																$this->input->post('type'),
																$this->input->post('options'),
																$this->input->post('default'),
																$this->input->post('width'),
																$this->input->post('help'),
																$required,
																$validators,
																$type['system_name']
															);
			
			$this->notices->SetNotice('Field added successfully.');
		}
		else {
			$this->custom_fields_model->update_custom_field(
												$id,
												$type['custom_field_group_id'],
												$this->input->post('name'),
												$this->input->post('type'),
												$this->input->post('options'),
												$this->input->post('default'),
												$this->input->post('width'),
												$this->input->post('help'),
												$required,
												$validators,
												$type['system_name']
											);
															
			$this->notices->SetNotice('Field edited successfully.');
		}
		
		redirect('admincp/publish/type_fields/' . $type['id']);
		
		return TRUE;
	}
	
	function type_field_edit ($content_type_id, $id) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($content_type_id);
	
		$this->load->model('custom_fields_model');
		$field = $this->custom_fields_model->get_custom_field($id);
		
		$data = array(
						'field' => $field,
						'type' => $type,
						'form_title' => 'Edit Field',
						'form_action' => site_url('admincp/publish/post_type_field/edit/' . $field['id'])
					);
	
		$this->load->view('type_field_form.php', $data);
	}
	
	function type_fields_delete ($content_type_id, $fields, $return_url) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($content_type_id);
	
		$this->load->library('asciihex');
		$this->load->model('custom_fields_model');
		
		$fields = unserialize(base64_decode($this->asciihex->HexToAscii($fields)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($fields as $field) {
			$this->custom_fields_model->delete_custom_field($field, $type['system_name']);
		}
		
		$this->notices->SetNotice('Field(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
}