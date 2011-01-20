<?php

class Phpbb_functions {
	public $CI;
	private $session_began;
	
	function __construct () {
		$this->CI =& get_instance();
		$this->session_began = FALSE;
	}
	
	/**
	* Validate Config
	*
	* Validates our settings so we don't try to interact with a broken install and throw errors everywhere
	* 
	* @return boolean
	*/
	function validate_config () {
		if (setting('phpbb3_table_prefix') == '' or setting('phpbb3_document_root') == '') {
			return FALSE;
		}
		
		if (!$this->CI->db->table_exists(setting('phpbb3_table_prefix') . 'users')) {
			return FALSE;
		}		
		
		$test_file = setting('phpbb3_document_root') . 'index.php';
		if (@!file_exists($test_file)) {
			return FALSE;
		}
		
		return TRUE;
	}
	
	function hook_login ($user_id, $password) {
		if (!$this->validate_config()) {
			return FALSE;
		}
	
		// phpBB requires this definition
		define('IN_PHPBB', true);
		
		// declare global vars
		global $phpbb_root_path;
		global $phpEx;
		global $db;
		global $config;
		global $user;
		global $auth;
		global $cache;
		global $template;
		
		// the php extension being used
		$phpEx = 'php';
		$phpbb_root_path = setting('phpbb3_document_root');
		
		// common libraries
		require_once($phpbb_root_path . 'common.' . $phpEx);
		if ($this->session_began != TRUE) {
		    $user->session_begin();
		    $this->session_began = TRUE;
		}
		
		$login = $auth->login($this->CI->user_model->get('username'), $password);
		
		// get username
		if ($login['status'] != '3') {
			if ($login['status'] == '11') {
				// password failure
				$this->_update_password($this->CI->user_model->get('username'), $password);
				
				$login = $auth->login($this->CI->user_model->get('username'), $password);
				
				if ($login['status'] != '3') {
					return FALSE;
				}
				else {
					$this->_fix_groups($user_id);
					return TRUE;
				}
			}
			else {
				// maybe they don't have an account?
				$result = $this->CI->db->select('*')->from($this->_table('users'))->where('username', $this->CI->user_model->get('username'))->get();
				
				if ($result->num_rows() == 0) {
					// no account
					$this->_register($this->CI->user_model->get('username'), $password, $this->CI->user_model->get('email'), setting('phpbb3_group_default'));
					
					$login = $auth->login($this->CI->user_model->get('username'), $password);
					if ($login['status'] != '3') {
						// we are *still* failing - this is irrepairable
						return FALSE;
					}
					else {
						$this->_fix_groups($user_id);
						return TRUE;
					}
				}
				else {
					return FALSE;
				}
			}
		}
		else {
			// we logged in!
			$this->_fix_groups($user_id);
			return TRUE;
		}
	}
	
	function hook_logout ($user_id) {
		if (!$this->validate_config()) {
			return FALSE;
		}
		
		define('IN_PHPBB', true);

		global $phpbb_root_path;
		global $phpEx;
		global $db;
		global $config;
		global $user;
		global $auth;
		global $cache;
		global $template;
		
		// the php extension being used
		$phpEx = 'php';
		$phpbb_root_path = setting('phpbb3_document_root');
		
		// common libraries
		require_once($phpbb_root_path . 'common.' . $phpEx);
		if ($this->session_began != TRUE) {
		    $user->session_begin();
		    $this->session_began = TRUE;
		}
		
		$local_user = $this->CI->user_model->get_user($user_id);
		
		$result = $this->CI->db->select('user_id')->where('username',$local_user['username'])->get($this->_table('users'));
		$row = $result->row_array();
		
		$this->CI->db->delete($this->_table('sessions'), array('session_user_id' => $row['user_id']));
		
		return TRUE;
	}
	
	function hook_change_password ($user_id, $password) {
		if (!$this->validate_config()) {
			return FALSE;
		}
	
		$user = $this->CI->user_model->get_user($user_id);
		
		$this->_update_password($user['username'], $password);
		
		return TRUE;
	}
	
	function hook_register ($user_id, $password) {
		if (!$this->validate_config()) {
			return FALSE;
		}
		
		$user = $this->CI->user_model->get_user($user_id);
		
		$this->_register($user['username'], $password, $user['email'], setting('phpbb3_group_default'));
		
		return TRUE;
	}

