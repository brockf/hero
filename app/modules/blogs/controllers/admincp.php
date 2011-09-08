<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Blog Control Panel
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
		$this->admin_navigation->module_link('New Blog/Archive',site_url('admincp/blogs/add'));
	
		$this->load->library('dataset');
		
		$this->load->model('publish/content_type_model');
		$types = $this->content_type_model->get_content_types(array('is_standard' => '1'));
		$type_options = array();
		if (!empty($types)) {
			foreach ($types as $type) {
				$type_options[$type['id']] = $type['name'];
			}
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
		$this->dataset->base_url(site_url('admincp/blogs'));
		
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
		
		// template form
		$form = new Admin_form;
		$setting = $this->settings_model->get_setting('front_items_count');
		
		$form->fieldset('Design');
		$this->load->helper('template_files');
		$template_files = template_files();
		$form->dropdown('Output Template', 'template', $template_files, 'blog.thtml', FALSE, TRUE, 'This template in your theme directory will be used to display this blog/archive page.');
		$form->text('Items per Page','per_page', $setting['value'], 'Automatic pagination will occur if the total number of content items is greater than this number.', TRUE, FALSE, FALSE, '70px', '', array('number'));
		
		$data = array(
					'types' => $type_options,
					'users' => $user_options,
					'topics' => $topic_options,
					'privilege_form' => $privilege_form,
					'form_title' => 'Create New Blog/Archive',
					'form_action' => site_url('admincp/blogs/post/new'),
					'form' => $form->display()
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
		$field_options['content_title'] = 'Title';
		$field_options['content_date'] = 'Date Created';
		$field_options['content_modified'] = 'Date Modified';
		$field_options['link_url_path'] = 'URL Path';
		foreach ($custom_fields as $field) {
			$field_options[$field['name']] = $field['friendly_name'];
		}
		
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
		
		$privileges->dropdown('Access Requires Membership to Group','privileges',$options,(!empty($blog['privileges'])) ? $blog['privileges'] : array(0), TRUE, FALSE, 'Select multiple member groups by holding the CTRL or CMD button and selecting multiple options.');
		
		$privilege_form = $privileges->display();
		
		// template form
		$form = new Admin_form;
		
		$form->fieldset('Design');
		$this->load->helper('template_files');
		$template_files = template_files();
		$form->dropdown('Output Template', 'template', $template_files, $blog['template'], FALSE, TRUE, 'This template in your theme directory will be used to display this blog/archive page.');
		$form->text('Items per Page','per_page', $blog['per_page'], 'Automatic pagination will occur if the total number of content items is greater than this number.', TRUE, FALSE, FALSE, '70px', '', array('number'));
		
		$data = array(
					'types' => $type_options,
					'users' => $user_options,
					'topics' => $topic_options,
					'field_options' => $field_options,
					'blog' => $blog,
					'form' => $form->display(),
					'privilege_form' => $privilege_form,
					'form_title' => 'Edit Blog/Archive',
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
										$this->input->post('sort_field'),
										$this->input->post('sort_dir'),
										($this->input->post('auto_trim') == '1') ? TRUE : FALSE,
										$this->input->post('template'),
										$this->input->post('per_page'),
										$this->input->post('privileges')
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
									$this->input->post('sort_field'),
									$this->input->post('sort_dir'),
									($this->input->post('auto_trim') == '1') ? TRUE : FALSE,
									$this->input->post('template'),
									$this->input->post('per_page'),
									$this->input->post('privileges')
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