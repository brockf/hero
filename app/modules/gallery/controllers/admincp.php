<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Gallery Control Panel
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
				
		$this->admin_navigation->parent_active('publish');
	}
	
	
	function index () {		
		// get type_id from settings table
		$type_id = $this->config->item('gallery_content_type_id');
		
		$this->admin_navigation->module_link('Publish New Gallery',site_url('admincp/gallery/create/' . $type_id));
		
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
							'width' => '15%',
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
							'width' => '20%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('publish/content_model','get_contents', array('allow_future' => TRUE, 'type' => $type_id));
		$this->dataset->base_url(site_url('admincp/gallery'));
		
		// initialize the dataset
		$this->dataset->initialize(FALSE);
		
		// count total rows
		$this->load->model('publish/content_model');
		$total_rows = $this->content_model->count_content($this->dataset->get_unlimited_parameters());
		$this->dataset->total_rows($total_rows);
		$this->dataset->initialize_pagination();

		// add actions
		$this->dataset->action('Delete','admincp/gallery/delete');
		
		$this->load->view('galleries');
	}
	
	function delete ($contents, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('publish/content_model');
		
		$contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($contents as $content) {
			$this->content_model->delete_content($content);
		}
		
		$this->notices->SetNotice('Galleries deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function create () {
		$type = $this->config->item('gallery_content_type_id'); 
		
		$this->load->model('publish/content_type_model');
		$type = $this->content_type_model->get_content_type($type);
		
		$this->load->library('admin_form');
		
		$title = new Admin_form;
		$title->fieldset('Standard Page Elements');
		$title->text('Title','title','',FALSE,TRUE,FALSE,TRUE);
		$title->hidden('base_url',$type['base_url']);
		$title->text('URL Path','url_path',$type['base_url'],'If you leave this blank, it will be auto-generated from the Title above.',FALSE,'e.g., /about/contact_us',FALSE,'500px');
		
		// we will build the rest of the sidebar form with form_builder because we want to use it's cool
		// fieldtypes and better API
		$this->load->model('publish/topic_model');
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
		
		// handle custom fields
		$this->load->model('custom_fields_model');
		$custom_fieldset = new Admin_form;
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
		$custom_fieldset->fieldset('Custom Fields');
		$custom_fieldset->custom_fields($custom_fields);
		$custom_fields = $custom_fieldset->display();
		
		$data = array(
					'title' => $title,
					'standard' => $standard,
					'privileges' => $privileges,
					'custom_fields' => $custom_fields,
					'type' => $type,
					'invalid' => FALSE,
					'form_title' => 'Publish New Gallery',
					'form_action' => site_url('admincp/gallery/post/new')
				);
				
		$this->load->view('create', $data);
	}
	
	function edit ($id) {
		$this->load->model('publish/content_model');
		$content = $this->content_model->get_content($id, TRUE);
	
		$this->load->model('publish/content_type_model');
		$type = $this->content_type_model->get_content_type($content['type_id']);
		
		$this->load->library('admin_form');
		
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
		$this->load->model('publish/topic_model');
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
		
		// handle custom fields
		$this->load->model('custom_fields_model');
		$custom_fieldset = new Admin_form;
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
		$custom_fieldset->fieldset('Custom Product Data');
		$custom_fieldset->custom_fields($custom_fields, $content);
		$custom_fields = $custom_fieldset->display();
		
		$data = array(
					'title' => $title,
					'standard' => $standard,
					'privileges' => $privileges,
					'custom_fields' => $custom_fields,
					'type' => $type,
					'form_title' => 'Edit Gallery',
					'form_action' => site_url('admincp/gallery/post/edit/' . $content['id']),
					'invalid' => ($this->input->get('invalid')) ? TRUE : FALSE,
					'errors' => $this->session->flashdata('errors')
				);
				
		$this->load->view('create', $data);
	}
	
	function post ($action = 'new', $id = FALSE) {
		$this->load->model('publish/content_type_model');
		$type = $this->content_type_model->get_content_type($this->input->post('type'));
		
		$this->load->model('publish/content_model');
		
		// get values for topics/publish date if standard
		$this->load->library('custom_fields/form_builder');
		$this->form_builder->reset();
		$this->form_builder->add_field('multicheckbox')->name('topics')->label('Topics');
		$this->form_builder->add_field('datetime')->name('date')->label('Publish Date')->default_value(date('Y-m-d H:i:s'));
		
		$form_builder_data = $this->form_builder->post_to_array();
		
		$topics = unserialize($form_builder_data['topics']);
		$date = $form_builder_data['date'];
		
		$this->load->library('custom_fields/form_builder');
		$this->form_builder->reset();
		$this->form_builder->add_field('multicheckbox')->name('privileges')->label('Member Access Groups');

		$form_builder_data = $this->form_builder->post_to_array();
		
		$privileges = unserialize($form_builder_data['privileges']);
		
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
			
			// we're going to re-route this to our own controller
			$content = $this->content_model->get_content($content_id, TRUE);
			
			$this->db->update('links', array(
											'link_module' => 'gallery',
											'link_controller' => 'gallery',
											'link_method' => 'view'
										),
										array('link_id' => $content['link_id'])
									);
									
			// re-generate routes file
			$this->load->model('link_model');
			$this->link_model->gen_routes_file();									
				
			if ($error == FALSE) {										
				$this->notices->SetNotice('Gallery posted successfully.');
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
				$this->notices->SetNotice('Gallery updated successfully.');
			}
		}
		
		if ($error == TRUE) {
			// may not have $content_id if editign
			if (!empty($id)) {
				$content_id = $id;
			}
		
			$this->session->set_flashdata('errors', $validation_errors);
			redirect('admincp/gallery/edit/' . $content_id . '?invalid=TRUE');
		}
		else {
			redirect('admincp/gallery');
		}
	}
	
	function images ($id) {
		$this->admin_navigation->module_link('Manage Galleries',site_url('admincp/gallery'));
		$this->admin_navigation->module_link('Edit this Gallery',site_url('admincp/gallery/edit/' . $id));
	
		$this->load->model('publish/content_model');
		$gallery = $this->content_model->get_content($id);
		
		// get images
		$this->load->model('gallery/gallery_image_model');
		$gallery['images'] = $this->gallery_image_model->get_images($gallery['id']);
		
		// gallery
		$this->load->library('image_gallery_form');
		$image_gallery = new Image_gallery_form;
		//$gallery->label('Upload New Images');
		//$gallery->name('product_images');
		$image_gallery->show_upload_button(FALSE);
		
		$this->load->helper('format_size');
		$this->load->helper('image_thumb');
		
		$data = array(
					'form_action' => site_url('admincp/gallery/post_images/' . $gallery['id']),
					'gallery' => $gallery,
					'images' => $image_gallery->display()
					);
					
		$this->load->view('gallery', $data);			
	}
	
	function save_image_order ($content_id) {
		$this->load->model('gallery/gallery_image_model');
		
		// reset
		$this->gallery_image_model->images_reset_order($content_id);
		
		$count = 1;
		foreach ($_POST['image'] as $image_id) {
			$this->gallery_image_model->image_update_order($image_id, $count);
			$count++;
		}
	}
	
	function image_feature ($content_id, $image_id) {
		$this->load->model('gallery/gallery_image_model');	
		$this->gallery_image_model->make_feature_image($image_id);
		
		redirect('admincp/gallery/images/' . $content_id);
	}
	
	function image_delete ($content_id, $image_id) {
		$this->load->model('gallery/gallery_image_model');	
		$this->gallery_image_model->remove_image($image_id);
		
		redirect('admincp/gallery/images/' . $content_id);
	}
	
	function post_images ($content_id) {
		// deal with image uploads
		$config = array();
	    $config['upload_path'] = setting('path_writeable') . 'gallery_images/';
	    $config['allowed_types'] = 'zip|jpg|gif|png';
	    
	    // upload class may already be loaded
	    $this->load->library('upload');
	    
	    // do we already have images
	    $this->load->model('gallery_image_model');
	    $images = $this->gallery_image_model->get_images($content_id);
	    
	    if (isset($images[0]['featured']) and $images[0]['featured'] == '1') {
	    	$has_feature = TRUE;
	    }
	    else {
	    	$has_feature = FALSE;
	    }
	    
    	if (is_uploaded_file($_FILES['image']['tmp_name'])) {
    		// get extension
    		$this->load->helper('file_extension');
    		$ext = file_extension($_FILES['image']['name']);
    		
    		// set random filename
    		if ($ext == 'zip') {
    			$file_name = 'zip' . time() . '.zip';
    		}
    		else {
	    		$file_name = 'img' . time() . '.' . $ext;
	    	}
    		
    		$this->upload->initialize($config);
    	
    		if (!$this->upload->do_upload('image')) {
    			die(show_error($this->upload->display_errors()));
    		}
    		
    		// rename to filename
    		rename(setting('path_writeable') . 'gallery_images/' . $this->upload->file_name, setting('path_writeable') . 'gallery_images/' . $file_name);
    		
    		// zip archive?
    		if ($ext == 'zip') {
	    		$this->load->library('unzip');
				// only take out these files, anything else is ignored
				$this->unzip->allow(array('png', 'gif', 'jpeg', 'jpg'));
				
				$folder_name = substr_replace($file_name, '', -4, 4);
				$full_path = setting('path_writeable') . 'gallery_images/' . $folder_name;
				$this->settings_model->make_writeable_folder($full_path, TRUE);
				$this->unzip->extract(setting('path_writeable') . 'gallery_images/' . $file_name, $full_path);
				
				$this->load->helper('directory');
				$files = directory_map($full_path, 1);
				
				$count = 1;
				$time = time();
				foreach ($files as $file) {
					$ext = file_extension($file);
					
					// ignore the filler index.html file
					if ($ext != 'html') {
						$this_filename = 'img' . $time . '_' . $count . '.' . $ext;
						
						copy($full_path . '/' . $file, setting('path_writeable') . 'gallery_images/' . $this_filename);
						
						$image_id = $this->gallery_image_model->add_image($content_id, $this_filename);
						
						$count++;
					}
				}
			}
    		else {
    			// single image
	    		$image_id = $this->gallery_image_model->add_image($content_id, $file_name);
	    	}
    		
    		if ($has_feature == FALSE) {
    			$this->gallery_image_model->make_feature_image($image_id);
    			$has_feature = TRUE;
    		}
    		
    		$this->notices->SetNotice('Image(s) uploaded successfully.');
	    }	
	    else {
	    	$this->notices->SetNotice('No file uploaded.');
	    }
		
		return redirect('admincp/gallery/images/' . $content_id);
	}
}