	function hook_subscribe ($subscription_id) {
		if (!$this->validate_config()) {
			return FALSE;
		}

		$result = $this->CI->db->select('user_id')
							   ->from('subscriptions')
							   ->where('subscription_id',$subscription_id)
							   ->join('users','users.customer_id = subscriptions.customer_id')
							   ->limit(1)
							   ->get();
							   
		if ($result->num_rows() == 0) {
			log_message('debug','Failed to move user into phpBB usergroup because we couldn\'t find a user_id for subscription #' . $subscription_id);
			return FALSE;
		}
							   
		$user = $result->row_array();
		
		$this->_fix_groups($user['user_id']);		
	}
	
	function hook_expire ($subscription_id) {
		if (!$this->validate_config()) {
			return FALSE;
		}
		
		$result = $this->CI->db->select('user_id')
							   ->from('subscriptions')
							   ->where('subscription_id',$subscription_id)
							   ->join('users','users.customer_id = subscriptions.customer_id')
							   ->limit(1)
							   ->get();
							   
		if ($result->num_rows() == 0) {
			log_message('debug','Failed to move user out of phpBB usergroup because we couldn\'t find a user_id for subscription #' . $subscription_id);
			return FALSE;
		}
							   
		$user = $result->row_array();
		
		$this->_fix_groups($user['user_id']);
	}	
	
	// ensures that they are in their proper groups
	function _fix_groups ($user_id) {
		if (!$this->validate_config()) {
			return FALSE;
		}
	
		if (setting('phpbb3_groups') == '') {
			return FALSE;
		}
		
		define('IN_PHPBB', true);

		global $phpbb_root_path;
		global $phpEx;
		global $db;
		global $config;
		global $user;
		global $auth;
		global $cache;
		global $template;
		
		// the php extension being used
		$phpEx = 'php';
		$phpbb_root_path = setting('phpbb3_document_root');
		
		// common libraries
		require_once($phpbb_root_path . 'common.' . $phpEx);
		
		// the user library
		require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		$local_user = $this->CI->user_model->get_user($user_id);
		
		// remove all groups from the user's phpBB
		$group_assignments = unserialize(setting('phpbb3_groups'));
		
		foreach ($group_assignments as $local => $phpbb) {
			group_user_del($phpbb,'',$local_user['username']);
		}
		
		// add default group
		group_user_add(setting('phpbb3_group_default'),'',$local_user['username']);
		
		// now add each group
		$groups = $local_user['usergroups'];
		
		foreach ($groups as $group) {
			group_user_add($group_assignments[$group['id']],'',$local_user['username']);
		}
		
		return TRUE;
	}
	
	function _update_password ($username, $password) {
  		$this->CI->db->update($this->_table('users'), array('user_password' => md5($password)), array('username' => $username));
  		
  		return TRUE;
	}	
	
	function _register ($username, $password, $email, $group_id) {
		define('IN_PHPBB', true);

	    global $phpbb_root_path;
	    global $phpEx;
	    global $db;
	    global $config;
	    global $user;
	    global $auth;
	    global $cache;
	    global $template;
	    
	    $phpEx = 'php';
	    $phpbb_root_path = setting('phpbb3_document_root');
	
	    // common libraries
	    require_once($phpbb_root_path . 'common.' . $phpEx);
	    if ($this->session_began != TRUE) {
		    $user->session_begin();
		    $this->session_began = TRUE;
		}
	    $auth->acl($user->data);
	
	    // the user library
	    require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
	    
	    // hash the password
	    $password = md5($password);
	    
	    // user data
	    $user_row = array(
	      'username' => $username,
	      'user_password' => $password,
	      'user_email' => $email,
	      'group_id' => $group_id,
	      'user_timezone' => '1.00',
	      'user_dst' => 0,
	      'user_lang' => 'en',
	      'user_type' => '0',
	      'user_actkey' => '',
	      'user_dateformat' => 'd M Y H:i',
	      'user_regdate' => time(),
	    );
	
	    // perform the registration
	    $phpbb_user_id = user_add($user_row);
	    
	    return TRUE;
	}		
	
	function _table ($name) {
		return setting('phpbb3_table_prefix') . $name;
	}		
}