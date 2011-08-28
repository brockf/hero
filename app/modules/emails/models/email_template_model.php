<?php

/**
* Email Template Model 
*
* Contains all the methods used to create, update, and delete system email templates.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Email_template_model extends CI_Model
{
	private $cache;
	
	function __construct ()
	{
		parent::__construct();
	}
	
	/**
	* New Email Template
	*
	* @param string $name
	* @param string $subject
	* @param string $body
	* @param boolean $is_html
	*
	* @return int $email_template_id
	*/
	function new_template ($name, $subject, $body, $is_html = FALSE) {
		$insert = array(
						'email_template_name' => $name,
						'email_template_subject' => $subject,
						'email_template_body' => $body,
						'email_template_is_html' => (empty($is_html)) ? '0' : '1'
					);
					
		$this->db->insert('email_templates', $insert);
		
		return $this->db->insert_id();			
	}
	
	/**
	* Delete Email Template
	*
	* @param int $email_template_id
	*
	* @return boolean 
	*/
	function delete_template ($email_template_id) {
		$this->db->delete('email_templates', array('email_template_id' => $email_template_id));
		
		return TRUE;
	}
	
	/**
	* Get Email Template
	*
	* @param int $email_template_id
	*
	* @return array $template
	*/
	function get_template ($email_template_id) {
		$templates = $this->get_templates(array('id' => $email_template_id));
		
		return (empty($templates)) ? FALSE : $templates[0];
	}
	
	/**
	* Get Email Templates
	*
	* @param $filters['id']
	*
	* @return array $templates
	*/
	function get_templates ($filters = array()) {
		if (isset($filters['id'])) {
			$this->db->where('email_template_id',$filters['id']);
		}
		
		$this->db->order_by('email_template_name');
		
		$result = $this->db->get('email_templates');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$templates = array();
		
		foreach ($result->result_array() as $template) {
			$templates[] = array(
								'id' => $template['email_template_id'],
								'name' => $template['email_template_name'],
								'subject' => $template['email_template_subject'],
								'body' => $template['email_template_body'],
								'is_html' => (empty($template['email_template_is_html'])) ? FALSE : TRUE
							);
		}
		
		return $templates;
	}
}