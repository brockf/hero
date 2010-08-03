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
		$this->dbforge->add_field('`submission_date` DATETIME NOT NULL');
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
		$form = $this->get_form($form_id);
	
		$this->db->delete('forms',array('form_id' => $form_id));
		
		// delete link
		$this->load->model('link_model');
		$this->link_model->delete_link($form['link_id']);
		
		// delete table
		$this->load->dbforge();
		$this->dbforge->drop_table($form['table_name']);
		
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
		
		// get custom fields
		$CI =& get_instance();
		
		$CI->load->model('custom_fields_model');
		$custom_fields = $CI->custom_fields_model->get_custom_fields(array('group' => $form[0]['custom_field_group_id']));
		
		$form[0]['custom_fields'] = $custom_fields;
		
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
	
	/*
	* New Response
	*
	* @param int $form_id
	* @param int $user_id
	* @param array $custom_fields
	*
	* @return $response_id
	*/
	function new_response($form_id, $user_id = FALSE, $custom_fields = array()) {
		$form = $this->get_form($form_id);
			
		if (empty($form)) {
			die(show_error('Invalid form ID.'));
		}
		
		$date = date('Y-m-d H:i:s');
		
		$insert_fields = array(
							'submission_date' => $date,
							'user_id' => ($user_id) ? $user_id : 0
						);
						
		foreach ($custom_fields as $field => $value) {
			$insert_fields[$field] = $value;
		}
		reset($custom_fields);
		
		$insert_id = $this->db->insert($form['table_name'], $insert_fields);
		
		if (!empty($form['email'])) {
			// build email
			$config['mailtype'] = 'text';
			$config['wordwrap'] = TRUE;
			$this->email->initialize($config);
			
			// build body
			$lines = array();
			$lines[] = 'Date: ' . date('F j, Y, g:i a', strtotime($date));
			
			if (!empty($user_id)) {
				$user = $this->user_model->get_user($user_id);
				$lines[] = 'Member Username: ' . $user['username'];
				$lines[] = 'Member Name: ' . $user['first_name'] . ' ' . $user['last_name'];
				$lines[] = 'Member Email: ' . $user['email'];
			}
			else {
				$lines[] = 'Member: None';
			}

			foreach ($form['custom_fields'] as $field) {
				if ($field['type'] == 'multiselect') {
					$value = implode(', ', unserialize($custom_fields[$field['name']]));
				}
				elseif ($field['type'] == 'file') {
					$value = $custom_fields[$field['name']] . ' (Download: ' . site_url('writeable/custom_uploads/' . $custom_fields[$field['name']]);
				}
				elseif ($field['type'] == 'date') {
					$value = date('F j, Y', strtotime($custom_fields[$field['name']]));
				}
				else {
					$value = $custom_fields[$field['name']];
				}
				
				$lines[] = $field['friendly_name'] . ': ' . $value;
			}
			
			$body = implode("\n\n", $lines);
			
			// send the email
			$this->email->from(setting('site_email'), setting('email_name'));
			$this->email->to($form['email']);
			$this->email->subject('New Submission: ' . $form['title']);
			$this->email->message($body);
			
			$this->email->send();
		}
		
		return $insert_id;
	}
	
	/**
	* Delete Response
	*
	* @param int $form_id
	* @param int $response_id
	*
	* @return boolean TRUE
	*/
	function delete_response ($form_id, $response_id) {
		$form = $this->get_form($form_id);
		
		if (empty($form)) {
			die(show_error('Form doesn\'t exist.'));
		}
	
		$this->db->delete($form['table_name'],array($form['table_name'] . '_id' => $response_id));
		
		return TRUE;
	}
	
	/**
	* Get Response
	*
	* @param int $form_id
	* @param int $response_id
	*
	* @return array|boolean response
	*/
	function get_response ($form_id, $response_id) {
		$response = $this->get_responses(array('form_id' => $form_id, 'response_id' => $response_id));
		
		if (empty($response)) {
			return FALSE;
		}
		
		return $response[0];
	}
	
	/**
	* Get Responses
	*
	* @param int $filters['form_id'] (REQUIRED)
	* @param int $filters['limit]
	* @param int $filters['response_id']
	* @param string $filters['start_date']
	* @param string $filters['end_date']
	* @param string $filters['username']
	*
	* @return array|boolean responses
	*/
	function get_responses ($filters = array()) {
		if (!isset($filters['form_id'])) {
			die(show_error('Form ID is required in get_responses.'));
		}
	
		$form = $this->get_form($filters['form_id']);
		
		if (empty($form)) {
			return FALSE;
		}
		
		if (isset($filters['response_id'])) {
			$this->db->where($form['table_name'] . '_id', $filters['response_id']);
		}
		
		if (isset($filters['start_date'])) {
			$start_date = date('Y-m-d H:i:s', strtotime($filters['start_date']));
			$this->db->where('submission_date >=', $start_date);
		}
		
		if (isset($filters['end_date'])) {
			$end_date = date('Y-m-d H:i:s', strtotime($filters['end_date']));
			$this->db->where('submission_date <=', $end_date);
		}
		
		if (isset($filters['username'])) {
			$this->db->like('users.user_username',$filters['username']);
		}
	
		$this->db->order_by('submission_date','DESC');
		
		if (isset($filters['limit'])) {
			$this->db->limit($filters['limit']);
		}
		
		$this->db->join('users','users.user_id = ' . $form['table_name'] . '.user_id','LEFT');
		$result = $this->db->get($form['table_name']);
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$responses = array();
		foreach ($result->result_array() as $row) {
			$this_response = array(
								'id' => $row[$form['table_name'] . '_id'],
								'form_id' => $form['id'],
								'submission_date' => local_time($row['submission_date']),
								'user_id' => $row['user_id']
								);
								
			// member data?
			if (!empty($row['user_id'])) {
				$this_response['member_username'] = $row['user_username'];
				$this_response['member_first_name'] = $row['user_first_name'];
				$this_response['member_last_name'] = $row['user_last_name'];
				$this_response['member_email'] = $row['user_email'];
				$this_response['member_signup_date'] = local_time($row['user_signup_date']);
			}
			
			// custom field data
			foreach ($form['custom_fields'] as $field) {
				$this_response[$field['name']] = $row[$field['name']];
			}
		
			$responses[] = $this_response;
		}
		
		return $responses;
	}
}