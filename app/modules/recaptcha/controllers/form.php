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
	
	function submit () {
		$form_id = $this->input->post('form_id');
		
		if (empty($form_id)) {
			die(show_error('You did not specify a "form_id" in your form post.'));
		}
		
		$this->load->model('forms/form_model');
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
		
		$recaptchaUserResponse = $this->CI->input->post('g-recaptcha-response');
		
		$this->CI->load->model('recaptcha_model');
		$recaptchaValidation = $this->recaptcha_model->recaptchaValidation($recaptchaUserResponse);
		
		if ($this->form_builder->validate_post() === FALSE || $recaptchaValidation == false) {
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