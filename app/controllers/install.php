<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Install Controller 
*
* Installs app by 1) generating config details and setting up the DB file and, 2) Creating the first admin account
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework
*/
class Install extends CI_Controller {

	function Install()
	{
		parent::__construct();
		
		define("_INSTALLER","1");
		
		// make sure we have a trailing slash
		if (substr($this->current_url(),-7,7) == 'install') {
			// we don't have a trailing slash
			header('Location: ' . $this->current_url() . '/');
			die();
		}
				
		// no one should access this if app is installed
		if (file_exists(APPPATH . 'config/installed.php')) {
			show_error($this->config->item('app_name') .' has already been installed.  This file, /system/opengateway/controllers/install.php, can be deleted.');
			die();
		}
	}
	
	function index() {
		$this->load->helper('file');
		$this->load->helper('string');
		
		// check for submission
		if ($this->input->post('base_url') != '') {
			// we have a submission
			
			// validate MySQL info
			$valid_mysql = FALSE;
			if ($dbh = @mysql_connect($this->input->post('db_host'),$this->input->post('db_user'),$this->input->post('db_pass')))
		    {
		        if (@mysql_select_db($this->input->post('db_name'), $dbh))
		        {
		        	$valid_mysql = TRUE;
		        }
		    }
		    
		    if ($valid_mysql == FALSE) {
		    	$error_mysql = TRUE;
		    }
		    
			$base_url = rtrim($this->input->post('base_url'),'/') . '/';
			$cron_key = $this->input->post('cron_key');
			$encryption_key = $this->input->post('encryption_key');
			
			if (empty($base_url) or empty($cron_key) or empty($encryption_key)) {
				$error_empty_site = TRUE;
			}
			
			if (!strstr($base_url,'http://')) {
				$error_base_url = TRUE;
			}
			
			// no errors? let's write to config files
			if (!isset($error_empty_site) and !isset($error_base_url) and !isset($error_mysql)) {
				// all good!
				
				// read in current config
				$config_file = read_file(APPPATH . 'config/config.php');
				
				// swap in variables
				$config_file = preg_replace('/\$config\[\'base_url\'\](.*?)\=(.*?)\"(.*?)\"/','$config[\'base_url\'] = "' . $base_url . '"',$config_file);
				$config_file = preg_replace('/\$config\[\'cron_key\'\](.*?)\=(.*?)\'(.*?)\'/','$config[\'cron_key\'] = \'' . $cron_key . '\'',$config_file);
				$config_file = preg_replace('/\$config\[\'encryption_key\'\](.*?)\=(.*?)\"(.*?)\"/','$config[\'encryption_key\'] = "' . $encryption_key . '"',$config_file);
				
				// write config file
				write_file(APPPATH . 'config/config.php',$config_file,'w');
				
				// create database file
				$database_file = read_file(APPPATH . 'config/database.format.php');
				
				$database_file = preg_replace('/\$db\[\'default\'\]\[\'hostname\'\](.*?)\=(.*?)\"(.*?)\"/','$db[\'default\'][\'hostname\'] = "' . $this->input->post('db_host') . '"',$database_file);
				$database_file = preg_replace('/\$db\[\'default\'\]\[\'username\'\](.*?)\=(.*?)\"(.*?)\"/','$db[\'default\'][\'username\'] = "' . $this->input->post('db_user') . '"',$database_file);
				$database_file = preg_replace('/\$db\[\'default\'\]\[\'password\'\](.*?)\=(.*?)\'(.*?)\'/','$db[\'default\'][\'password\'] = \'' . $this->input->post('db_pass') . '\'',$database_file);
				$database_file = preg_replace('/\$db\[\'default\'\]\[\'database\'\](.*?)\=(.*?)\"(.*?)\"/','$db[\'default\'][\'database\'] = "' . $this->input->post('db_name') . '"',$database_file);
				
				// write database file
				write_file(APPPATH . 'config/database.php',$database_file,'w');
				
				// import initial database structure
				// note - all update files will be run before the next step loads (because auto_updater will be invoked)
				$structure = read_file(APPPATH . 'updates/install.php');
				$structure = str_replace('<?php if (!defined(\'BASEPATH\')) exit(\'No direct script access allowed\'); ?>','',$structure);
				
				// break into newlines
				$structure = explode("\n",$structure);
				
				// run mysql queries
				$query = "";
				$querycount = 0;
				foreach ($structure as $sql_line)
				{
					if (trim($sql_line) != "" and substr($sql_line,0,2) != "--")
					{
						$query .= $sql_line;
						if (substr(trim($query), -1, 1) == ";")
						{
							// this query is finished, execute it
						    if (@mysql_query($query, $dbh))
						    {
						    	$query = "";
						    	$querycount++;
						    }
						    else {
						    	show_error('There was a critical error importing the initial database structure.  Please contact support.<br /><br />Query:<br /><br />' . $query);
						    	die();
						    }
						}
					}
				}
				
				// update settings
				mysql_query('UPDATE `settings` SET `setting_value`=\'' . $this->input->post('site_name') . '\' WHERE `setting_name`=\'site_name\' or `setting_name`=\'email_name\'');
				mysql_query('UPDATE `settings` SET `setting_value`=\'' . $this->input->post('site_email') . '\' WHERE `setting_name`=\'site_email\'');
				
				// send to administrator account setup
				if (strstr($this->current_url(),'/index')) {
					$forward_url = str_replace('/index','/admin',current_url());
				}
				else {
					$forward_url = rtrim($this->current_url(),'/') . '/admin'; 
				}
				
				// send to admin step
				header('Location: ' . $forward_url);
				die();
			}
		}
		
		// which folders/files should be writeable?
		$file_permissions = array(
							str_replace('system/','',BASEPATH) . 'writeable',
							APPPATH . 'config',
							APPPATH . 'config/config.php'
							);
					
		$file_permission_errors = array();		
		foreach ($file_permissions as $file) {
			if (!is_writable($file)) {
				$file_permission_errors[] = array(
												'file' => $file,
												'folder' => (is_dir($file)) ? TRUE : FALSE
											);
			}
		}
		
		// get domain name
		$domain = ($this->input->post('base_url')) ? $this->input->post('base_url') : rtrim(str_replace(array('install','index'),'',$this->current_url()), '/') . '/';
		
		// default values
		$db_user = ($this->input->post('db_user')) ? $this->input->post('db_user') : '';
		$db_host = ($this->input->post('db_host')) ? $this->input->post('db_host') : 'localhost';
		$db_pass = ($this->input->post('db_pass')) ? $this->input->post('db_pass') : '';
		$db_name = ($this->input->post('db_name')) ? $this->input->post('db_name') : '';
		$site_name = ($this->input->post('site_name')) ? $this->input->post('site_name') : 'Your Website';
		
		// build email from $domain
		$email_domain = str_replace(array('http://','www.','/'),'',$domain);
		
		$site_email = ($this->input->post('site_email')) ? $this->input->post('site_email') : 'you@' . $email_domain;
		
		// generate random keys
		$cron_key = random_string('unique');
		$encryption_key = random_string('unique');
		
		$vars = array(
					'file_permission_errors' => $file_permission_errors,
					'domain' => $domain,
					'cron_key' => $cron_key,
					'encryption_key' => $encryption_key,
					'site_name' => $site_name,
					'site_email' => $site_email,
					'error_mysql' => (isset($error_mysql) and !empty($error_mysql)) ? TRUE : FALSE,
					'error_empty_site' => (isset($error_empty_site) and !empty($error_empty_site)) ? TRUE : FALSE,
					'error_base_url' => (isset($error_base_url) and !empty($error_base_url)) ? TRUE : FALSE,
					'db_user' => $db_user,
					'db_host' => $db_host,
					'db_name' => $db_name,
					'db_pass' => $db_pass
				);
		
		$this->load->view(branded_view('install/configuration.php'), $vars);
	}
	
