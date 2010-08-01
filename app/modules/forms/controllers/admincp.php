<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Forms Control Panel
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
	
	function index () {	
		$this->navigation->module_link('New Form',site_url('admincp/forms/add'));
	
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'text'
							),
						array(
							'name' => 'Title',
							'width' => '45%',
							'filter' => 'title',
							'type' => 'text'
							),
						array(
							'name' => 'Responses',
							'width' => '20%'
							),
						array(
							'name' => '',
							'width' => '30%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('form_model','get_forms');
		$this->dataset->base_url(site_url('admincp/forms'));
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/forms/delete');
		
		$this->load->view('forms');
	}
	
	function responses ($form_id) {
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'text'
							),
						array(
							'name' => 'Date',
							'width' => '30%',
							'type' => 'date',
							'filter' => 'date',
							'field_start_date' => 'start_date',
							'field_end_date' => 'end_date'),
						array(
							'name' => 'Member',
							'width' => '35%'
							),
						array(
							'name' => '',
							'width' => '30%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('form_model','get_responses');
		$this->dataset->base_url(site_url('admincp/forms'));
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/forms/delete_responses');
		
		$this->load->view('forms');
	}
	
	function delete ($forms, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('form_model');
		
		$forms = unserialize(base64_decode($this->asciihex->HexToAscii($forms)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($forms as $form) {
			$this->form_model->delete_form($form);
		}
		
		$this->notices->SetNotice('Form(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function add () {
		define('INCLUDE_CKEDITOR',TRUE);
		
		$this->load->helper('form');
	
		// template
		$this->load->library('Admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Design');
		$this->load->helper('template_files');
		$template_files = template_files();
		$form->dropdown('Output Template', 'template', $template_files, 'form.thtml', FALSE, TRUE, 'This template in your theme directory will be used to display this form.');
		
		// privileges
		$this->load->model('users/usergroup_model');
		$groups = $this->usergroup_model->get_usergroups();
		
		$privileges = new Admin_form;
		$privileges->fieldset('Member Group Access');
		
		$options = array();
		$options[0] = 'Public / Any Member Group';
		foreach ($groups as $group) {
			$options[$group['id']] = $group['name'];
		}
		
		$privileges->dropdown('Access Requires Membership to Group','privileges',$options,array(0), TRUE, FALSE, 'Select multiple member groups by holding the CTRL or CMD button and selecting multiple options.');
		
		$privilege_form = $privileges->display();
		
		$data = array(
					'admin_form' => $form->display(),
					'privilege_form' => $privilege_form,
					'form_title' => 'Create New Form',
					'form_action' => site_url('admincp/forms/post/new')
				);
		
		$this->load->view('form', $data);
	}
	
	function edit ($id) {
		define('INCLUDE_CKEDITOR',TRUE);
		
		$this->load->helper('form');
		
		$this->load->model('form_model');
		$form = $this->form_model->get_form($id);
		
		if (empty($form)) {
			die(show_error('No form exists with that ID.'));
		}
	
		// template
		$this->load->library('Admin_form');
		$template_form = new Admin_form;
		
		$template_form->fieldset('Design');
		$this->load->helper('template_files');
		$template_files = template_files();
		$template_form->dropdown('Output Template', 'template', $template_files, $form['template'], FALSE, TRUE, 'This template in your theme directory will be used to display this form.');
		
		// privileges
		$this->load->model('users/usergroup_model');
		$groups = $this->usergroup_model->get_usergroups();
		
		$privileges = new Admin_form;
		$privileges->fieldset('Member Group Access');
		
		$options = array();
		$options[0] = 'Public / Any Member Group';
		foreach ($groups as $group) {
			$options[$group['id']] = $group['name'];
		}
		
		$privileges->dropdown('Access Requires Membership to Group','privileges',$options,(!empty($form['privileges'])) ? $form['privileges'] : array(0), TRUE, FALSE, 'Select multiple member groups by holding the CTRL or CMD button and selecting multiple options.');
		
		$privilege_form = $privileges->display();
		
		$data = array(
					'form' => $form,
					'admin_form' => $template_form->display(),
					'privilege_form' => $privilege_form,
					'form_title' => 'Edit Form',
					'form_action' => site_url('admincp/forms/post/edit/' . $form['id'])
				);
		
		$this->load->view('form', $data);
	}
	
	function post ($action = 'new', $id = FALSE) {
		$this->load->model('form_model');
		
		if ($action == 'new') {
			$form_id = $this->form_model->new_form(
										$this->input->post('title'),
										$this->input->post('url_path'),
										$this->input->post('text'),
										$this->input->post('button_text'),
										$this->input->post('redirect'),
										$this->input->post('email'),
										$this->input->post('privileges'),
										$this->input->post('template')
									);
										
			$this->notices->SetNotice('Form added successfully.');
			
			redirect('admincp/forms/fields/' . $form_id);
		}
		elseif ($action == 'edit') {
			$this->form_model->update_form(
									$id,
									$this->input->post('title'),
									$this->input->post('url_path'),
									$this->input->post('text'),
									$this->input->post('button_text'),
									$this->input->post('redirect'),
									$this->input->post('email'),
									$this->input->post('privileges'),
									$this->input->post('template')
								);
										
			$this->notices->SetNotice('Form edited successfully.');
			
			redirect('admincp/forms');
		}
	}
	
	function fields ($id) {
		$this->load->model('form_model');
		$form = $this->form_model->get_form($id);
		
		if (empty($form)) {
			die(show_error('No form exists with this ID.'));
		}
	
		$this->navigation->module_link('Add Field',site_url('admincp/forms/field_add/' . $form['id']));
		$this->navigation->module_link('Preview &amp; Arrange Fields',site_url('admincp/custom_fields/order/' . $form['custom_field_group_id'] . '/' . urlencode(base64_encode(site_url('admincp/forms/fields/' . $form['id'])))));
		
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
		$this->dataset->datasource('custom_fields_model','get_custom_fields', array('group' => $form['custom_field_group_id']));
		$this->dataset->base_url(site_url('admincp/forms/fields/' . $form['id']));
		$this->dataset->rows_per_page(1000);
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/publish/fields_delete/' . $form['id']);
		
		$data = array(
				'form' => $form
		);
		
		$this->load->view('fields', $data);
	}
	
	function field_add ($id) {
		$this->load->model('form_model');
		$form = $this->form_model->get_form($id);
	
		$data = array(
						'field' => array(),
						'form' => $form,
						'form_title' => 'New Field',
						'form_action' => site_url('admincp/forms/post_field/new')
					);
	
		$this->load->view('field_form', $data);
	}
	
	function post_field ($action, $id = FALSE) {
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
				redirect('admincp/forms/field_add/' . $this->input->post('form_id'));
				return false;
			}
			else {
				redirect('admincp/forms/field_edit/' . $this->input->post('form_id') . '/' . $id);
			}	
		}
		
		// build validators
		$validators = array();
		
		if ($this->input->post('type') != 'file') {
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
		$this->load->model('form_model');
		
		$form = $this->form_model->get_form($this->input->post('form_id'));
		
		if ($action == 'new') {
			$field_id = $this->custom_fields_model->new_custom_field(
																$form['custom_field_group_id'],
																$this->input->post('name'),
																$this->input->post('type'),
																$this->input->post('options'),
																$this->input->post('default'),
																$this->input->post('width'),
																$this->input->post('help'),
																$required,
																$validators,
																$form['table_name']
															);
			
			$this->notices->SetNotice('Field added successfully.');
		}
		else {
			$this->custom_fields_model->update_custom_field(
												$id,
												$form['custom_field_group_id'],
												$this->input->post('name'),
												$this->input->post('type'),
												$this->input->post('options'),
												$this->input->post('default'),
												$this->input->post('width'),
												$this->input->post('help'),
												$required,
												$validators,
												$form['table_name']
											);
															
			$this->notices->SetNotice('Field edited successfully.');
		}
		
		redirect('admincp/forms/fields/' . $form['id']);
		
		return TRUE;
	}
	
	function field_edit ($form_id, $id) {
		$this->load->model('form_model');
		$form = $this->form_model->get_form($form_id);
	
		$this->load->model('custom_fields_model');
		$field = $this->custom_fields_model->get_custom_field($id);
		
		$data = array(
						'field' => $field,
						'form' => $form,
						'form_title' => 'Edit Field',
						'form_action' => site_url('admincp/forms/post_field/edit/' . $field['id'])
					);
	
		$this->load->view('field_form', $data);
	}
	
	function fields_delete ($form_id, $fields, $return_url) {
		$this->load->model('form_model');
		$form = $this->form_model->get_form($form_id);
	
		$this->load->library('asciihex');
		$this->load->model('custom_fields_model');
		
		$fields = unserialize(base64_decode($this->asciihex->HexToAscii($fields)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($fields as $field) {
			$this->custom_fields_model->delete_custom_field($field, $form['system_name']);
		}
		
		$this->content_type_model->build_search_index($form['id']);
		
		$this->notices->SetNotice('Field(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
}