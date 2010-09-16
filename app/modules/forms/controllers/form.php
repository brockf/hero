<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Forms Module
*
* Displays single form
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Form extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view ($url_path) {
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
			paywall($form, 'form');
			die();
		}
		
		// do we have passed values?
		$values = ($this->input->get('values')) ? unserialize(query_value_decode($this->input->get('values'))) : array();
		
		if (empty($values)) {
			$this->load->model('custom_fields_model');
			$fields = $this->custom_fields_model->get_custom_fields(array('group' => $form['custom_field_group_id']));
			
			foreach ($fields as $field) {
				$values[$field['name']] = '';
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
		
		// form validation
		$this->load->library('form_validation');
		$this->load->model('custom_fields_model');
		
		$rules = $this->custom_fields_model->get_validation_rules($form['custom_field_group_id']);
		
		$this->form_validation->set_rules($rules);
		
		if ($this->form_validation->run() !== TRUE) {
			$this->session->set_flashdata('validation_errors',validation_errors());
			
			$values = query_value_encode(serialize($this->custom_fields_model->post_to_array($form['custom_field_group_id'])));
		
			return redirect($form['url_path'] . '?errors=true&values=' . $values);
		}
		
		// we validated!  let's make the post
		$custom_fields = $this->custom_fields_model->post_to_array($form['custom_field_group_id']);
			
		$this->form_model->new_response($form['id'], ($this->user_model->logged_in()) ? $this->user_model->get('id') : 0, $custom_fields);
		
		redirect($form['redirect']);
	}
}