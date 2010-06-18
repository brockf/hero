<?php

/**
* User Model 
*
* Contains all the methods used to create, update, and delete users.
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class User_model extends CI_Model
{
	var $CI;
	
	function __construct()
	{
		parent::CI_Model();
		
		$this->CI =& get_instance();
	}
	
	function new_user($email, $password, $username, $first_name, $last_name, $groups = FALSE, $affiliate = FALSE, $is_admin = FALSE) {
		if (empty($groups)) {
			$this->CI->load->model('users/usergroup_model');
			
			$group = $this->CI->usergroup_model->get_default();
			
			$groups = array($group);
		}
		
		$insert_fields = array(
								'user_is_admin' => ($is_admin == TRUE) ? '1' : '0',
								'user_groups' => implode('|',$groups),
								'user_first_name' => $first_name,
								'user_last_name' => $last_name,
								'user_username' => $username,
								'user_email' => $email,
								'user_password' => $password,
								'user_referrer' => ($affiliate != FALSE) ? $affiliate : '0',
								'user_signup_date' => date('Y-m-d H:i:s'),
								'user_last_login' => '0000-00-00 00:00:00',
								'user_suspended' => '0',
								'user_deleted' => '0'
							);
												
		$this->db->insert('users',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	function new_custom_field ($name, $type, $options, $help, $billing_equiv = '', $required = FALSE, $validators = array()) {
		$this->load->model('custom_fields_model');
		
		// create custom field to user group
		$custom_field_id = $this->custom_fields_model->new_custom_field('1', $name, $type, $options, $help, $required, $validators, 'users');
		
		$insert_fields = array(
							'custom_field_id' => $custom_field_id,
							'subscription_plans' => '',
							'products' => '',
							'user_field_billing_equiv' => $billing_equiv
							);
							
		$this->db->insert('user_fields',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	function update_custom_field ($user_field_id, $name, $type, $options, $help, $billing_equiv = '', $required = FALSE, $validators = array()) {	
		$this->load->model('custom_fields_model');
		
		// get custom_field_id
		$field = $this->get_custom_field($user_field_id);
		
		// create custom field to user group
		$custom_field_id = $this->custom_fields_model->update_custom_field($field['custom_field_id'], '1', $name, $type, $options, $help, $required, $validators, 'users');
		
		$update_fields = array(
							'subscription_plans' => '',
							'products' => '',
							'user_field_billing_equiv' => $billing_equiv
							);
							
		$this->db->update('user_fields',$update_fields,array('user_field_id' => $user_field_id));
		
		return TRUE;
	}
	
	function delete_custom_field ($user_field_id) {
		// get custom_field_id
		$field = $this->get_custom_field($user_field_id);
		
		$this->load->model('custom_fields_model');
		$this->custom_fields_model->delete_custom_field($field['custom_field_id'], 'users');
		
		$this->db->delete('user_fields',array('user_field_id' => $user_field_id));
		
		return TRUE;
	}
	
	function get_custom_field ($id) {
		$return = $this->get_custom_fields(array('id' => $id));
		
		if (empty($return)) {
			return FALSE;
		}
		
		return $return[0];
	}
	
	function get_custom_fields ($filters = array()) {
		$this->load->model('custom_fields_model');
	
		if (isset($filters['id'])) {
			$this->db->where('user_field_id',$filters['id']);
		}
	
		$this->db->join('custom_fields','custom_fields.custom_field_id = user_fields.custom_field_id','inner');
		$this->db->order_by('custom_fields.custom_field_order','ASC');
		
		$result = $this->db->get('user_fields');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$fields = array();
		foreach ($result->result_array() as $field) {
			$fields[] = array(
							'id' => $field['user_field_id'],
							'custom_field_id' => $field['custom_field_id'],
							'friendly_name' => $field['custom_field_friendly_name'],
							'name' => $field['custom_field_name'],
							'type' => $field['custom_field_type'],
							'options' => (!empty($field['custom_field_options'])) ? unserialize($field['custom_field_options']) : array(),
							'help' => $field['custom_field_help_text'],
							'order' => $field['custom_field_order'],
							'required' => $field['custom_field_required'],
							'validators' => (!empty($field['custom_field_validators'])) ? unserialize($field['custom_field_validators']) : array(),
							'billing_equiv' => $field['user_field_billing_equiv']
						);
		}
		
		return $fields;
	}
}