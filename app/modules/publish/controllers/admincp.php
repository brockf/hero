<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Content Control Panel
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
	
	function manage	($type_id) {
		$this->session->set_userdata('manage_content_type', $type_id);
		
		redirect('admincp/publish');
	}
	
	function index () {		
		// get type_id from session
		$type_id = $this->session->userdata('manage_content_type');
		
		// Make topics available to views
		$this->load->model('topic_model');
		$topics = $this->topic_model->get_topics();
		
		$topics_sort = array();
		
		if (is_array($topics))
		{
			foreach ($topics as $topic)
			{
				$topics_sort[$topic['id']] = $topic['name'];
			}
		}
		
		$data = array(
			'topics'=> $topics_sort
		);
		unset($topics);
		
		// we'll show all content if they don't pass a $type_id
		if ($type_id == FALSE) {
			$this->admin_navigation->module_link('Publish New Content',site_url('admincp/publish/create'));
			
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
								'width' => '20%',
								'filter' => 'title',
								'type' => 'text',
								'sort_column' => 'content.content_title'
								),
							array(
								'name' => 'Author',
								'width' => '15%',
								'filter' => 'author',
								'type' => 'text'
								),
							array(
								'name' => 'Topic',
								'width' => '15%',
								'type' => 'select',
								'options' => $topics_sort,
								'filter' => 'topic_id',
								'sort_column' => 'topic_maps.topic_id'
							),
							array(
								'name' => 'Date',
								'width' => '15%',
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
								'width' => '10%'
								)
						);
							
			$this->dataset->columns($columns);
			$this->dataset->datasource('content_model','get_contents', array('allow_future' => TRUE));
			$this->dataset->base_url(site_url('admincp/publish/index'));
			
			// initialize the dataset
			$this->dataset->initialize(FALSE);
			
			// count total rows
			$this->load->model('content_model');
			$total_rows = $this->content_model->count_content($this->dataset->get_unlimited_parameters());
			$this->dataset->total_rows($total_rows);
			$this->dataset->initialize_pagination();
	
			// add actions
			$this->dataset->action('Delete','admincp/publish/delete');
			
			$this->load->view('content', $data);
		}
		else {
			// they passed a type id
			$this->load->model('content_type_model');
			$type = $this->content_type_model->get_content_type($type_id);
			
			if (empty($type)) {
				die(show_error('Content type does not exist.'));
			}
			
			$this->admin_navigation->module_link('Publish New ' . $type['singular_name'],site_url('admincp/publish/create_post/' . $type_id));
			
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
									'sort_column' => 'content.content_title'
									),
								array(
									'name' => 'Author',
									'width' => '20%',
									'filter' => 'author_like',
									'type' => 'text'
									),
								array(
									'name' => 'Topic',
									'width' => '15%',
									'type' => 'select',
									'options' => $topics_sort,
									'filter' => 'topic',
									'sort_column' => 'topic_maps.topic_id'
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
				$this->dataset->initialize(FALSE);
				
				// count total rows
				$this->load->model('content_model');
				$total_rows = $this->content_model->count_content($this->dataset->get_unlimited_parameters());
				$this->dataset->total_rows($total_rows);
				$this->dataset->initialize_pagination();
		
				// add actions
				$this->dataset->action('Delete','admincp/publish/delete');
				
				$data['type'] = $type;
				
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
				$this->dataset->initialize(FALSE);
				
				// count total rows
				$this->load->model('content_model');
				$total_rows = $this->content_model->count_content($this->dataset->get_unlimited_parameters());
				$this->dataset->total_rows($total_rows);
				$this->dataset->initialize_pagination();
		
				// add actions
				$this->dataset->action('Delete','admincp/publish/delete');
				
				$data['type'] 		= $type;
				$data['columns']	= $data_columns;
				
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
			
			$title = new Admin_form;
			$title->fieldset('Standard Page Elements');
			$title->text('Title','title','',FALSE,TRUE,FALSE,TRUE);
			$title->hidden('base_url',$type['base_url']);
			$title->text('URL Path','url_path',$type['base_url'],'If you leave this blank, it will be auto-generated from the Title above.',FALSE,'e.g., /about/contact_us',FALSE,'500px');
			
			// we will build the rest of the sidebar form with form_builder because we want to use it's cool
			// fieldtypes and better API
			$this->load->model('topic_model');
			$topics = $this->topic_model->get_tiered_topics();
			
			$options = array();
			foreach ($topics as $data) {
				$options[] = array('name' => $data['name'], 'value' => $data['id']);
			}
			
			$this->load->library('custom_fields/form_builder');
			$topics = $this->form_builder->add_field('multicheckbox');
			$topics->options($options)
				   ->name('topics')
				   ->label('Topics');
			
			$date = $this->form_builder->add_field('datetime');
			$date->data('future_only',TRUE)
			     ->name('date')
				 ->label('Publish Date')
				 ->value(date('Y-m-d H:i:s'));
			
			$title = $title->display();
			$standard = $this->form_builder->output_admin();
		}
		else {
			$title = FALSE;
			$standard = FALSE;
		}
		
		if ($type['is_privileged'] == TRUE) {
			// we require a member group access privileges dropdown
			$this->load->model('users/usergroup_model');
			$groups = $this->usergroup_model->get_usergroups();
			
			$options = array();
			$options[] = array('name' => 'Public / Any Member Group', 'value' => '0');
			foreach ($groups as $group) {
				$options[] = array('name' => $group['name'], 'value' => $group['id']);
			}
			
			$this->load->library('custom_fields/form_builder');
			$this->form_builder->reset();
			$privileges = $this->form_builder->add_field('multicheckbox');
			$privileges->name('privileges')
					   ->options($options)
					   ->default_value(0)
					   ->label('Allowed Membership Groups')
					   ->help('If a group or groups is selected, this content will require the user be in this group to view it.  This enables you to 
					   charge for subscriptions and products that move the user to this group.');
			
			$privileges = $this->form_builder->output_admin();
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
			$custom_fieldset->fieldset('Custom Fields');
			$custom_fieldset->custom_fields($custom_fields);
			$custom_fields = $custom_fieldset->display();
		}

		
		$data = array(
					'title' => $title,
					'standard' => $standard,
					'privileges' => $privileges,
					'custom_fields' => $custom_fields,
					'type' => $type,
					'invalid' => FALSE,
					'form_title' => 'Publish New Content',
					'form_action' => site_url('admincp/publish/post/new')
				);
				
		$this->load->view('create_post', $data);
	}
	
	function edit ($id) {
		$this->load->model('content_model');
		$content = $this->content_model->get_content($id, TRUE);
	
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($content['type_id']);
		
		$this->load->library('admin_form');
		
		if ($type['is_standard'] == TRUE) {
			// we require Title, URL Path, and Topic fields
			
			$title = new Admin_form;
			$title->fieldset('Standard Page Elements');
			$title->text('Title','title',$content['title'],FALSE,TRUE,FALSE,TRUE);
			
			// if we are using the base_url in the current URL, chances are we want to keep it for future URL's
			if (isset($type['base_url']) and !empty($type['base_url']) and strpos($content['url_path'], $type['base_url']) === 0) {
				$title->hidden('base_url',$type['base_url']);
			}
			
			$title->text('URL Path','url_path',$content['url_path'],'If you leave this blank, it will be auto-generated from the Title above.',FALSE,'e.g., /about/contact_us',FALSE,'500px');
			
			// we will build the rest of the sidebar form with form_builder because we want to use it's cool
			// fieldtypes and better API
			$this->load->model('topic_model');
			$topics = $this->topic_model->get_tiered_topics();
			
			$options = array();
			foreach ($topics as $data) {
				$options[] = array('name' => $data['name'], 'value' => $data['id']);
			}
			
			$this->load->library('custom_fields/form_builder');
			$topics = $this->form_builder->add_field('multicheckbox');
			$topics->options($options)
				   ->name('topics')
				   ->label('Topics');
			
			$date = $this->form_builder->add_field('datetime');
			$date->data('future_only',TRUE)
				 ->name('date')
				 ->label('Publish Date');
			
			// editing, assign values
			$topics->value($content['topics']);
			$date->value($content['date']);
			
			$title = $title->display();
			$standard = $this->form_builder->output_admin();
		}
		else {
			$standard = FALSE;
			$title = FALSE;
		}
		
		if ($type['is_privileged'] == TRUE) {
			// we require a member group access privileges dropdown
			$this->load->model('users/usergroup_model');
			$groups = $this->usergroup_model->get_usergroups();
			
			$options = array();
			$options[] = array('name' => 'Public / Any Member Group', 'value' => '0');
			foreach ($groups as $group) {
				$options[] = array('name' => $group['name'], 'value' => $group['id']);
			}
			
			$this->load->library('custom_fields/form_builder');
			$this->form_builder->reset();
			$privileges = $this->form_builder->add_field('multicheckbox');
			$privileges->name('privileges')
					   ->options($options)
					   ->default_value(0)
					   ->label('Allowed Membership Groups')
					   ->help('If a group or groups is selected, this content will require the user be in this group to view it.  This enables you to 
					   charge for subscriptions and products that move the user to this group.')
					   ->value($content['privileges']);
			
			$privileges = $this->form_builder->output_admin();
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
					'title' => $title,
					'standard' => $standard,
					'privileges' => $privileges,
					'custom_fields' => $custom_fields,
					'type' => $type,
					'form_title' => 'Edit Content',
					'form_action' => site_url('admincp/publish/post/edit/' . $content['id']),
					'invalid' => ($this->input->get('invalid')) ? TRUE : FALSE,
					'errors' => $this->session->flashdata('errors')
				);
				
		$this->load->view('create_post', $data);
	}
	
	function post ($action = 'new', $id = FALSE) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($this->input->post('type'));
		
		$this->load->model('content_model');
		
		if ($type['is_standard'] == TRUE) {
			// get values for topics/publish date if standard
			$this->load->library('custom_fields/form_builder');
			$this->form_builder->reset();
			$this->form_builder->add_field('multicheckbox')->name('topics')->label('Topics');
			$this->form_builder->add_field('datetime')->name('date')->label('Publish Date')->default_value(date('Y-m-d H:i:s'));
			
			$form_builder_data = $this->form_builder->post_to_array();
			
			$topics = unserialize($form_builder_data['topics']);
			$date = $form_builder_data['date'];
		}
		else {
			$topics = array();
			$date = FALSE;
		}
		
		if ($type['is_privileged'] == TRUE) {
			$this->load->library('custom_fields/form_builder');
			$this->form_builder->reset();
			$this->form_builder->add_field('multicheckbox')->name('privileges')->label('Member Access Groups');

			$form_builder_data = $this->form_builder->post_to_array();
			
			$privileges = unserialize($form_builder_data['privileges']);
		}
		else {
			$privileges = array();
		}
		
		// gather custom field data
		$this->load->library('custom_fields/form_builder');
		$this->form_builder->build_form_from_group($type['custom_field_group_id']);
		
		// validation, though we won't kill script if it doesn't validate because we don't
		// want to lose any data
		// we'll allow the post to save, then take them to the edit screen with errors!
		if ($this->form_builder->validate_post() === FALSE) {
			$validation_errors = $this->form_builder->validation_errors();
			$this->notices->SetError($validation_errors);
			$error = TRUE;
		}
		else {
			$error = FALSE;
		}
		
		$custom_fields = $this->form_builder->post_to_array();
		
		if ($action == 'new') {
			$content_id = $this->content_model->new_content(
													$this->input->post('type'),
													$this->user_model->get('id'),
													$this->input->post('title'),
													$this->input->post('url_path'),
													$topics,
													$privileges,
													$date,
													$custom_fields
												);
				
			if ($error == FALSE) {										
				$this->notices->SetNotice('Content posted successfully.');
			}
		}
		elseif ($action == 'edit') {
			$this->content_model->update_content(
											$id,
											$this->input->post('title'),
											$this->input->post('url_path'),
											$topics,
											$privileges,
											$date,
											$custom_fields
										);
			
			if ($error == FALSE) {								
				$this->notices->SetNotice('Post updated successfully.');
			}
		}
		
		if ($error == TRUE) {
			// may not have $content_id if editign
			if (!empty($id)) {
				$content_id = $id;
			}
		
			$this->session->set_flashdata('errors', $validation_errors);
			redirect('admincp/publish/edit/' . $content_id . '?invalid=TRUE');
		}
		else {
			redirect('admincp/publish/manage/' . $this->input->post('type'));
		}
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
		$this->admin_navigation->module_link('Add Topic',site_url('admincp/publish/topic_add'));
		
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
		
		// we don't have limits or pagination here, because get_tiered_topics() doesnt have limits or offsets
		$this->dataset->rows_per_page = 10000;
		
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
		$this->admin_navigation->module_link('New Content Type',site_url('admincp/publish/type_new'));
		
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'width' => '5%'),
						array(
							'name' => 'Name',
							'width' => '25%'),
						array(
							'name' => 'System Name',
							'width' => '15%'
							),
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
		$form->dropdown('Output Template', 'template', $template_files, 'content.thtml', FALSE, TRUE, 'This template in your theme directory will be used to display content of this type.  (Ignore this field if it\'s not applicable.)');
		$form->text('Base URL Path','base_url','','(Optional) If this value is set, the URL Path box where you create the URL for each piece of content will be pre-loaded with a folder that will unify all content of this type.  For example, setting this to "articles/" will help you make all URL\'s for content of this type like "articles/my_article", "articles/my_other_article", etc.');
		
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
		$form->dropdown('Output Template', 'template', $template_files, $type['template'], FALSE, TRUE, 'This template in your theme directory will be used to display content of this type.  (Ignore this field if it\'s not applicable.)');
		$form->text('Base URL Path','base_url',$type['base_url'],'(Optional) If this value is set, the URL Path box where you create the URL for each piece of content will be pre-loaded with a folder that will unify all content of this type.  For example, setting this to "articles/" will help you make all URL\'s for content of this type like "articles/my_article", "articles/my_other_article", etc.');
		
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
																$this->input->post('template'),
																$this->input->post('base_url')
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
													$this->input->post('template'),
													$this->input->post('base_url')
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
	
		$this->admin_navigation->module_link('Add Field',site_url('admincp/publish/type_field_add/' . $type['id']));
		$this->admin_navigation->module_link('Preview &amp; Arrange Fields',site_url('admincp/custom_fields/order/' . $type['custom_field_group_id'] . '/' . urlencode(base64_encode(site_url('admincp/publish/type_fields/' . $type['id'])))));
		
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
	
		return redirect('admincp/custom_fields/add/' . $type['custom_field_group_id'] . '/publish/' . $type['system_name']);
	}
	
	function type_field_edit ($content_type_id, $id) {
		$this->load->model('content_type_model');
		$type = $this->content_type_model->get_content_type($content_type_id);
	
		$this->load->model('custom_fields_model');
		$field = $this->custom_fields_model->get_custom_field($id);
		
		return redirect('admincp/custom_fields/edit/' . $field['id'] . '/publish/' . $type['system_name']);
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