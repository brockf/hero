<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Users Control Panel
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
		
		$this->admin_navigation->parent_active('members');
	}
	
	function index () {
		$this->admin_navigation->module_link('Add Member/Administrator',site_url('admincp/users/add'));
		
		$this->load->library('dataset');
		
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
							'width' => '3%',
							'filter' => 'id'
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
							'width' => '18%'
							),
						array(
							'name' => 'Full Name',
							'type' => 'text',
							'filter' => 'name',
							'sort_column' => 'user_last_name',
							'width' => '16%'
							),
						array(
							'name' => 'Usergroup(s)',
							'type' => 'select',
							'filter' => 'group',
							'options' => $options,
							'width' => '14%'
							),
						array(
							'name' => 'Status',
							'width' => '10%',
							'type' => 'select',
							'filter' => 'suspended',
							'options' => array('0' => 'Active', '1' => 'Suspended')
							),
						array(
							'name' => '',
							'width' => '19%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('user_model','get_users');
		$this->dataset->base_url(site_url('admincp/users/index'));
		
		// initialize the dataset
		$this->dataset->initialize(FALSE);
		
		// count total rows
		$total_rows = $this->user_model->count_users($this->dataset->get_unlimited_parameters());
		$this->dataset->total_rows($total_rows);
		$this->dataset->initialize_pagination();

		// add actions
		$this->dataset->action('Suspend','admincp/users/suspend');
		$this->dataset->action('Unsuspend','admincp/users/unsuspend');
		$this->dataset->action('Delete','admincp/users/delete');
		
		$data = array('usergroups' => $usergroups);
		
		$this->load->view('users.php', $data);
	}
	
	function user_actions ($action, $id) {
		$this->load->model('users/user_model');
		$user = $this->user_model->get_user($id);
		
		if ($action == 'profile') {
			redirect('admincp/users/profile/' . $user['id']);
		}
		elseif ($action == 'edit') {
			redirect('admincp/users/edit/' . $user['id']);
		}
		elseif ($action == 'subscriptions') {
			header('Location: ' . dataset_link('admincp/reports/subscriptions', array('member_name' => $user['id'])));
		}
		elseif ($action == 'add_subscription') {
			redirect('admincp/billing/new_subscription/' . $user['id']);
		}
		elseif ($action == 'suspend') {
			redirect('admincp/users/suspend_user/' . $user['id']);
		}
		elseif ($action == 'unsuspend') {
			redirect('admincp/users/unsuspend_user/' . $user['id']);
		}
		elseif ($action == 'logins') {
			header('Location: ' . dataset_link('admincp/users/logins', array('username' => $user['username'])));
		}
		elseif ($action == 'invoices') {
			header('Location: ' . dataset_link('admincp/reports/invoices', array('member_name' => $user['id'])));
		}
		elseif ($action == 'products') {
			header('Location: ' . dataset_link('admincp/reports/products', array('member_name' => $user['id'])));
		}
		elseif ($action == 'validate_email') {
			redirect('admincp/users/resend_validate_email/' . $user['id']);
		}
		
		return TRUE;
	}
	
	function resend_validate_email ($user_id) {
		if ($this->user_model->resend_validation_email($user_id)) {
			$this->notices->SetNotice('Validation email resent successfully.');
		}
		else {
			$this->notices->SetError('There was an error resending the validation email.');
		}
		
		redirect($_SERVER['HTTP_REFERER']);
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
		$this->admin_navigation->module_link('Invoices',dataset_link('admincp/reports/invoices/',array('member_name' => $user['id'])));
		
		$this->admin_navigation->module_link('Login History',dataset_link('admincp/users/logins/',array('username' => $user['username'])));
		
		$this->admin_navigation->module_link('Edit Profile',site_url('admincp/users/edit/' . $user['id']));
		
		if ($user['suspended'] != TRUE) {
			$this->admin_navigation->module_link('Suspend User',site_url('admincp/users/suspend_user/' . $id));
		}
		else {
			$this->admin_navigation->module_link('Unsuspend User',site_url('admincp/users/unsuspend_user/' . $id));
		}
		
		$this->admin_navigation->module_link('New Subscription',site_url('admincp/billing/new_subscription/' . $id));	
		
		// prep data
		$custom_fields = $this->user_model->get_custom_fields();
		
		$this->load->model('billing/subscription_model');
		$subscriptions = $this->subscription_model->get_subscriptions_friendly(array(), $user['id']);
		
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
	
	function profile_actions ($subscription_id = FALSE, $action = FALSE) {
		// take input from POST if it wasn't passed
		if ($subscription_id == FALSE) {
			$subscription_id = $this->input->post('subscription_id');
		}
		
		if ($action == FALSE) {
			$action = $this->input->post('action');
		}
		
		// get subscription
		$this->load->model('billing/subscription_model');
		$subscription = $this->subscription_model->get_subscription($subscription_id);
		
		if ($action == 'cancel') {
			if ($this->subscription_model->cancel_subscription($subscription['id'])) {
				$this->notices->SetNotice('Subscription cancelled successfully.');
			}
			else {
				$this->notices->SetError('There was an error cancelling this subscription.');
			}
			redirect('admincp/users/profile/' . $subscription['user_id']);
		}
		elseif ($action == 'update_cc') {
			redirect('admincp/billing/update_cc/' . $subscription['id']);
		}
		elseif ($action == 'change_plan') {
			redirect('admincp/billing/change_plan/' . $subscription['id']);
		}
		elseif ($action == 'change_price') {
			redirect('admincp/billing/change_price/' . $subscription['id']);
		}
		elseif ($action == 'view_all') {
			$this->load->helper('admincp/dataset_link');
			$url = dataset_link('admincp/reports/invoices', array('subscription_id' => $subscription_id));
			header('Location: ' . $url);
		}
		
		return TRUE;
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
		
		$form->custom_fields($this->custom_fields_model->get_custom_fields(array('group' => '1')));
	
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
		
		$this->load->library('custom_fields/form_builder');
		$this->form_builder->build_form_from_group(1);
		$custom_fields = $this->form_builder->post_to_array();
			
		if ($action == 'new') {
			$validation = (setting('validate_emails') == '1') ? TRUE : FALSE;
			
			$user_id = $this->user_model->new_user(
													$this->input->post('email'),
													$this->input->post('password'),
													$this->input->post('username'),
													$this->input->post('first_name'),
													$this->input->post('last_name'),
													$this->input->post('usergroups'),
													FALSE,
													($this->input->post('is_admin') == '1') ? TRUE : FALSE,
													$custom_fields,
													$validation
												);
			
			$this->notices->SetNotice('User added successfully.');
		}
		else {
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
	
	function logins () {
		$this->load->library('dataset');
		
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
							'name' => 'Member',
							'type' => 'text',
							'filter' => 'username',
							'sort_column' => 'user_username',
							'width' => '15%'
							),
						array(
							'name' => 'Member Group(s)',
							'type' => 'select',
							'filter' => 'group',
							'options' => $options,
							'width' => '15%'
							),
						array(
							'name' => 'Date',
							'type' => 'date',
							'sort_column' => 'user_login_date',
							'width' => '20%',
							'filter' => 'timestamp',
							'field_start_date' => 'start_date',
							'field_end_date' => 'end_date'
							),
						array(
							'name' => 'IP Address',
							'type' => 'text',
							'filter' => 'ip',
							'sort_column' => 'user_ip',
							'width' => '15%'
							),
						array(
							'name' => 'Browser',
							'type' => 'text',
							'filter' => 'browser',
							'options' => $options,
							'width' => '30%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('login_model','get_logins');
		$this->dataset->base_url(site_url('admincp/users/logins'));
		
		// total rows
		$total_rows = $this->db->get('user_logins')->num_rows(); 
		$this->dataset->total_rows($total_rows);
		
		// initialize the dataset
		$this->dataset->initialize();

		$data = array('usergroups' => $usergroups);
		
		$this->load->view('logins.php', $data);
	}
	
	function groups () {
		$this->admin_navigation->module_link('Add New Member Group',site_url('admincp/users/group_add'));
		
		$this->load->library('dataset');
		
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
		$this->admin_navigation->parent_active('configuration');
		
		$this->admin_navigation->module_link('Add Custom Field',site_url('admincp/users/data_add'));
		$this->admin_navigation->module_link('Preview &amp; Arrange Fields',site_url('admincp/custom_fields/order/1/' . urlencode(base64_encode(site_url('admincp/users/data')))));
		
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%'
							),
						array(
							'name' => 'Human Name',
							'width' => '19%'
							),
						array(
							'name' => 'System Name',
							'width' => '15%'
							),
						array(
							'name' => 'Type',
							'type' => 'text',
							'width' => '10%'
							),
						array(
							'name' => 'Billing Address Field?',
							'width' => '15%'
							),
						array(
							'name' => 'Admin Only?',
							'width' => '11%'
							),
						array(
							'name' => 'Registration Form?',
							'width' => '15%'
							),
						array(
							'name' => '',
							'width' => '15%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('user_model','get_custom_fields');
		$this->dataset->base_url(site_url('admincp/users/data'));
		$this->dataset->rows_per_page(1000);
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/users/data_delete');
		
		// we may not have all of the fields in the `user_fields` table, because they are created in the
		// custom_fields controller and not here
		if (!empty($this->dataset->data)) {
			foreach ($this->dataset->data as $field) {
				if (!isset($field['id'])) {
					$this->user_model->update_custom_field($field['custom_field_id'], '', FALSE, FALSE);
				}
			}
		}
		
		$this->load->view('data.php');
	}
	
	function data_update () {
		$this->user_model->update_custom_field(
											$this->input->post('custom_field_id'),
											$this->input->post('billing_equiv'),
											($this->input->post('admin_only')) ? TRUE : FALSE,
											($this->input->post('registration_form')) ? TRUE : FALSE
										);
										
		echo 'SUCCESS';
	}
	
	function data_add () {
		return redirect('admincp/custom_fields/add/1/users/users');
	}
	
	function data_edit ($id) {
		return redirect('admincp/custom_fields/edit/' . $id . '/users/users');
	}
	
	/**
	* Delete Custom Fields
	*
	* Delete user custom fields as passed from the dataset
	*
	* @param string Hex'd, base64_encoded, serialized array of user_field ID's
	* @param string Return URL for Dataset
	*
	* @return bool Redirects to dataset
	*/
	function data_delete ($fields, $return_url) {
		$this->admin_navigation->parent_active('configuration');
		
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