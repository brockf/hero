<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Events Control Panel
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
		$this->navigation->module_link('New Event',site_url('admincp/events/add'));
	
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
							'width' => '55%',
							'filter' => 'title',
							'type' => 'text'
							),
						array(
							'name' => '',
							'width' => '20%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('events_model','get_events');
		$this->dataset->base_url(site_url('admincp/events'));
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/events/delete');
		
		$this->load->view('events');
	}
	
	function delete ($feeds, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('events_model');
		
		$feeds = unserialize(base64_decode($this->asciihex->HexToAscii($feeds)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($feeds as $feed) {
			$this->rss_model->delete_feed($feed);
		}
		
		$this->notices->SetNotice('Feed(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function add () {
		$this->load->helper('form');
		$this->load->library('Admin_form');	
		
		// privileges form
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
		
		// start & end dates
		$form = new Admin_form;
		
		$form->fieldset('Dates');
		$form->date('Start Date', 'start_date', date('Y-m-d'), 'If set to a future date, content will be hidden from public view until this date (unless you\'re an administrator).', FALSE, FALSE, FALSE, '85px');
		
		$form->date('End Date', 'end_date', date('Y-m-d'), 'If set to a future date, content will be hidden from public view until this date (unless you\'re an administrator).', FALSE, FALSE, FALSE, '85px');
				
		$data = array(
					'form_title' => 'Create New Event',
					'form' => $form->display(),
					'privileges' => $privileges->display(),
					'form_action' => site_url('admincp/events/post/new')
				);

		$this->load->view('event_form', $data);
	}
	
	function edit ($id) {
		$this->load->helper('form');
		$this->load->model('rss_model');
		
		$feed = $this->rss_model->get_feed($id);
	
		// get content types
		$this->load->model('publish/content_type_model');
		$types = $this->content_type_model->get_content_types(array('is_standard' => '1'));
		$type_options = array();
		foreach ($types as $type) {
			$type_options[$type['id']] = $type['name'];
		}
		
		// get users
		$users = $this->user_model->get_users(array('is_admin' => '1'));
		$user_options = array();
		$user_options[0] = 'Any Author';
		foreach ($users as $user) {
			$user_options[$user['id']] = $user['username'] . ' (' . $user['first_name'] . ' ' . $user['last_name'] . ')';
		}
		
		// get topics
		$this->load->model('publish/topic_model');
		$topics = $this->topic_model->get_tiered_topics();
		$topic_options = array();
		$topic_options[0] = 'Any Topic';
		foreach ($topics as $topic) {
			$topic_options[$topic['id']] = $topic['name'];
		}
		
		// get field options
		$type = $this->content_type_model->get_content_type($feed['type']);
		
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
		$field_options = array();
		$field_options['0'] = 'Do not include a summary for each item in the RSS feed.';
		$field_options['content_title'] = 'Title';
		$field_options['content_date'] = 'Date Created';
		$field_options['content_modified'] = 'Date Modified';
		$field_options['link_url_path'] = 'URL Path';
		foreach ($custom_fields as $field) {
			$field_options[$field['name']] = $field['friendly_name'];
		}
		
		// template
		$this->load->library('Admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Design');
		$this->load->helper('template_files');
		$template_files = template_files();
		$form->dropdown('Output Template', 'template', $template_files, $feed['template'], FALSE, TRUE, 'This template in your theme directory will be used to display this RSS feed.');
		
		$data = array(
					'types' => $type_options,
					'users' => $user_options,
					'topics' => $topic_options,
					'field_options' => $field_options,
					'feed' => $feed,
					'form' => $form->display(),
					'form_title' => 'Edit RSS Feed',
					'form_action' => site_url('admincp/rss/post/edit/' . $feed['id'])
				);
		
		$this->load->view('feed_form', $data);
	}
	
	function post ($action = 'new', $id = FALSE) {
		$this->load->model('events_model');
		
		if ($action == 'new') {
			$event_id = $this->events_model->new_event(
										$this->input->post('title'),
										$this->input->post('url_path'),
										$this->input->post('description'),
										$this->input->post('location'),
										$this->input->post('max_attendees'),
										$this->input->post('price'),
										$this->input->post('start_time'),
										$this->input->post('end_time'),
										$this->input->post('subscription')
									);
										
			$this->notices->SetNotice('Event added successfully.');
		}
		elseif ($action == 'edit') {
			$this->events_model->update_event(
									$id,
									$this->input->post('title'),
									$this->input->post('url_path'),
									$this->input->post('description'),
									$this->input->post('location'),
									$this->input->post('max_attendees'),
									$this->input->post('price'),
									$this->input->post('start_time'),
									$this->input->post('end_time'),
									$this->input->post('subscription')
								);
										
			$this->notices->SetNotice('Event edited successfully.');
		}
		
		redirect('admincp/events');
	}
	
	function get_fields ($type_id) {
		$this->load->model('custom_fields_model');
		$this->load->model('publish/content_type_model');
		$this->load->helper('array_to_json');
		
		$type = $this->content_type_model->get_content_type($type_id);
		
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
		$options = array();
		$options['0'] = 'Do not include a summary for each item in the RSS feed.';
		$options['content_title'] = 'Title';
		$options['content_date'] = 'Date Created';
		$options['content_modified'] = 'Date Modified';
		$options['link_url_path'] = 'URL Path';
		foreach ($custom_fields as $field) {
			$options[$field['name']] = $field['friendly_name'];
		}
		
		echo array_to_json($options);
	}
}