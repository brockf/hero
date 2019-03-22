<?php

/**
* User Login Model 
*
* Contains all the methods used to track login records
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Login_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	* Record Login
	*
	* @param int $user_id
	*
	* @return int $login_id
	*/
	function new_login ($user_id) {
		$insert_fields = array(
							'user_id' => $user_id,
							'user_login_date' => date('Y-m-d H:i:s'),
							'user_login_ip' => $this->input->ip_address(),
							'user_login_browser' => $this->input->user_agent()
							);
							
		$this->db->insert('user_logins',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/*
	* Get Login Records
	*
	* @param int $filters['id'] Specify login record ID
	* @param int $filters['user_id'] Filter by user ID
	* @param string $filters['username'] Filter by username
	* @param int $filters['group_id'] Filter by usergroup ID
	* @param string $filters['ip'] Filter by IP address search (e.g., "125.34.")
	* @param string $filters['browser'] Filter by browser search (e.g., "mozilla")
	* @param date $filters['start_date'] Only retrieve records after or including this date (requires $filters['end_date'])
	* @param date $filters['end_date'] Only retrieve records before or including this date (requires $filters['start_date'])
	* @param string $filters['sort'] Field to sort by
	* @param string $filters['sort_dir'] ASC or DESC
	* @param int $filters['limit'] How many records to retrieve
	* @param int $filters['offset'] Start records retrieval at this record
	*
	* @return array Login records, else FALSE
	*/
	function get_logins ($filters = array()) {
		if (isset($filters['id'])) {
			$this->db->where('user_login_id',$filters['id']);
		}
		if (isset($filters['user_id'])) {
			$this->db->where('user_logins.user_id',$filters['user_id']);
		}
		if (isset($filters['username'])) {
			$this->db->where('user_username',$filters['username']);
		}
		if (isset($filters['group_id'])) {
			$this->db->where('(user_groups LIKE \'%|' . $filters['group'] . '|%\' or user_groups = \'|' . $filters['group'] . '|\')');
		}
		if (isset($filters['ip'])) {
			$this->db->like('user_login_ip',$filters['ip']);
		}
		if (isset($filters['browser'])) {
			$this->db->like('user_login_browser',$filters['browser']);
		}
		if (isset($filters['start_date']) and isset($filters['end_date'])) {
			// got a date range
			$this->db->where('user_login_date >=',date('Y-m-d H:i:s',strtotime($filters['start_date'])));
			$this->db->where('user_login_date <=',date('Y-m-d H:i:s',strtotime($filters['end_date'])));
		}
		
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'user_login_id';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'DESC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		$this->db->join('users','users.user_id = user_logins.user_id');
		$result = $this->db->get('user_logins');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		else {
			$logins = array();
			
			foreach ($result->result_array() as $login) {
				$groups = explode('|',$login['user_groups']);
				$user_groups = array();
				foreach ($groups as $group) {
					if (!empty($group)) {
						$user_groups[] = $group;
					}
				}
				
				$logins[] = array(
									'id' => $login['user_login_id'],
									'user_id' => $login['user_id'],
									'date' => local_time($login['user_login_date']),
									'ip' => $login['user_login_ip'],
									'browser' => $login['user_login_browser'],
									'username' => $login['user_username'],
									'email' => $login['user_email'],
									'usergroups' => $user_groups
								);
			}
			
			return $logins;
		}
	}	
}