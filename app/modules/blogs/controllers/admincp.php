<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Blog Control Panel
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
		$this->navigation->module_link('New Blog',site_url('admincp/blogs/add'));
	
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
		$this->dataset->datasource('blog_model','get_blogs');
		$this->dataset->base_url(site_url('admincp/blog'));
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/blogs/delete');
		
		$this->load->view('blogs');
	}
	
	function delete ($blogs, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('blog_model');
		
		$blogs = unserialize(base64_decode($this->asciihex->HexToAscii($blogs)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($blogs as $blog) {
			$this->blog_model->delete_blog($blog);
		}
		
		$this->notices->SetNotice('Blog(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function add () {
		define('INCLUDE_CKEDITOR','TRUE');
		
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
		
		$data = array(
					'types' => $type_options,
					'users' => $user_options,
					'topics' => $topic_options,
					'form_title' => 'Create New Blog',
					'form_action' => site_url('admincp/blogs/post/new')
				);
		
		$this->load->view('blog_form', $data);
	}
	
	function edit ($id) {
		define('INCLUDE_CKEDITOR','TRUE');
		
		$this->load->helper('form');
		$this->load->model('blog_model');
		
		$blog = $this->blog_model->get_blog($id);
	
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
		$type = $this->content_type_model->get_content_type($blog['type']);
		
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
		$field_options = array();
		foreach ($custom_fields as $field) {
			$field_options[$field['name']] = $field['friendly_name'];
		}
		
		$data = array(
					'types' => $type_options,
					'users' => $user_options,
					'topics' => $topic_options,
					'field_options' => $field_options,
					'blog' => $blog,
					'form_title' => 'Edit Blog',
					'form_action' => site_url('admincp/blogs/post/edit/' . $blog['id'])
				);
		
		$this->load->view('blog_form', $data);
	}
	
	function post ($action = 'new', $id = FALSE) {
		$this->load->model('blog_model');
		
		if ($action == 'new') {
			$blog_id = $this->blog_model->new_blog(
										$this->input->post('type'),
										$this->input->post('title'),
										$this->input->post('url_path'),
										$this->input->post('description'),
										(!in_array('0',$this->input->post('authors'))) ? $this->input->post('authors') : FALSE,
										(!in_array('0',$this->input->post('topics'))) ? $this->input->post('topics') : FALSE,
										$this->input->post('summary_field'),
										($this->input->post('auto_trim') == '1') ? TRUE : FALSE
									);
										
			$this->notices->SetNotice('Blog added successfully.');
		}
		elseif ($action == 'edit') {
			$this->blog_model->update_blog(
									$id,
									$this->input->post('type'),
									$this->input->post('title'),
									$this->input->post('url_path'),
									$this->input->post('description'),
									(!in_array('0',$this->input->post('authors'))) ? $this->input->post('authors') : FALSE,
									(!in_array('0',$this->input->post('topics'))) ? $this->input->post('topics') : FALSE,
									$this->input->post('summary_field'),
										($this->input->post('auto_trim') == '1') ? TRUE : FALSE
								);
										
			$this->notices->SetNotice('Blog edited successfully.');
		}
		
		redirect('admincp/blogs');
	}
	
	function get_fields ($type_id) {
		$this->load->model('custom_fields_model');
		$this->load->model('publish/content_type_model');
		$this->load->helper('array_to_json');
		
		$type = $this->content_type_model->get_content_type($type_id);
		
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
		$options = array();
		foreach ($custom_fields as $field) {
			$options[$field['name']] = $field['friendly_name'];
		}
		
		echo array_to_json($options);
	}
}