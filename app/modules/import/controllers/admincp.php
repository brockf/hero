<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Admincp extends Admincp_Controller {

	private $fields = array(
		'user_first_name'	=> 'First Name',
		'user_last_name'	=> 'Last Name',
		'user_username'		=> 'Username',
		'user_email'		=> 'Email',
		'user_password'		=> 'Password',
	);

	public function __construct() 
	{
		parent::__construct();
		
		$this->admin_navigation->parent_active('members');
		
		// Merge any custom fields.
		$this->load->model('custom_fields_model');
		
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group'=>'1'));
		
		foreach ($custom_fields as $field)
		{
			$this->fields[$field['name']] = $field['friendly_name'];
		}
	}
	
	//--------------------------------------------------------------------
	
	public function index() 
	{
		$this->load->library('admin_form');
		
		if ($this->input->post('submit'))
		{
			if (isset($_FILES['userfile']) && isset($_FILES['userfile']['tmp_name']) && is_uploaded_file($_FILES['userfile']['tmp_name']))
			{ 
				$this->load->library('encrypt');
				$this->load->helper('file');
				
				// Read the file in
				$content = read_file($_FILES['userfile']['tmp_name']);
				
				// is this a CSV file?
				if (strpos($content, ',') !== FALSE) {				
					// encrypt it and save it to /writable.
					$content = $this->encrypt->encode($content);
					
					write_file($this->config->item('path_writeable') . 'csv_upload.csv', $content, 'w+');
					return redirect(site_url('admincp/import/fields'));
				}
			}
		}
		
		$form = new Admin_Form;
		
		$form->fieldset('File');
		$form->file('CSV File', 'userfile', '250px', true);
		
		// Compile View data
		$data = array(
			'form'			=> $form->display(),
			'form_action'	=> site_url('admincp/import/') 
		);
		
		$this->load->view('index.php', $data);
	}
	
	//--------------------------------------------------------------------
	
	public function fields() 
	{
		$this->load->helper('file');
		$data = array(
			'fields' => $this->fields
		);
	
		$data['csv_data'] = $this->read_csv_file(5);
		
		$this->load->view('fields', $data);
	}
	
	//--------------------------------------------------------------------
	
	public function do_import() 
	{
		$data = array();
		$error_users = array();
		$total_imports = 0;
		
		$imports = array();
		
		if (isset($_POST['db_field']) && is_array($_POST['db_field']))
		{
			// Grab our records. 
			$records = $this->read_csv_file();

			// Map each of our fields for importing.
			foreach ($records as $record) {
				// Split into each field
				$row_fields = explode(',', $record);
				$new_record = array();

				$count = count($row_fields);
				for ($i=0; $i < $count; $i++) { 
					if (!empty($_POST['db_field'][$i])) {
						$new_record[$_POST['db_field'][$i]] = trim($row_fields[$i]);  
					}
				}
				
				$imports[] = $new_record;
			}
			
			// Create the users
			
			// we may need to generate a password
			$this->load->helper('string');
			
			foreach ($imports as $row)
			{
				$email = isset($row['user_email']) ? $row['user_email'] : '';
				$first_name = isset($row['user_first_name']) ? $row['user_first_name'] : '';
				$last_name = isset($row['user_last_name']) ? $row['user_last_name'] : '';
				$password = isset($row['user_password']) ? $row['user_password'] : '';
				$username = isset($row['user_username']) ? $row['user_username'] : '';
				
				// populate username from email if empty
				if (empty($username) and !empty($email)) {
					$username = $email;
				}
				
				// generate password if empty
				if (empty($password)) {
					$password = random_string('alnum', 8);
				}

				// create custom fields array
				$custom_fields = $row;
				// remove standard form elements from the custom fields array
				unset($custom_fields['user_email'], $custom_fields['user_first_name'], $custom_fields['user_last_name'], $custom_fields['user_password'], $custom_fields['user_username']);
			
				if (empty($email) || empty($first_name) || empty($last_name)) {
					$error_users[] = array('error' => 'missing_info', 'data' => $row);
				} elseif (!$this->user_model->unique_email($email)) {
					$error_users[] = array('error' => 'duplicate_email', 'data' => $row);
				} elseif (!$this->user_model->unique_username($username)) {
					$error_users[] = array('error' => 'duplicate_username', 'data' => $row);
				} elseif ($this->user_model->new_user($email, $password, $username, $first_name, $last_name, false, false, false, $custom_fields) === false) {
					$error_users[] = array('error' => 'miscellaneous', 'data' => $row);
				} else {
					$total_imports++;
				}
			}
		}
	
		// Delete the import file
		unlink($this->config->item('path_writeable') . 'csv_upload.csv');
	
		$data['error_users']	= $error_users;
		$data['total_imports']	= $total_imports;
	
		$this->load->view('results', $data);
	}
	
	//--------------------------------------------------------------------
	
	private function read_csv_file($limit=0) 
	{
		$this->load->helper('file');
		$return = false;
	
		if ($content = read_file('writeable/csv_upload.csv'))
		{
			$this->head_assets->stylesheet('css/dataset.css');
			$this->load->library('Encrypt');
			
			$content = explode("\n", $this->encrypt->decode($content));
			
			if ($limit > 0)
			{
				// Return a slice.
				$return = array_slice($content, 0, $limit);
			}
			else
			{
				// Return all of the results.
				$return = $content;
			}
		}
		
		return $return;
	}
	
	//--------------------------------------------------------------------
	
}