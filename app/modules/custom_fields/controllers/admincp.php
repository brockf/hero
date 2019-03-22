<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Custom Fields Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Admincp extends Admincp_Controller {
	private $protected_field_names;

	function __construct()
	{
		parent::__construct();
		
		$this->admin_navigation->parent_active('configuration');
		$this->admin_navigation->module_link('Go Back','javascript:history.go(-1)');
		
		// set protected names
		$this->protected_field_names = array('sort','sort_dir','type','limit','date');
	}
	
	/**
	* Introduction to custom fields, with individual links
	*/
	function index () {
		$this->load->view('custom_fields.php');
	}
	
	/**
	* Add a Custom Field
	*
	* @param int $custom_field_group_id The custom field group ID
	* @param string $area Example: "publish", "users", "products", "collections", "forms".  If enabled, only compatible fields are shown.
	* @param string $table If available, this MySQL table will be modified with the custom field
	*/
	function add ($custom_field_group_id, $area = FALSE, $table = FALSE) {
		// gather possible fieldtpyes
		$this->load->library('custom_fields/fieldtype');
		$fieldtypes = $this->fieldtype->get_fieldtype_options();
		
		$available_fieldtypes = array();
		asort($fieldtypes);
		foreach ($fieldtypes as $type => $name) {
			if ($this->fieldtype->$type->enabled == TRUE) {
				if (empty($area) or in_array($area, $this->fieldtype->$type->compatibility)) {
					$available_fieldtypes[$type] = $name;
				}
			}
		}
		
		$data = array(
						'fieldtypes' => $available_fieldtypes,
						'area' => $area,
						'table' => $table,
						'custom_field_group_id' => $custom_field_group_id,
						'form_title' => 'New Custom Field',
						'form_action' => site_url('admincp/custom_fields/post/new')
					);
		
		return $this->load->view('field_form', $data);
	}
	
	/**
	* Edit a Custom Field
	*
	* @param int $custom_field_id The custom field ID
	* @param string $area Example: "publish", "users", "products", "collections", "forms".  If enabled, only compatible fields are shown.
	* @param string $table If available, this MySQL table will be modified with the custom field
	*/
	function edit ($custom_field_id, $area = FALSE, $table = FALSE) {
		$this->load->model('custom_fields_model');
		$field = $this->custom_fields_model->get_custom_field($custom_field_id);
		
		if (empty($field)) {
			die(show_error('Custom field doesn\'t exist.'));
		}
	
		// gather possible fieldtpyes
		$this->load->library('custom_fields/fieldtype');
		$fieldtypes = $this->fieldtype->get_fieldtype_options();
		
		$available_fieldtypes = array();
		asort($fieldtypes);
		foreach ($fieldtypes as $type => $name) {
			if ($this->fieldtype->$type->enabled == TRUE) {
				if (empty($area) or in_array($area, $this->fieldtype->$type->compatibility)) {
					$available_fieldtypes[$type] = $name;
				}
			}
		}
		
		$data = array(
						'field' => $field,
						'fieldtypes' => $available_fieldtypes,
						'area' => $area,
						'table' => $table,
						'custom_field_group_id' => $field['group_id'],
						'form_title' => 'Edit Custom Field',
						'form_action' => site_url('admincp/custom_fields/post/edit/' . $field['id'])
					);
		
		return $this->load->view('field_form', $data);
	}
	
	function ajax_field_form () {
		$type = $this->input->post('type');
	
		$this->load->library('custom_fields/fieldtype');
		$this->fieldtype->load_type($type);
		
		// is this an edit of an existing field type?
		$custom_field_id = $this->input->post('id');
		
		echo $this->fieldtype->$type->field_form($custom_field_id);
	}
	
	function post ($action, $id = FALSE) {
		if ($this->input->post('name') == '') {
			$this->notices->SetError('Field name is a required field.');
			$error = TRUE;
		}
		
		// certain names are off limits, let's check that now
		$name = $this->input->post('name');
		$this->load->helper('clean_string');
		$system_name = clean_string($name);
		
		if (in_array($system_name, $this->protected_field_names) or in_array($name, $this->protected_field_names)) {
			$this->notices->SetError('The field name you selected, "' . $name . '", is a protected field name that cannot be used.  Please select another.');
			$error = TRUE;
		}
		
		// radio validation
		if (in_array($this->input->post('type'),array('select','radio')) and trim($this->input->post('options')) == '') {
			$this->notices->SetError('You must specify field options.');
			$error = TRUE;
		}
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/custom_fields/add/' . $this->input->post('custom_field_group_id') . '/' . $this->input->post('area') . '/' . $this->input->post('table'));
				return false;
			}
			else {
				redirect('admincp/custom_fields/edit/' . $id . '/' . $this->input->post('area') . '/' . $this->input->post('table'));
			}	
		}

		// send to fieldtype-specific form processor
		// in return, we'll receive an array which can be used the in the new_ and update_ () methods below
		
		$this->load->library('custom_fields/fieldtype');
		$type = $this->input->post('type');
		$this->fieldtype->load_type($type);
		$post = $this->fieldtype->$type->field_form_process();
		
		// complete array's missing parts, or throw errors
		if (!isset($post['name'])) { die(show_error($type . 's field_form_process() returned an empty "name" value.')); }
		if (!isset($post['type'])) { die(show_error($type . 's field_form_process() returned an empty "type" value.')); }
		if (!isset($post['options'])) { $post['options'] = FALSE; }
		if (!isset($post['default'])) { $post['default'] = FALSE; }
		if (!isset($post['width'])) { $post['width'] = FALSE; }
		if (!isset($post['help'])) { $post['help'] = FALSE; }
		if (!isset($post['required'])) { $post['required'] = FALSE; }
		if (!isset($post['validators'])) { $post['validators'] = FALSE; }
		if (!isset($post['data'])) { $post['data'] = FALSE; }
		
		$this->load->model('custom_fields_model');
		
		if ($action == 'new') {
			$field_id = $this->custom_fields_model->new_custom_field(
																$this->input->post('custom_field_group_id'),
																$post['name'],
																$post['type'],
																$post['options'],
																$post['default'],
																$post['width'],
																$post['help'],
																$post['required'],
																$post['validators'],
																$this->input->post('table'),
																$post['data']
															);
			
			$this->notices->SetNotice('Field added successfully.');
		}
		else {
			$this->custom_fields_model->update_custom_field(
												$id,
												$this->input->post('custom_field_group_id'),
												$post['name'],
												$post['type'],
												$post['options'],
												$post['default'],
												$post['width'],
												$post['help'],
												$post['required'],
												$post['validators'],
												$this->input->post('table'),
												$post['data']
											);
															
			$this->notices->SetNotice('Field edited successfully.');
		}
		
		// redirect
		$area = $this->input->post('area');
		
		if ($area == 'products') {
			redirect('admincp/store/data');
		}
		elseif ($area == 'collections') {
			redirect('admincp/store/collection_data');
		}
		elseif ($area == 'users') {
			redirect('admincp/users/data');
		}
		elseif ($area == 'publish') {
			$result = $this->db->select('content_type_id')
   						 ->from('content_types')
						 ->where('custom_field_group_id', $this->input->post('custom_field_group_id'))
						 ->get();
						 
			// build search index
			$this->load->model('publish/content_type_model');
			$this->content_type_model->build_search_index($result->row()->content_type_id);
						 
			redirect('admincp/publish/type_fields/' . $result->row()->content_type_id);
		}
		elseif ($area == 'forms') {
			$result = $this->db->select('form_id')
   						 ->from('forms')
						 ->where('custom_field_group_id', $this->input->post('custom_field_group_id'))
						 ->get();
						 
						 
			redirect('admincp/forms/fields/' . $result->row()->form_id);
		}
		elseif ($this->session->userdata('customfield_add_redirect')) {
			// added this little trick so that other modules can tap into this
			// method without being a part of this if/else flow
			redirect($this->session->userdata('customfield_add_redirect'));
		}
		
		return TRUE;
	}
	
	/**
	* Re-position a custom field group
	*
	* @param int $custom_field_group Custom field group ID
	* @param string $return_url A URL-encoded, base64_encoded, site_url() call (e.g., url_encode(base64_encode(site_url('admincp/'))))
	*
	* @return view
	*/
	function order($custom_field_group, $return_url) {
		$return_url = base64_decode(urldecode($return_url));
		
		// load model
		$this->load->model('custom_fields_model');
		
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $custom_field_group));
		
		if (empty($custom_fields)) {
			die(show_error('No custom fields in this group.'));
		}
		
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Custom Fields',array('sortable'));
		
		foreach ($custom_fields as $key => $field) {
			// get rid of required fields
			$custom_fields[$key]['required'] = '0';
			
			// no wysiwygs
			if ($field['type'] == 'wysiwyg') {
				$custom_fields[$key]['type'] = 'textarea';
			}
		}
		
		$form->custom_fields($custom_fields);
		$form = $form->display();
		
		$data = array(
						'form' => $form,
						'return_url' => urlencode(base64_encode($return_url)),
						'field_group_id' => $custom_field_group
					);
	
		$this->load->view('arrange_fields.php', $data);
	}
	
	function save ($return_url) {
		redirect(base64_decode(urldecode($return_url)));
	}
	
	/**
	* Save Order
	*/
	function save_order ($custom_field_group)
	{
		$this->load->model('custom_fields_model');
		
		// reset all custom field orders
		$this->custom_fields_model->reset_order($custom_field_group);
		
		$count = 1;
		foreach ($_POST['row'] as $field_id) {
			$this->custom_fields_model->update_order($field_id, $count);
			
			$count++;
		}
	}
}
