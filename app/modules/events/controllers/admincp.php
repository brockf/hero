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
							'width' => '25%',
							'filter' => 'title',
							'type' => 'text'
							),
						array(
							'name' => 'Location',
							'width' => '25%',
							'filter' => 'location',
							'type' => 'text'
							),
						array(
							'name' => 'Price',
							'width' => '25%',
							'filter' => 'price',
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
	
	function delete ($events, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('events_model');
		
		$events = unserialize(base64_decode($this->asciihex->HexToAscii($events)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($events as $event) {
			$this->events_model->delete_event($event);
		}
		
		$this->notices->SetNotice('Event(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function add () {
		$this->load->helper('form');
		$this->load->library('Admin_form');	
		
		// get users
		$users = $this->user_model->get_users(array('is_admin' => '1'));
		$user_options = array();
		$user_options[0] = 'Any Author';
		foreach ($users as $user) {
			$user_options[$user['id']] = $user['username'] . ' (' . $user['first_name'] . ' ' . $user['last_name'] . ')';
		}
		
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
		$dates = new Admin_form;
		
		$dates->fieldset('Dates');
		$dates->date('Start Date', 'start_date', date('Y-m-d'), 'If set to a future date, content will be hidden from public view until this date (unless you\'re an administrator).', FALSE, FALSE, FALSE, '85px');
		
		$dates->date('End Date', 'end_date', date('Y-m-d'), 'If set to a future date, content will be hidden from public view until this date (unless you\'re an administrator).', FALSE, FALSE, FALSE, '85px');
				
		$data = array(
					'form_title' => 'Create New Event',
					'users' => $user_options,
					'dates' => $dates->display(),
					'privileges' => $privileges->display(),
					'form_action' => site_url('admincp/events/post/new')
				);

		$this->load->view('event_form', $data);
	}
	
	function edit ($id) {
		$this->load->helper('form');
		$this->load->library('Admin_form');	
		$this->load->model('events_model');
		
		$event = $this->events_model->get_event($id);
	
		// get users
		$users = $this->user_model->get_users(array('is_admin' => '1'));
		$user_options = array();
		$user_options[0] = 'Any Author';
		foreach ($users as $user) {
			$user_options[$user['id']] = $user['username'] . ' (' . $user['first_name'] . ' ' . $user['last_name'] . ')';
		}
		
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
		$dates = new Admin_form;
		
		$dates->fieldset('Dates');
		$dates->date('Start Date', 'start_date', date('Y-m-d'), 'If set to a future date, content will be hidden from public view until this date (unless you\'re an administrator).', FALSE, FALSE, FALSE, '85px');
		
		$dates->date('End Date', 'end_date', date('Y-m-d'), 'If set to a future date, content will be hidden from public view until this date (unless you\'re an administrator).', FALSE, FALSE, FALSE, '85px');
				
		$data = array(
					'form_title' => 'Edit Event',
					'event' => $event,
					'dates' => $dates->display(),
					'privileges' => $privileges->display(),
					'form_action' => site_url('admincp/events/post/edit/' . $event['id'])
				);
		
		$this->load->view('event_form', $data);
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
										$this->input->post('start_date'),
										$this->input->post('end_date'),
										$this->input->post('privileges')
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
									$this->input->post('start_date'),
									$this->input->post('end_date'),
									$this->input->post('privileges')
								);
										
			$this->notices->SetNotice('Event edited successfully.');
		}
		
		redirect('admincp/events');
	}
}