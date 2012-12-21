<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Forms Control Panel
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
				
		$this->admin_navigation->parent_active('publish');
	}
	
	function index () {	
		$this->admin_navigation->module_link('New Form',site_url('admincp/forms/add'));
	
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
	
	function responses ($form_id = FALSE) {
		if (!empty($form_id) and is_numeric($form_id)) {
			$this->session->set_userdata('responses_form_id', $form_id);
		}
		else {
			// this is likely a string of filters, not a form_id
			$form_id = $this->session->userdata('responses_form_id');
		}
	
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'response_id'
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
							'width' => '35%',
							'filter' => 'username',
							'type' => 'text'
							),
						array(
							'name' => '',
							'width' => '30%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('form_model','get_responses', array('form_id' => $form_id));
		$this->dataset->base_url(site_url('admincp/forms/responses'));
		
		// Set total rows here so we don't run out of memory 
		// trying to pull all of our results at once.
		$this->load->model('form_model');
		$this->dataset->total_rows($this->form_model->count_responses($form_id, $this->dataset->get_filter_array()));
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/forms/delete_responses');
		
		$this->load->view('responses');
	}
	
	function delete_responses ($responses, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('form_model');
		
		$responses = unserialize(base64_decode($this->asciihex->HexToAscii($responses)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($responses as $response) {
			$this->form_model->delete_response($this->session->userdata('responses_form_id'), $response);
		}
		
		$this->notices->SetNotice('Response(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function response ($form_id, $response_id) {
		$this->admin_navigation->module_link('Back to Responses','javascript:history.go(-1)');
	
		$this->load->model('form_model');
		
		$response = $this->form_model->get_response($form_id, $response_id);
		$form = $this->form_model->get_form($form_id);
		
		if (empty($response)) {
			die(show_error('Response doesn\'t exist.'));
		}
		
		$lines = array();
		$lines['Date'] = date('F j, Y, g:i a', strtotime($response['submission_date']));
		
		if (!empty($response['user_id'])) {
			$lines['Member Username'] = '<a href="' . site_url('admincp/users/profile/' . $response['user_id']) . '">' . $response['member_username'] . '</a>';
			$lines['Member Name'] = $response['member_first_name'] . ' ' . $response['member_last_name'];
			$lines['Member Email'] = $response['member_email'];
		}
		else {
			$lines['Member'] = 'None';
		}

		foreach ($form['custom_fields'] as $field) {
			if ($field['type'] == 'multiselect') {
				$value = implode(', ', unserialize($response[$field['name']]));
			}
			elseif ($field['type'] == 'file') {
				$value = $custom_fields[$field['name']] . ' (Download: ' . site_url('writeable/custom_uploads/' . $response[$field['name']]);
			}
			elseif ($field['type'] == 'date') {
				$value = date('F j, Y', strtotime($response[$field['name']]));
			}
			else {
				$value = $response[$field['name']];
				
				// automatically parse links
				$value = preg_replace('/(http:\/\/[^ )\r\n!]+)/i', '<a target="_blank" href="\\1">\\1</a>', $value);
				$value = preg_replace('/(https:\/\/[^ )\r\n!]+)/i', '<a target="_blank" href="\\1">\\1</a>', $value);
				$value = preg_replace('/([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4})/i','<a href="mailto:\\1">\\1</a>', $value);
			}
			
			$lines[$field['friendly_name']] = $value;
		}
		
		$this->load->view('response', array('form' => $form, 'response' => $response, 'lines' => $lines));
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
	
		$this->admin_navigation->module_link('Add Field',site_url('admincp/forms/field_add/' . $form['id']));
		$this->admin_navigation->module_link('Preview &amp; Arrange Fields',site_url('admincp/custom_fields/order/' . $form['custom_field_group_id'] . '/' . urlencode(base64_encode(site_url('admincp/forms/fields/' . $form['id'])))));
		
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
		$this->dataset->action('Delete','admincp/forms/fields_delete/' . $form['id']);
		
		$data = array(
				'form' => $form
		);
		
		$this->load->view('fields', $data);
	}
	
	function field_add ($id) {
		$this->load->model('form_model');
		$form = $this->form_model->get_form($id);
	
		return redirect('admincp/custom_fields/add/' . $form['custom_field_group_id'] . '/forms/' . $form['table_name']);
	}
		
	function field_edit ($form_id, $id) {
		$this->load->model('form_model');
		$form = $this->form_model->get_form($form_id);
	
		$this->load->model('custom_fields_model');
		$field = $this->custom_fields_model->get_custom_field($id);
		
		return redirect('admincp/custom_fields/edit/' . $field['id'] . '/forms/' . $form['table_name']);
	}
	
	function fields_delete ($form_id, $fields, $return_url) {
		$this->load->model('form_model');
		$form = $this->form_model->get_form($form_id);
	
		$this->load->library('asciihex');
		$this->load->model('custom_fields_model');
		
		$fields = unserialize(base64_decode($this->asciihex->HexToAscii($fields)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($fields as $field) {
			$this->custom_fields_model->delete_custom_field($field, $form['table_name']);
		}
		
		$this->notices->SetNotice('Field(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
}