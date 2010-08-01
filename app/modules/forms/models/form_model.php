<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Form Model 
*
* Contains all the methods used to create, update, and delete forms.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Form_model extends CI_Model
{
	function __construct()
	{
		parent::CI_Model();
	}
	
	/*
	* Create New Form
	*
	* @param string $title
	* @param string $url_path
	* @param string $text
	* @param string $button_text
	* @param string $redirect (relative URL string)
	* @param string $email
	* @param array $privileges Array of usergroup ID's who can access it, else FALSE or an empty array
	* @param string $template Output template file
 	*
 	* @return $form_id
 	*/ 										
	function new_form ($title, $url_path, $text, $button_text, $redirect, $email = '', $privileges = array(), $template = 'form.thtml') {
		$this->load->helper('clean_string');
		$table_name = clean_string($title);
		
		$url_path = (empty($url_path)) ? clean_string($title) : clean_string($url_path);
		
		// make sure table doesn't already exist
		if ($this->db->table_exists($table_name)) {
			die(show_error('There is already a table in the database by the name of ' . $table_name . '.  You should retitle your form to avoid a conflict.'));
		}
		
		$this->load->model('link_model');
		$url_path = $this->link_model->get_unique_url_path($url_path);
		$link_id = $this->link_model->new_link($url_path, FALSE, $title, 'Form', 'forms', 'form', 'view');
		
		// create custom field group
		$this->load->model('custom_fields_model');
		$custom_field_group_id = $this->custom_fields_model->new_group('Form: ' . $title);
		
		$insert_fields = array(
							'link_id' => $link_id,
							'form_table_name' => $table_name,
							'custom_field_group_id' => $custom_field_group_id,
							'form_title' => $title,
							'form_text' => $text,
							'form_email' => $email,
							'form_button_text' => $button_text,
							'form_redirect' => $redirect,
							'form_privileges' => (is_array($privileges) and !in_array(0, $privileges)) ? serialize($privileges) : '',
							'form_template' => $template
						);
							
		$this->db->insert('forms',$insert_fields);
		
		$form_id = $this->db->insert_id();
		
		// database functions
		$this->load->dbforge();
		
		// add ID, date, edit_date, admin rows
		$this->dbforge->add_field('`' . $table_name . '_id` INT(11) auto_increment PRIMARY KEY');
		$this->dbforge->add_field('`submission_date` INT(11) NOT NULL');
		$this->dbforge->add_field('`user_id` INT(11) NOT NULL');
		
		// create table
		$this->dbforge->create_table($table_name);
		
		return $form_id;
	}
	
	/*
	* Update Form
	*
	* @param int $form_id
	* @param string $title
	* @param string $url_path
	* @param string $text
	* @param string $button_text
	* @param string $redirect (relative URL string)
	* @param string $email
	* @param array $privileges Array of usergroup ID's who can access it, else FALSE or an empty array
	* @param string $template Output template file
 	*
 	* @return $form_id
 	*/ 										
	function update_form ($form_id, $title, $url_path, $text, $button_text, $redirect, $email = '', $privileges = array(), $template = 'form.thtml') {
		$form = $this->get_form($form_id);
		
		$this->load->model('link_model');
		if ($url_path != $form['url_path']) {
			$this->load->helper('clean_string');
			$url_path = clean_string($url_path);
			
			$url_path = $this->link_model->get_unique_url_path($url_path);
			$this->link_model->update_url($form['link_id'], $url_path);
		}
		$this->link_model->update_title($form['link_id'], $title);
		
		$update_fields = array(
							'form_title' => $title,
							'form_text' => $text,
							'form_email' => $email,
							'form_button_text' => $button_text,
							'form_redirect' => $redirect,
							'form_privileges' => (is_array($privileges) and !in_array(0, $privileges)) ? serialize($privileges) : '',
							'form_template' => $template
						);
							
		$this->db->update('forms',$update_fields,array('form_id' => $form_id));
		
		return TRUE;
	}
	
	/*
	* Delete Form
	*
	* @param int $form_id
	*
	* @return boolean TRUE
	*/
	function delete_form ($form_id) {
		$form = $this->get_feed($form_id);
	
		$this->db->delete('forms',array('form_id' => $form_id));
		
		$this->load->model('link_model');
		$this->link_model->delete_link($form['link_id']);
		
		return TRUE;
	}
	
	/**
	* Get Form ID
	*
	* Returns form ID from a URL_path
	*
	* @param $url_path
	* 
	* @return boolean|int The form ID, or FALSE
	*/
	function get_form_id($url_path) {
		$this->db->select('form_id');
		$this->db->where('link_url_path',$url_path);
		$this->db->join('links','forms.link_id = links.link_id','inner');
		$result = $this->db->get('forms');
		
		if ($result->num_rows() == FALSE) {
			return FALSE;
		}
		
		$form = $result->row_array();
		
		return $form['form_id'];
	}
	
	/*
	* Get Form
	*
	* @param int $form_id
	*
	* @return array
	*/
	function get_form ($form_id) {
		$form = $this->get_forms(array('id' => $form_id));
		
		if (empty($form)) {
			return FALSE;
		}
		
		return $form[0];
	}
	
	/*
	* Get Forms
	* @param int $filters['id']
	* @param string $filters['title']
	*
	*/
	function get_forms ($filters = array()) {
		if (isset($filters['id'])) {
			$this->db->where('form_id',$filters['id']);
		}
		if (isset($filters['title'])) {
			$this->db->like('form_title',$filters['title']);
		}
	
		$this->db->order_by('form_title');
		$this->db->join('links','links.link_id = forms.link_id','left');
		$result = $this->db->get('forms');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$forms = array();
		foreach ($result->result_array() as $row) {
			$this->db->select('count(*) AS `num_responses`',FALSE,FALSE);
			$result_2 = $this->db->get($row['form_table_name']);
			$row_2 = $result_2->row_array();
			$num_responses = $row_2['num_responses'];
		
			$forms[] = array(
						'id' => $row['form_id'],
						'link_id' => $row['link_id'],
						'table_name' => $row['form_table_name'],
						'custom_field_group_id' => $row['custom_field_group_id'],
						'title' => $row['form_title'],
						'text' => $row['form_text'],
						'admin_link' => site_url('admincp/forms/responses/' . $row['form_id']),
						'url' => site_url($row['link_url_path']),
						'url_path' => $row['link_url_path'],
						'email' => $row['form_email'],
						'button_text' => $row['form_button_text'],
						'redirect' => $row['form_redirect'],
						'privileges' => (!empty($row['form_privileges'])) ? unserialize($row['form_privileges']) : array(),
						'num_responses' => $num_responses,
						'template' => $row['form_template']
					);
		}
		
		return $forms;
	}
}