	function current_url() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		
		return $pageURL;
	}
	
	function admin () {
		$this->load->library('session');
		$this->load->helper('url');
		
		if ($this->input->post('username')) {
			if ($this->input->post('password') != $this->input->post('password2')) {
				$error_password = 'Your passwords do not match.';
			}
			elseif (strlen($this->input->post('password')) < 6) {
				$error_password = 'Your password is less than 6 characters in length.  It must be longer.';
			}
		
			if (!isset($error_password)) {
				// form submission
				$user_id = $this->user_model->new_user(
													 $this->input->post('email'),
													 $this->input->post('password'),
													 $this->input->post('username'),
													 $this->input->post('first_name'),
													 $this->input->post('last_name'),
													 FALSE,
													 FALSE,
													 TRUE);
				
			if (isset($user_id)) {
					// success!
					$this->session->set_userdata('username',$this->input->post('username'));
					$this->session->set_userdata('email',$this->input->post('email'));
					$this->session->set_userdata('password',$this->input->post('password'));
					
					header('Location: ' . site_url('install/complete'));
					die();
				}
			}
		}
	
		// default values
		$username = ($this->input->post('username')) ? $this->input->post('username') : 'admin';
		$email = ($this->input->post('email')) ? $this->input->post('email') : '';
		$first_name = ($this->input->post('first_name')) ? $this->input->post('first_name') : '';
		$last_name = ($this->input->post('last_name')) ? $this->input->post('last_name') : '';
		$gmt_offset = ($this->input->post('gmt_offset')) ? $this->input->post('gmt_offset') : 'UM5';
		
		$vars = array(
				'username' => $username,
				'email' => $email,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'gmt_offset' => $gmt_offset,
				'error_password' => (isset($error_password)) ? $error_password : FALSE
				);
		
		$this->load->view(branded_view('install/admin.php'), $vars);
	}
	
	function complete () {
		$this->load->helper('file');
		
		// write the file that disables the installer - they can't even refresh this page now
		write_file(APPPATH . 'config/installed.php', '<?php /* App is installed */ ?>','w');
		
		$vars = array(
				'username' => $this->session->userdata('username'),
				'email' => $this->session->userdata('email'),
				'password' => $this->session->userdata('password'),
				'cron_key' => $this->config->item('cron_key'),
				'cp_link' => site_url('admincp')
				);
		
		$this->load->view(branded_view('install/complete.php'), $vars);
	}
}