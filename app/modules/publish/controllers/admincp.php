<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Content Control Panel
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
	
	function manage	($type_id) {
		$this->session->set_userdata('manage_content_type', $type_id);
		
		redirect('admincp/publish');
	}
	
	function index () {		
		// get type_id from session
		$type_id = $this->session->userdata('manage_content_type');
		
		// we'll show all content if they don't pass a $type_id
		if ($type_id == FALSE) {
			$this->navigation->module_link('Publish New Content',site_url('admincp/publish/create'));
			
			$this->load->model('content_type_model');
			$types = $this->content_type_model->get_content_types();
			
			$content_types = array();
			if (!empty($types)) {
				foreach ($types as $type) {
					$content_types[$type['id']] = $type['name'];
				}
			}
			
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
								'type' => 'text',
								'sort_column' => 'content.title'
								),
							array(
								'name' => 'Author',
								'width' => '15%',
								'filter' => 'author',
								'type' => 'text'
								),
							array(
								'name' => 'Date',
								'width' => '20%',
								'sort_column' => 'content.content_date',
								'type' => 'date',
								'filter' => 'date',
								'field_start_date' => 'start_date',
								'field_end_date' => 'end_date'
								),
							array(
								'name' => 'Type',
								'width' => '10%',
								'type' => 'select',
								'options' => $content_types,
								'filter' => 'type',
								'sort_column' => 'content_type.content_type_friendly_name'
								),
							array(
								'name' => 'Hits',
								'width' => '10%',
								'sort_column' => 'content.content_hits'
								),
							array(
								'name' => '',
								'width' => '15%'
								)
						);
							
			$this->dataset->columns($columns);
			$this->dataset->datasource('content_model','get_contents', array('allow_future' => TRUE));
			$this->dataset->base_url(site_url('admincp/publish'));
			
			// initialize the dataset
			$this->dataset->initialize();
	
			// add actions
			$this->dataset->action('Delete','admincp/publish/delete');
			
			$this->load->view('content');
		}
		else {
			// they passed a type id
			$this->load->model('content_type_model');
			$type = $this->content_type_model->get_content_type($type_id);
			
			if (empty($type)) {
				die(show_error('Content type does not exist.'));
			}
			
			$this->navigation->module_link('Publish New ' . $type['singular_name'],site_url('admincp/publish/create_post/' . $type_id));
			
			if ($type['is_standard'] == TRUE) {
				// standard content, we'll display it as such
				
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
									'width' => '30%',
									'filter' => 'title',
									'type' => 'text',
									'sort_column' => 'content.title'
									),
								array(
									'name' => 'Author',
									'width' => '20%',
									'filter' => 'author_like',
									'type' => 'text'
									),
								array(
									'name' => 'Date',
									'width' => '20%',
									'sort_column' => 'content.content_date',
									'type' => 'date',
									'filter' => 'date',
									'field_start_date' => 'start_date',
									'field_end_date' => 'end_date'
									),
								array(
									'name' => 'Hits',
									'width' => '10%',
									'sort_column' => 'content.content_hits'
									),
								array(
									'name' => '',
									'width' => '15%'
									)
							);
								
				$this->dataset->columns($columns);
				$this->dataset->datasource('content_model','get_contents', array('allow_future' => TRUE, 'type' => $type['id']));
				$this->dataset->base_url(site_url('admincp/publish'));
				
				// initialize the dataset
				$this->dataset->initialize();
		
				// add actions
				$this->dataset->action('Delete','admincp/publish/delete');
				
				$data = array(
							'type' => $type
						);
				
				$this->load->view('content_standard', $data);
			}
			else {
				// content type is non-standard, so we need to generate a table on the fly
				
				// get the custom fields
				$this->load->model('custom_fields_model');
				$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
				
				$columns = array();
				
				// we need to track dynamic columns so that, in the view, we know what to display in the table cells
				$data_columns = array();
				
				// add ID column
				$columns[] = array(
								'name' => 'ID #',
								'type' => 'id',
								'sort_column' => 'id',
								'width' => '5%',
								'filter' => 'text'
								);
				
				$count = 1;
				foreach ($custom_fields as $field) {
					// max 3 fields
					if ($count > 3) {
						break;
					}
					
					// only text, radio, and dropdown fields are eligible here
					if (in_array($field['type'], array('text', 'radio', 'dropdown'))) {
						$columns[] = array(
											'name' => $field['friendly_name'],
											'width' => '13%',
											'sort_column' => $type['system_name'] . '.' . $field['name']
										);
										
						$data_columns[] = $field['name'];
						
						$count++;
					}
				}
				
				$columns[] = array(
									'name' => 'Date',
									'width' => '20%',
									'sort_column' => 'content.content_date',
									'type' => 'date',
									'filter' => 'date',
									'field_start_date' => 'start_date',
									'field_end_date' => 'end_date'
									);
									
				$columns[] = array(
								'name' => 'Hits',
								'width' => '10%',
								'sort_column' => 'content.content_hits'
									);
				
				$columns[] = array(
								'name' => '',
								'width' => '15%'
							);
							
				$this->load->library('dataset');
								
				$this->dataset->columns($columns);
				$this->dataset->datasource('content_model','get_contents', array('allow_future' => TRUE, 'type' => $type['id']));
				$this->dataset->base_url(site_url('admincp/publish'));
				
				// initialize the dataset
				$this->dataset->initialize();
		
				// add actions
				$this->dataset->action('Delete','admincp/publish/delete');
				
				$data = array(
							'type' => $type,
							'columns' => $data_columns
						);
				
				$this->load->view('content_non_standard', $data);
			}
		}	
	}
	
	function delete ($contents, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('content_model');
		
		$contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($contents as $content) {
			$this->content_model->delete_content($content);
		}
		
		$this->notices->SetNotice('Post(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function create () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Publish New Content');
		
		$this->load->model('content_type_model');
		
		$types = $this->content_type_model->get_content_types();
		
		// are there any content types?
		if (empty($types)) {
			die(show_error('You need to create at least one content "type" before publishing content.  <a href="' . site_url('admincp/publish/type_new') . '">Click here to add your first content type</a>.'));
		}
		
		// if there's only one content type, this step is redundant
		if (count($types) == 1) {
			redirect('admincp/publish/create_post/' . $types[0]['id']);
			return TRUE;
		}
		
		$options = array();
		$options[0] = '';
		foreach ($types as $type) {
			$options[$type['id']] = $type['name'];
		}
		
		$form->dropdown('Type','type',$options, 0);
		
		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/publish/create_post'),
					'form_title' => 'Select content type'
				);
				
		$this->load->view('create_type', $data);
	}
	
	function create_post ($type_id = FALSE) {
		$type = ($type_id == FALSE) ? $this->input->get_post('type') : $type_id; 
		
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($type);
		
		if (empty($type)) {
			die(show_error('This type does not exist.'));
		}
		
		$this->load->library('admin_form');
		
		if ($type['is_standard'] == TRUE) {
			// we require Title, URL Path, and Topic fields
			
			$standard = new Admin_form;
			$standard->fieldset('Standard Page Elements');
			$standard->text('Title','title','',FALSE,TRUE,FALSE,TRUE);
			$standard->text('URL Path','url_path','','If you leave this blank, it will be auto-generated from the Title above.',FALSE,'e.g., /about/contact_us',FALSE,'500px');
			
			$this->load->model('topic_model');
			$topics = $this->topic_model->get_tiered_topics();
			
			$options = array();
			$options[0] = 'No topics';
			foreach ($topics as $data) {
				$options[$data['id']] = $data['name'];
			}
			
			$standard->dropdown('Topic(s)','topics[]',$options, array(), TRUE, FALSE, 'Select multiple topics by holding the CTRL or CMD button and selecting multiple options.');
			
			$standard->date('Publish Date', 'date', date('Y-m-d'), 'If set to a future date, content will be hidden from public view until this date (unless you\'re an administrator).', FALSE, FALSE, FALSE, '85px');
			
			$standard = $standard->display();
		}
		else {
			$standard = FALSE;
		}
		
		if ($type['is_privileged'] == TRUE) {
			// we require a member group access privileges dropdown
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
			
			$privileges = $privileges->display();
		}
		else {
			$privileges = FALSE;
		}
		
		// handle custom fields
		$this->load->model('custom_fields_model');
		$custom_fieldset = new Admin_form;
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
		if (empty($custom_fields)) {
			$custom_fields = FALSE;
		}
		else {
			$custom_fieldset->fieldset('Custom Product Data');
			$custom_fieldset->custom_fields($custom_fields);
			$custom_fields = $custom_fieldset->display();
		}

		
		$data = array(
					'standard' => $standard,
					'privileges' => $privileges,
					'custom_fields' => $custom_fields,
					'type' => $type,
					'form_title' => 'Publish New Content',
					'form_action' => site_url('admincp/publish/post/new')
				);
				
		$this->load->view('create_post', $data);
	}
	
	function edit ($id) {
		$this->load->model('content_model');
		$content = $this->content_model->get_content($id);
	
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($content['type_id']);
		
		$this->load->library('admin_form');
		
		if ($type['is_standard'] == TRUE) {
			// we require Title, URL Path, and Topic fields
			
			$standard = new Admin_form;
			$standard->fieldset('Standard Page Elements');
			$standard->text('Title','title',$content['title'],FALSE,TRUE,FALSE,TRUE);
			$standard->text('URL Path','url_path',$content['url_path'],'If you leave this blank, it will be auto-generated from the Title above.',FALSE,'e.g., /about/contact_us',FALSE,'500px');
			
			$this->load->model('topic_model');
			$topics = $this->topic_model->get_tiered_topics();
			
			$options = array();
			$options[0] = 'No topics';
			foreach ($topics as $data) {
				$options[$data['id']] = $data['name'];
			}
			
			$standard->dropdown('Topic(s)','topics[]',$options, ($content['topics'] == FALSE) ? array() : $content['topics'], TRUE, FALSE, 'Select multiple topics by holding the CTRL or CMD button and selecting multiple options.');
			
			$standard->date('Publish Date', 'date', date('Y-m-d', strtotime($content['date'])), 'If set to a future date, content will be hidden from public view until this date (unless you\'re an administrator).', FALSE, FALSE, FALSE, '85px');
			
			$standard = $standard->display();
		}
		else {
			$standard = FALSE;
		}
		
		if ($type['is_privileged'] == TRUE) {
			// we require a member group access privileges dropdown
			$this->load->model('users/usergroup_model');
			$groups = $this->usergroup_model->get_usergroups();
			
			$privileges = new Admin_form;
			$privileges->fieldset('Member Group Access');
			
			$options = array();
			$options[0] = 'Public / Any Member Group';
			foreach ($groups as $group) {
				$options[$group['id']] = $group['name'];
			}
			
			$privileges->dropdown('Access Requires Membership to Group','privileges',$options,($content['privileges'] == FALSE) ? array(0) : $content['privileges'], TRUE, FALSE, 'Select multiple member groups by holding the CTRL or CMD button and selecting multiple options.');
			
			$privileges = $privileges->display();
		}
		else {
			$privileges = FALSE;
		}
		
		// handle custom fields
		$this->load->model('custom_fields_model');
		$custom_fieldset = new Admin_form;
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
		if (empty($custom_fields)) {
			$custom_fields = FALSE;
		}
		else {
			$custom_fieldset->fieldset('Custom Product Data');
			$custom_fieldset->custom_fields($custom_fields, $content);
			$custom_fields = $custom_fieldset->display();
		}

		
		$data = array(
					'standard' => $standard,
					'privileges' => $privileges,
					'custom_fields' => $custom_fields,
					'type' => $type,
					'form_title' => 'Edit Content',
					'form_action' => site_url('admincp/publish/post/edit/' . $content['id'])
				);
				
		$this->load->view('create_post', $data);
	}
	
	function post ($action = 'new', $id = FALSE) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($this->input->post('type'));
		
		$this->load->model('content_model');
		$this->load->model('custom_fields_model');
		$custom_fields = $this->custom_fields_model->post_to_array($type['custom_field_group_id']);
		
		if ($action == 'new') {
			$content_id = $this->content_model->new_content(
													$this->input->post('type'),
													$this->user_model->get('id'),
													$this->input->post('title'),
													$this->input->post('url_path'),
													$this->input->post('topics'),
													$this->input->post('privileges'),
													$this->input->post('date'),
													$custom_fields
												);
													
			$this->notices->SetNotice('Content posted successfully.');
		}
		elseif ($action == 'edit') {
			$this->content_model->update_content(
											$id,
											$this->input->post('title'),
											$this->input->post('url_path'),
											$this->input->post('topics'),
											$this->input->post('privileges'),
											$this->input->post('date'),
											$custom_fields
										);
											
			$this->notices->SetNotice('Post updated successfully.');
		}
		
		redirect('admincp/publish/manage/' . $this->input->post('type'));
	}
	
	function topic_add () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Topic');
		
		$this->load->model('topic_model');
		$topics = $this->topic_model->get_tiered_topics();
		
		$options = array();
		$options[0] = 'No parent';
		foreach ($topics as $data) {
			$options[$data['id']] = $data['name'];
		}
		
		$form->dropdown('Parent','parent',$options,'0',FALSE,FALSE,'If a parent is selected, this topic will act as a sub-topic of its parent.',TRUE);
		$form->text('Topic Name','name','',FALSE,TRUE,'e.g., Entertainment &amp; Leisure',TRUE);
		$form->textarea('Description','description','',FALSE,FALSE,'basic',TRUE);
		
		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/publish/post_topic/new'),
					'form_title' => 'New Topic'
					);
					
		$this->load->view('topic_form',$data);
	}
	
	function topic_edit ($id) {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Edit Topic');
		
		$this->load->model('topic_model');
		$topics = $this->topic_model->get_tiered_topics();
		
		$topic = $this->topic_model->get_topic($id);
		
		$options = array();
		$options[0] = 'No parent';
		foreach ($topics as $data) {
			$options[$data['id']] = $data['name'];
		}
		
		$form->dropdown('Parent','parent',$options,$topic['parent'],FALSE,FALSE,'If a parent is selected, this topic will act as a sub-topic of its parent.',TRUE);
		$form->text('Topic Name','name',$topic['name'],FALSE,TRUE,'e.g., Entertainment &amp; Leisure',TRUE);
		$form->textarea('Description','description',$topic['description'],FALSE,FALSE,'basic',TRUE);
		
		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/publish/post_topic/edit/' . $topic['id']),
					'form_title' => 'Edit Topic'
					);
					
		$this->load->view('topic_form',$data);
	}
	
	function post_topic ($action, $id = FALSE) {
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('name','Name','trim|required');
		$this->form_validation->set_rules('parent','Parent','is_natural');
		
		if ($this->form_validation->run() === FALSE) {
			$errors = rtrim(validation_errors('','||'),'|');
			$errors = explode('||',str_replace('<p>','',$errors));
			
			$this->notices->SetError($errors);
			$error = TRUE;
		}
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/publish/topic_add');
				return FALSE;
			}
			else {
				redirect('admincp/publish/topic_edit/' . $id);
			}	
		}
		
		$this->load->model('topic_model');
		
		if ($action == 'new') {
			$topic_id = $this->topic_model->new_topic(
													$this->input->post('name'),
													$this->input->post('description'),
													$this->input->post('parent')
												);
													
			$this->notices->SetNotice('Topic added successfully.');
		}
		elseif ($action == 'edit') {
			$this->topic_model->update_topic(
											$id,
											$this->input->post('name'),
											$this->input->post('description'),
											$this->input->post('parent')
										);
											
			$this->notices->SetNotice('Topic updated successfully.');
		}
		
		redirect('admincp/publish/topics');
	}
	
	function topics () {
		$this->navigation->module_link('Add Topic',site_url('admincp/publish/topic_add'));
		
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%'
							),
						array(
							'name' => 'Name',
							'width' => '70%',
							'filter' => 'name',
							'type' => 'text'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('topic_model','get_tiered_topics');
		$this->dataset->base_url(site_url('admincp/publish/topics'));
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/publish/topics_delete');
		
		$this->load->view('topics');
	}
	
	function topics_delete ($topics, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('topic_model');
		
		$topics = unserialize(base64_decode($this->asciihex->HexToAscii($topics)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($topics as $topic) {
			$this->topic_model->delete_topic($topic);
		}
		
		$this->notices->SetNotice('Topic(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function types () {
		$this->navigation->module_link('New Content Type',site_url('admincp/publish/type_new'));
		
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'width' => '5%'),
						array(
							'name' => 'Name',
							'width' => '40%'),
						array(
							'name' => 'Standard Content',
							'width' => '15%',
							),
						array(
							'name' => 'Member Privileges',
							'width' => '15%'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);
		
		$this->dataset->columns($columns);
		$this->dataset->datasource('content_type_model','get_content_types', array('is_module','0'));
		$this->dataset->base_url(site_url('admincp/publish/types'));
		$this->dataset->rows_per_page(1000);

		// total rows
		$total_rows = $this->db->get('content_types')->num_rows(); 
		$this->dataset->total_rows($total_rows);

		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/publish/types_delete');
		
		$this->load->view('content_types');
	}
	
	function types_delete ($types, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('content_type_model');
		
		$types = unserialize(base64_decode($this->asciihex->HexToAscii($types)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($types as $type) {
			$this->content_type_model->delete_content_type($type);
		}
		
		$this->notices->SetNotice('Content type(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function type_new () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('New Content Type');
		$form->text('Name', 'name', '', 'Enter the name for this type of content.', TRUE, 'e.g., News Articles', TRUE);
		$form->fieldset('Options');
		$form->checkbox('Standard page fields?', 'is_standard', '1', TRUE, 'If checked, each content item will have the following fields: "Title", "URL Path", and "Topic".  These are standard items which allow ' . setting('app_name') . ' to display this content as an individual web page, include in blog/topic listings, etc.');
		$form->checkbox('Restrict to certain member groups?', 'is_privileged', '1', TRUE, 'If checked, you will be able to specify the member group(s) that have permissions to see this content (or make it public).');
		
		$form->fieldset('Design');
		$this->load->helper('template_files');
		$template_files = template_files();
		$form->dropdown('Output Template', 'template', $template_files, 'content.thtml', FALSE, TRUE, 'This template in your theme directory will be used to display content of this type.');
		
		$data = array(
					'form' => $form->display(),
					'form_title' => 'Create New Content Type',
					'form_action' => site_url('admincp/publish/post_type/new'),
					'action' => 'new'
					);
		
		$this->load->view('type_form.php',$data);
	}
	
	function type_edit ($id) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($id);
		
		if (empty($type)) {
			die(show_error('No content type exists by that ID.'));
		}
	
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('New Content Type');
		$form->text('Name', 'name', $type['name'], 'Enter the name for this type of content.', TRUE, 'e.g., News Articles', TRUE);
		$form->fieldset('Options');
		$form->checkbox('Standard page fields?', 'is_standard', '1', $type['is_standard'], 'If checked, each content item will have the following fields: "Title", "URL Path", and "Topic".  These are standard items which allow ' . setting('app_name') . ' to display this content as an individual web page, include in blog/topic listings, etc.');
		$form->checkbox('Restrict to certain member groups?', 'is_privileged', '1', $type['is_privileged'], 'If checked, you will be able to specify the member group(s) that have permissions to see this content (or make it public).');
		
		$form->fieldset('Design');
		$this->load->helper('template_files');
		$template_files = template_files();
		$form->dropdown('Output Template', 'template', $template_files, $type['template'], FALSE, TRUE, 'This template in your theme directory will be used to display content of this type.');
		
		$data = array(
					'form' => $form->display(),
					'form_title' => 'Edit Content Type',
					'form_action' => site_url('admincp/publish/post_type/edit/' . $type['id']),
					'action' => 'edit'
					);
		
		$this->load->view('type_form.php',$data);
	}
	
	function post_type ($action = 'new', $id = false) {		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('name','Name','required|trim');
		$this->form_validation->set_rules('template','Template','required');
		
		if ($this->form_validation->run() === FALSE) {
			$this->notices->SetError('You must include a content type name.');
			$error = TRUE;
		}	
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/publish/type_new');
				return FALSE;
			}
			else {
				redirect('admincp/publish/type_edit/' . $id);
				return FALSE;
			}	
		}
		
		$this->load->model('content_type_model');
		
		if ($action == 'new') {
			$type_id = $this->content_type_model->new_content_type(
																$this->input->post('name'),
																($this->input->post('is_standard') == '1') ? TRUE : FALSE,
																($this->input->post('is_privileged') == '1') ? TRUE : FALSE,
																FALSE,
																$this->input->post('template')
															);
															
			$this->notices->SetNotice('Content type added successfully.');
			
			redirect('admincp/publish/type_fields/' . $type_id);
		}
		else {
			$this->content_type_model->update_content_type(
													$id,
													$this->input->post('name'),
													($this->input->post('is_standard') == '1') ? TRUE : FALSE,
													($this->input->post('is_privileged') == '1') ? TRUE : FALSE,
													$this->input->post('template')
												);
												
			$this->notices->SetNotice('Content type updated successfully.');
			
			redirect('admincp/publish/types');
		}
		
		return TRUE;
	}
	
	function type_fields ($id) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($id);
		
		if (empty($type)) {
			die(show_error('No content type exists with this ID.'));
		}
	
		$this->navigation->module_link('Add Field',site_url('admincp/publish/type_field_add/' . $type['id']));
		$this->navigation->module_link('Preview &amp; Arrange Fields',site_url('admincp/custom_fields/order/' . $type['custom_field_group_id'] . '/' . urlencode(base64_encode(site_url('admincp/publish/type_fields/' . $type['id'])))));
		
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
		$this->dataset->datasource('custom_fields_model','get_custom_fields', array('group' => $type['custom_field_group_id']));
		$this->dataset->base_url(site_url('admincp/publish/type_fields/' . $type['id']));
		$this->dataset->rows_per_page(1000);
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/publish/type_fields_delete/' . $type['id']);
		
		$data = array(
				'type' => $type
		);
		
		$this->load->view('type_fields.php', $data);
	}
	
	function type_field_add ($id) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($id);
	
		$data = array(
						'field' => array(),
						'type' => $type,
						'form_title' => 'New Field',
						'form_action' => site_url('admincp/publish/post_type_field/new')
					);
	
		$this->load->view('type_field_form.php', $data);
	}
	
	function post_type_field ($action, $id = FALSE) {
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
				redirect('admincp/publish/type_field_add/' . $this->input->post('content_type_id'));
				return false;
			}
			else {
				redirect('admincp/publish/type_field_edit/' . $this->input->post('content_type_id') . '/' . $id);
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
		$this->load->model('content_type_model');
		
		$type = $this->content_type_model->get_content_type($this->input->post('content_type_id'));
		
		if ($action == 'new') {
			$field_id = $this->custom_fields_model->new_custom_field(
																$type['custom_field_group_id'],
																$this->input->post('name'),
																$this->input->post('type'),
																$this->input->post('options'),
																$this->input->post('default'),
																$this->input->post('width'),
																$this->input->post('help'),
																$required,
																$validators,
																$type['system_name']
															);
															
			$this->content_type_model->build_search_index($this->input->post('content_type_id'));
			
			$this->notices->SetNotice('Field added successfully.');
		}
		else {
			$this->custom_fields_model->update_custom_field(
												$id,
												$type['custom_field_group_id'],
												$this->input->post('name'),
												$this->input->post('type'),
												$this->input->post('options'),
												$this->input->post('default'),
												$this->input->post('width'),
												$this->input->post('help'),
												$required,
												$validators,
												$type['system_name']
											);
											
			$this->content_type_model->build_search_index($this->input->post('content_type_id'));
															
			$this->notices->SetNotice('Field edited successfully.');
		}
		
		redirect('admincp/publish/type_fields/' . $type['id']);
		
		return TRUE;
	}
	
	function type_field_edit ($content_type_id, $id) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($content_type_id);
	
		$this->load->model('custom_fields_model');
		$field = $this->custom_fields_model->get_custom_field($id);
		
		$data = array(
						'field' => $field,
						'type' => $type,
						'form_title' => 'Edit Field',
						'form_action' => site_url('admincp/publish/post_type_field/edit/' . $field['id'])
					);
	
		$this->load->view('type_field_form.php', $data);
	}
	
	function type_fields_delete ($content_type_id, $fields, $return_url) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($content_type_id);
	
		$this->load->library('asciihex');
		$this->load->model('custom_fields_model');
		
		$fields = unserialize(base64_decode($this->asciihex->HexToAscii($fields)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($fields as $field) {
			$this->custom_fields_model->delete_custom_field($field, $type['system_name']);
		}
		
		$this->content_type_model->build_search_index($type['id']);
		
		$this->notices->SetNotice('Field(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
}