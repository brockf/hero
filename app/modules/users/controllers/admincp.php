<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->navigation->parent_active('members');
	}
	
	function index () {
		$this->navigation->module_link('Add Member/Administrator',site_url('admincp/users/add'));
	
		$this->load->model('admincp/dataset','dataset');
		
		$this->load->model('usergroup_model');			
	    $usergroups = $this->usergroup_model->get_usergroups();
	    
	    $options = array();
	    foreach ($usergroups as $group) {
	    	$options[$group['id']] = $group['name'];
	    }
	    $usergroups = $options;
	    				
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%'
							),
						array(
							'name' => 'Username',
							'type' => 'text',
							'filter' => 'username',
							'sort_column' => 'user_username',
							'width' => '15%'
							),
						array(
							'name' => 'Email',
							'type' => 'text',
							'filter' => 'email',
							'sort_column' => 'user_email',
							'width' => '15%'
							),
						array(
							'name' => 'Full Name',
							'type' => 'text',
							'filter' => 'name',
							'sort_column' => 'user_last_name',
							'width' => '15%'
							),
						array(
							'name' => 'Usergroup(s)',
							'type' => 'select',
							'filter' => 'group',
							'options' => $options,
							'width' => '10%'
							),
						array(
							'name' => 'Status',
							'width' => '10%',
							'type' => 'select',
							'filter' => 'suspended',
							'options' => array('0' => 'Active', '1' => 'Suspended')
							),
						array(
							'name' => 'Last Login',
							'sort_column' => 'user_last_login',
							'width' => '15%'
							),
						array(
							'name' => '',
							'width' => '10%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('user_model','get_users');
		$this->dataset->base_url(site_url('admincp/users/index'));
		
		// total rows
		$total_rows = $this->db->where('user_deleted','0')->get('users')->num_rows(); 
		$this->dataset->total_rows($total_rows);
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Suspend','admincp/users/suspend');
		$this->dataset->action('Unsuspend','admincp/users/unsuspend');
		$this->dataset->action('Delete','admincp/users/delete');
		
		$data = array('usergroups' => $usergroups);
		
		$this->load->view('users.php', $data);
	}
	
	function delete ($users, $return_url) {
		$this->load->library('asciihex');
		
		$users = unserialize(base64_decode($this->asciihex->HexToAscii($users)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($users as $user) {
			$this->user_model->delete_user($user);
		}
		
		$this->notices->SetNotice('Users(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function suspend ($users, $return_url) {
		$this->load->library('asciihex');
		
		$users = unserialize(base64_decode($this->asciihex->HexToAscii($users)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($users as $user) {
			$this->user_model->suspend_user($user);
		}
		
		$this->notices->SetNotice('Users(s) suspended successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function unsuspend ($users, $return_url) {
		$this->load->library('asciihex');
		
		$users = unserialize(base64_decode($this->asciihex->HexToAscii($users)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($users as $user) {
			$this->user_model->unsuspend_user($user);
		}
		
		$this->notices->SetNotice('Users(s) unsuspended successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function suspend_user ($user) {
		$this->user_model->suspend_user($user);
				
		redirect('admincp/users/profile/' . $user);
		
		return TRUE;
	}
	
	function unsuspend_user ($user) {
		$this->user_model->unsuspend_user($user);
				
		redirect('admincp/users/profile/' . $user);
		
		return TRUE;
	}
	
	function profile ($id) {	
		$user = $this->user_model->get_user($id);
		
		if (!$user) {
			die(show_error('User does not exist.'));
		}
		
		// navigation
		if ($user['suspended'] != TRUE) {
			$this->navigation->module_link('Suspend User',site_url('admincp/users/suspend_user/' . $id));
		}
		else {
			$this->navigation->module_link('Unsuspend User',site_url('admincp/users/unsuspend_user/' . $id));
		}
		
		$this->navigation->module_link('New Subscription',site_url('admincp/billing/new_subscription/' . $id));	
		
		// prep data
		$custom_fields = $this->user_model->get_custom_fields();
		$subscriptions = $this->user_model->get_subscriptions($user['id']);
		
		// prep $show_usergroups
		$this->load->model('usergroup_model');
		$usergroups = $this->usergroup_model->get_usergroups();
		
		$usergroup_options = array();
		foreach ($usergroups as $group) {
			$usergroup_options[$group['id']] = $group['name'];
		}
		$usergroups = $usergroup_options;
		
		foreach ($user['usergroups'] as $key => $group) {
			$user['usergroups'][$key] = $usergroups[$group];
		}
		
		$user['show_usergroups'] = implode(', ',$user['usergroups']);
		
		$data = array(
					'user' => $user,
					'custom_fields' => $custom_fields,
					'subscriptions' => $subscriptions,
					'usergroups' => $usergroups
			);
		
		$this->load->view('profile', $data);
	}
	
	function add () {
		$this->load->model('usergroup_model');
		
		$usergroups = $this->usergroup_model->get_usergroups();
		
		$usergroup_options = array();
		foreach ($usergroups as $group) {
			$usergroup_options[$group['id']] = $group['name'];
		}
		
		$this->load->library('admin_form');
		$this->load->model('custom_fields_model');
		
		$form = new Admin_form;
		$form->fieldset('System Information');
		$form->text('Username', 'username', '', FALSE, TRUE, FALSE, TRUE);
		$form->text('Email', 'email', '', FALSE, TRUE, 'email@example.com', TRUE);
		$form->password('Password', 'password', '', TRUE, TRUE);
		$form->password('Repeat Password', 'password2', 'Passwords must be at least 6 characters in length.', TRUE, TRUE);
		$form->fieldset('Usergroup');
		$form->dropdown('Usergroups','usergroups',$usergroup_options, array($this->usergroup_model->get_default()), TRUE, TRUE);
		$form->checkbox('Administrator','is_admin','1',FALSE);
		$form->fieldset('Profile Information');
		$form->names('Name', '', '', FALSE, TRUE);
		
		$form->custom_fields($this->custom_fields_model->get_custom_fields('1'));
	
		$data = array(
						'user' => array(),
						'usergroups' => $usergroup_options,
						'default_usergroup' => $this->usergroup_model->get_default(),
						'form' => $form->display(),
						'form_title' => 'Create Member Account',
						'action' => 'new',
						'form_action' => site_url('admincp/users/post_user/new')
					);
	
		$this->load->view('user_form.php', $data);
	}
	
	function post_user ($action, $id = FALSE) {
		if ($action == 'edit') {
			$editing = TRUE;
		}
		else {
			$editing = FALSE;
		}
	
		$validated = $this->user_model->validation($editing);
		if ($validated !== TRUE) {
			$this->notices->SetError(implode('<br />',$validated));
			$error = TRUE;
		}
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/users/add');
				return FALSE;
			}
			else {
				redirect('admincp/users/edit/' . $id);
				return FALSE;
			}	
		}
		
		if ($action == 'new') {
			$custom_fields = $this->custom_fields_model->post_to_array('1');
			
			$user_id = $this->user_model->new_user(
													$this->input->post('email'),
													$this->input->post('password'),
													$this->input->post('username'),
													$this->input->post('first_name'),
													$this->input->post('last_name'),
													$this->input->post('usergroups'),
													FALSE,
													($this->input->post('is_admin') == '1') ? TRUE : FALSE,
													$custom_fields
												);
			
			$this->notices->SetNotice('User added successfully.');
		}
		else {
			$custom_fields = $this->custom_fields_model->post_to_array('1');	
			
			$this->user_model->update_user(
											$id,
											$this->input->post('email'),
											($this->input->post('password') != '') ? $this->input->post('password') : '',
											$this->input->post('username'),
											$this->input->post('first_name'),
											$this->input->post('last_name'),
											$this->input->post('usergroups'),
											($this->input->post('is_admin') == '1') ? TRUE : FALSE,
											$custom_fields
										);
															
			$this->notices->SetNotice('User edited successfully.');
			
			$user_id = $id;
		}
		
		redirect('admincp/users/profile/' . $user_id);
		
		return TRUE;
	}
	
	function edit ($id) {
		$this->load->model('usergroup_model');
		
		$usergroups = $this->usergroup_model->get_usergroups();
		
		$usergroup_options = array();
		foreach ($usergroups as $group) {
			$usergroup_options[$group['id']] = $group['name'];
		}
		
		$user = $this->user_model->get_user($id);
		
		if (!$user) {
			die(show_error('No user found with that ID.'));
		}
		
		$this->load->library('admin_form');
		
		$form = new Admin_form;
		$form->fieldset('System Information');
		$form->text('Username', 'username', $user['username'], FALSE, TRUE, FALSE, TRUE);
		$form->text('Email', 'email', $user['email'], FALSE, TRUE, 'email@example.com', TRUE);
		$form->password('Password', 'password', '', FALSE, TRUE);
		$form->password('Repeat Password', 'password2', 'Leave blank to keep current password. Passwords must be at least 6 characters in length.', FALSE, TRUE);
		$form->fieldset('Usergroup');
		$form->dropdown('Usergroups','usergroups',$usergroup_options, $user['usergroups'], TRUE, TRUE);
		$form->checkbox('Administrator','is_admin','1',$user['is_admin']);
		$form->fieldset('Profile Information');
		$form->names('Name', $user['first_name'], $user['last_name'], FALSE, TRUE);
		$form->custom_fields($this->user_model->get_custom_fields(), $user, TRUE);
	
		$data = array(
						'user' => array(),
						'usergroups' => $usergroup_options,
						'default_usergroup' => $this->usergroup_model->get_default(),
						'form' => $form->display(),
						'form_title' => 'Edit Member Account',
						'form_action' => site_url('admincp/users/post_user/edit/' . $user['id'])
					);
	
		$this->load->view('user_form.php', $data);
	}
	
	function groups () {
		$this->navigation->module_link('Add New Member Group',site_url('admincp/users/group_add'));
		
		$this->load->model('admincp/dataset','dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%'
							),
						array(
							'name' => 'Name',
							'width' => '70%'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('usergroup_model','get_usergroups');
		$this->dataset->base_url(site_url('admincp/users/groups'));
		$this->dataset->rows_per_page(1000);
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/users/group_delete');
		
		$this->load->view('groups.php');
	}
	
	function group_add () {
		$this->load->library('admin_form');
		
		$form = new Admin_form;
		$form->fieldset('Group Information');
		$form->text('Name', 'name', '', FALSE, TRUE, FALSE, TRUE);
		
		$data = array(
						'form' => $form->display(),
						'form_title' => 'Create Member Group',
						'form_action' => site_url('admincp/users/post_group/new')
					);
	
		$this->load->view('group_form.php', $data);
	}
	
	function post_group ($action, $id = FALSE) {
		$this->load->library('form_validation');
		$this->form_validation->set_rules('name','Group Name','required|trim');
		
		if ($this->form_validation->run() == FALSE) {
			$error = TRUE;
			$this->notices->SetError('Group name is a required field.');
		}
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/users/group_add');
				return FALSE;
			}
			else {
				redirect('admincp/users/group_edit/' . $id);
				return FALSE;
			}	
		}
		
		$this->load->model('usergroup_model');
		
		if ($action == 'new') {
			$group_id = $this->usergroup_model->new_group(
													$this->input->post('name')
												);
			
			$this->notices->SetNotice('Member group added successfully.');
		}
		else {
			$this->usergroup_model->update_group(
													$id,
													$this->input->post('name')
												);
															
			$this->notices->SetNotice('Member group edited successfully.');
		}
		
		redirect('admincp/users/groups');
		
		return TRUE;
	}
	
	function group_edit ($id) {
		$this->load->library('admin_form');
		
		$this->load->model('usergroup_model');
		$group = $this->usergroup_model->get_group($id);
		
		$form = new Admin_form;
		$form->fieldset('Group Information');
		$form->text('Name', 'name', $group['name'], FALSE, TRUE, FALSE, TRUE);
		
		$data = array(
						'form' => $form->display(),
						'form_title' => 'Edit Member Group',
						'form_action' => site_url('admincp/users/post_group/edit/' . $id)
					);
	
		$this->load->view('group_form.php', $data);
	}
	
	function group_default ($id) {
		$this->load->model('usergroup_model');
		
		$this->usergroup_model->make_default($id);
		
		redirect(site_url('admincp/users/groups'));
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
	function group_delete ($groups, $return_url) {
		$this->load->library('asciihex');
		
		$groups = unserialize(base64_decode($this->asciihex->HexToAscii($groups)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		$this->load->model('usergroup_model');
		
		foreach ($groups as $group) {
			$this->usergroup_model->delete_group($group);
		}
		
		$this->notices->SetNotice('Group(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function data () {
		$this->navigation->module_link('Add Custom Field',site_url('admincp/users/data_add'));
		$this->navigation->module_link('Preview &amp; Arrange Fields',site_url('admincp/custom_fields/order/1/' . urlencode(base64_encode(site_url('admincp/users/data')))));
		
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
		
		if (in_array($this->input->post('type'),array('select','radio')) and trim($this->input->post('options')) == '') {
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
		
		if ($action == 'new') {
			$field_id = $this->user_model->new_custom_field(
																$this->input->post('name'),
																$this->input->post('type'),
																$this->input->post('options'),
																$this->input->post('default'),
																$this->input->post('width'),
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
												$this->input->post('default'),
												$this->input->post('width'),
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