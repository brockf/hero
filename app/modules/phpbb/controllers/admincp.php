<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* phpBB3 Control Panel
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
		if (!$this->db->table_exists(setting('phpbb3_table_prefix') . 'users')) {
			return redirect('admincp/phpbb/database');
		}		
		
		$test_file = setting('phpbb3_document_root') . 'index.php';
		if (@!file_exists($test_file)) {
			return redirect('admincp/phpbb/docroot');
		}
		
		// config is good, we just have to specify usergroup relationships
		$this->load->model('users/usergroup_model');
		$usergroups = $this->usergroup_model->get_usergroups();
		
		// we'll get an array of all phpBB3 user groups
		$forum_groups = array();
	    $select = $this->db->get(setting('phpbb3_table_prefix') . 'groups');
		foreach ($select->result_array() as $group) {
	    	$forum_groups[$group['group_name']] = $group['group_id'];
	  	}
		
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Member Groups');
		$form->value_row('&nbsp;','<div style="float:left; width: 600px">Here, you can link each usergroup in phpBB3 to a ' . setting('app_name') . '
								   member group.</div>');
		
		$form->dropdown('Default Forum Usergroup','group_default',$forum_groups,setting('phpbb3_default_group'));
		
		$group_settings = (setting('phpbb3_groups') != '') ? unserialize(setting('phpbb3_groups')) : array();
		
		foreach ($usergroups as $usergroup) {
			$selected = isset($group_settings[$group['id']]) ? $group_settings[$group['id']] : FALSE;
			$form->dropdown($usergroup['name'] . ' Forum Group','group_' . $group['id'],$forum_groups,$selected);	
		}
		
		$form->value_row('&nbsp;','<div style="float:left; width: 600px"><b>Important phpBB Code Fix</b><br />
								   <p>Due to a conflict between this application and phpBB, the phpBB code needs to modified slightly.</p>
								   <p>Please find the "redirect()" function at ' . setting('phpbb3_document_root') . 'includes/functions.php 
								   and wrap "if (!defined(\'BASEPATH\')) {" and "}" around this function.</div>');
		
		$data = array(
					'form_title' => 'phpBB3: Group Configuration',
					'form_action' => site_url('admincp/phpbb/post_groups'),
					'form' => $form->display(),
					'form_button' => 'Save Group Configuration'
				);
	
		$this->load->view('generic', $data);
	}
	
	function post_groups () {
		$this->load->model('users/usergroup_model');
		$usergroups = $this->usergroup_model->get_usergroups();

		$group_settings = array();
		foreach ($usergroups as $group) {
			$group_settings[$group['id']] = $this->input->post('group_' . $group['id']);
		}
		
		$this->settings_model->update_setting('phpbb3_groups', serialize($group_settings));
		
		$this->notices->SetNotice('Configuration updated successfully.');
		return redirect('admincp/phpbb');
	}
	
	
	function post_docroot () {
		// auto-adds trailing slash
		$this->settings_model->update_setting('phpbb3_document_root', rtrim($this->input->post('document_root'),'/') . '/');
		
		$this->notices->SetNotice('Configuration saved successfully.');
		return redirect('admincp/phpbb');
	}
	
	function docroot () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Document Root');
		$form->value_row('&nbsp;','<div style="float:left; width: 600px">Please specify the exact, full path to your phpBB3 installation.</div>');
		
		$value = (setting('phpbb3_document_root') != '') ? setting('phpbb3_document_root') : FCPATH . 'forum/';
		
		$form->text('Path to phpBB3','document_root', $value, 'Include a trailing slash.');
	
		$data = array(
					'form_title' => 'phpBB3: Path Configuration',
					'form_action' => site_url('admincp/phpbb/post_docroot'),
					'form' => $form->display(),
					'form_button' => 'Save Folder Configuration'
				);
	
		$this->load->view('generic', $data);
	}
	
	function post_database () {
		$this->settings_model->update_setting('phpbb3_table_prefix', $this->input->post('table_prefix'));
		
		$this->notices->SetNotice('Configuration saved successfully.');
		return redirect('admincp/phpbb');
	}
	
	function database () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Database');
		$form->value_row('&nbsp;','<div style="float:left; width: 600px">As of right now, we can\'t locate your phpBB database tables.  Please
								   ensure that phpBB is installed <b>in the same database as ' . setting('app_name') . '</b> and then specify the
								   table prefix, below.</div>');
		
		$value = (setting('phpbb3_table_prefix') != '') ? setting('phpbb3_table_prefix') : 'phpbb_';
		
		$form->text('Table Prefix','table_prefix', $value, 'Enter the prefix for all of your phpBB tables in the database.');
	
		$data = array(
					'form_title' => 'phpBB3: Database Configuration',
					'form_action' => site_url('admincp/phpbb/post_database'),
					'form' => $form->display(),
					'form_button' => 'I have installed phpBB3 - Save Database Configuration'
				);
	
		$this->load->view('generic', $data);
	}
}