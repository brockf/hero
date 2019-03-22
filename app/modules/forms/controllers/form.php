<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Forms Module
*
* Displays single form
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Form extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view ($url_path) {
		// we may have a URL with "/" in it, so let's combine all the arguments into a URL string
		$args = func_get_args();
		$url_path = implode('/',$args);
		// end URL from arguments code
		
		$this->load->model('forms/form_model');
		
		$form_id = $this->form_model->get_form_id($url_path);
		
		if (empty($form_id)) {
			return show_404($url_path);
		}
		
		// get the form
		$form = $this->form_model->get_form($form_id);
		
		if (empty($form)) {
			die(show_404($url_path));
		}
		
		// do they have permissions?
		if (!$this->user_model->in_group($form['privileges'])) {
			$this->load->helper('paywall/paywall');
			if (paywall($form, 'form') !== FALSE) {
				die();
			}
		}
		
		// do we have passed values?
		$values = ($this->input->get('values')) ? unserialize(query_value_decode($this->input->get('values'))) : array();
		
		// we don't want non values, so we'll fill the $values array with empty placeholders
		if (empty($values)) {
			$this->load->model('custom_fields_model');
			$fields = $this->custom_fields_model->get_custom_fields(array('group' => $form['custom_field_group_id']));
			
			foreach ($fields as $field) {
				$values[$field['name']] = null;
			}
		}
		
		// errors
		$errors = ($this->input->get('errors') == 'true') ? $this->session->flashdata('validation_errors') : FALSE;
		
		// show content
		$this->smarty->assign($form);
		$this->smarty->assign('validation_errors',$errors);
		$this->smarty->assign('values',$values);
		return $this->smarty->display($form['template']);
	}
	
	function submit () {
		$form_id = $this->input->post('form_id');
		
		if (empty($form_id)) {
			die(show_error('You did not specify a "form_id" in your form post.'));
		}
		
		$this->load->model('form_model');
		$form = $this->form_model->get_form($form_id);
		
		if (empty($form)) {
			die(show_error('This form is invalid.'));
		}
		
		// do they have permissions?
		if (!$this->user_model->in_group($form['privileges'])) {
			die(show_error('Invalid permissions'));
		}
		
		// form validation and processing
		$this->load->library('custom_fields/form_builder');
		$this->form_builder->build_form_from_group($form['custom_field_group_id']);
		
		if ($this->form_builder->validate_post() === FALSE) {
			$this->session->set_flashdata('validation_errors',$this->form_builder->validation_errors());
			
			$values = query_value_encode(serialize($this->form_builder->post_to_array($form['custom_field_group_id'])));
		
			return redirect($form['url_path'] . '?errors=true&values=' . $values);
		}
		
		// we validated!  let's make the post
		$custom_fields = $this->form_builder->post_to_array($form['custom_field_group_id']);
			
		$this->form_model->new_response($form['id'], ($this->user_model->logged_in()) ? $this->user_model->get('id') : 0, $custom_fields);
		
		redirect($form['redirect']);
	}
}