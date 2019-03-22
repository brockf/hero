<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* XML/RSS Control Panel
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
		$this->admin_navigation->module_link('New RSS Feed',site_url('admincp/rss/add'));
	
		$this->load->library('dataset');
		
		$this->load->model('publish/content_type_model');
		$types = $this->content_type_model->get_content_types(array('is_standard' => '1'));
		$type_options = array();
		foreach ($types as $type) {
			$type_options[$type['id']] = $type['name'];
		}
			
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
							'name' => 'Content Type',
							'width' => '20%',
							'filter' => 'type',
							'type' => 'select',
							'options' => $type_options
							),
						array(
							'name' => '',
							'width' => '20%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('rss_model','get_feeds');
		$this->dataset->base_url(site_url('admincp/rss'));
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/rss/delete');
		
		$this->load->view('rss_feeds');
	}
	
	function delete ($feeds, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('rss_model');
		
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
		
		// template
		$this->load->library('Admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Design');
		$this->load->helper('template_files');
		$template_files = template_files();
		$form->dropdown('Output Template', 'template', $template_files, 'rss_feed.txml', FALSE, TRUE, 'This template in your theme directory will be used to display this blog/archive page.');
		
		$data = array(
					'types' => $type_options,
					'users' => $user_options,
					'topics' => $topic_options,
					'form' => $form->display(),
					'form_title' => 'Create New RSS Feed',
					'form_action' => site_url('admincp/rss/post/new')
				);
		
		$this->load->view('feed_form', $data);
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
		$this->load->model('rss_model');
		
		if ($action == 'new') {
			$rss_id = $this->rss_model->new_feed(
										$this->input->post('type'),
										$this->input->post('title'),
										$this->input->post('url_path'),
										$this->input->post('description'),
										(!in_array('0',$this->input->post('authors'))) ? $this->input->post('authors') : FALSE,
										(!in_array('0',$this->input->post('topics'))) ? $this->input->post('topics') : FALSE,
										$this->input->post('summary_field'),
										$this->input->post('sort_field'),
										$this->input->post('sort_dir'),
										$this->input->post('template')
									);
										
			$this->notices->SetNotice('RSS Feed added successfully.');
		}
		elseif ($action == 'edit') {
			$this->rss_model->update_feed(
									$id,
									$this->input->post('type'),
									$this->input->post('title'),
									$this->input->post('url_path'),
									$this->input->post('description'),
									(!in_array('0',$this->input->post('authors'))) ? $this->input->post('authors') : FALSE,
									(!in_array('0',$this->input->post('topics'))) ? $this->input->post('topics') : FALSE,
									$this->input->post('summary_field'),
									$this->input->post('sort_field'),
									$this->input->post('sort_dir'),
									$this->input->post('template')
								);
										
			$this->notices->SetNotice('RSS Feed edited successfully.');
		}
		
		redirect('admincp/rss');
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