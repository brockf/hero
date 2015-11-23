<?php
/**
* Customer Model 
*
* Contains all the methods used to create, update, and delete customers.
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework

*/
class Customer_model extends CI_Model
{
	function Customer_model()
	{
		parent::__construct();
	}
	
	/**
	* Create a new customer
	*
	* Creates a new customer.
	*
	* @param string $params['first_name'] Client's first name
	* @param string $params['last_name'] Client's last name
	* @param string $params['company'] Client's company. Optional.
	* @param string $params['address_1'] Client's address line 1. Optional.
	* @param string $params['address_2'] Client's address line 2. Optional.
	* @param string $params['city'] Client's city. Optional.
	* @param string $params['state'] Client's state. Optional.  If the country is US or Canada, the 2-letter abbreviation should be used.
	* @param string $params['postal_code'] Client's postal code. Optional. 
	* @param string $params['country'] Client's country in ISO format. Optional.
	* @param string $params['phone'] Client's phone. Optional.
	* @param string $params['email'] Client's email. Optional.
	* 
	* @return int New Customer ID
	*/
	
	function NewCustomer($params)
	{	
		if (!isset($params['email'])) $params['email'] = '';
		if (!isset($params['company'])) $params['company'] = '';
		if (!isset($params['internal_id'])) $params['internal_id'] = '';
		if (!isset($params['address_1'])) $params['address_1'] = '';
		if (!isset($params['address_2'])) $params['address_2'] = '';
		if (!isset($params['city'])) $params['city'] = '';
		if (!isset($params['state'])) $params['state'] = '';
		if (!isset($params['postal_code'])) $params['postal_code'] = '';
		if (!isset($params['phone'])) $params['phone'] = '';
		
		// Make sure the country is in the proper format
		$this->load->library('field_validation');
		if (!empty($params['country'])) {
			$country_id = $this->field_validation->ValidateCountry($params['country']);
			
			if(!$country_id) {
				die($this->response->Error(1007));
			}
		}
		else {
			$country_id = 0;
		}
		
		// If the country is US or Canada, we need to validate and supply the 2 letter abbreviation
		$this->load->helper('states_helper');
		$country_array = array(124,840);
		if(in_array($country_id, $country_array)) {
			$state = GetState($params['state']);
			if($state) {
				$params['state'] = $state;
			} else {
				die($this->response->Error(1012));
			}
		}
			
		if ($customer_id = $this->SaveNewCustomer($params['first_name'], $params['last_name'], $params['company'], $params['internal_id'], $params['address_1'], $params['address_2'], $params['city'], $params['state'], $params['postal_code'], $country_id, $params['phone'], $params['email']))
		{
			return $customer_id;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Save the new customer
	*
	* Saves a new customer into the database and returns the resulting customer_id
	*
	* @param string $params['first_name'] Client's first name
	* @param string $params['last_name'] Client's last name
	* @param string $params['company'] Client's company. Optional.
	* @param string $params['address_1'] Client's address line 1. Optional.
	* @param string $params['address_2'] Client's address line 2. Optional.
	* @param string $params['city'] Client's city. Optional.
	* @param string $params['state'] Client's state. Optional.  If the country is US or Canada, the 2-letter abbreviation should be used.
	* @param string $params['postal_code'] Client's postal code. Optional. 
	* @param string $params['country'] Client's country in ISO format. Optional.
	* @param string $params['phone'] Client's phone. Optional.
	* @param string $params['email'] Client's email. Optional.
	* 
	* @return int New Customer ID
	*/
	
	// Save new customer 
	function SaveNewCustomer($first_name, $last_name, $company = '', $internal_id = '', $address_1 = '', $address_2 = '', $city = '', $state = '', $postal_code = '', $country_id = '', $phone = '', $email = '')
	{
		$insert_data = array(
							'first_name' 	=> $first_name,
							'last_name' 	=> $last_name,
							'company'		=> $company,
							'internal_id' 	=> $internal_id,
							'address_1'		=> $address_1,
							'address_2'		=> $address_2,
							'city'			=> $city,
							'state'			=> $state,
							'postal_code'	=> $postal_code,
							'country'		=> $country_id,
							'phone'			=> $phone,
							'email'			=> $email,
							'active'		=> '1',
							'date_created'  => date('Y-m-d, H:i:s'),
							);
		$this->db->insert('customers', $insert_data);
		
		$customer_id = $this->db->insert_id();
		
		return $customer_id;						
	}
	
	/**
	* Get the customer details.
	*
	* Returns a array containg all the customers's details.  If the customer does not belong to the client, an error is returned.
	*
	* @param int $customer_id The customer ID
	* 
	* @return mixed Array containing all the customer details.
	*/
	function GetCustomerDetails($customer_id)
	{
		$this->db->where('customer_id', $customer_id);
		$this->db->limit(1);
		$query = $this->db->get('customers');
		if($query->num_rows > 0) {
			foreach($query->row() as $key => $value) {
				$data[$key] = $value;
			}
			return $data;	
		} else {
			die($this->response->Error(4000));
		}
	}
	
	
	/**
	* Updates a customer's details.
	*
	* Updates a customer details. If the customer does not belong to the client, an error is returned.
	*
	* @param int $customer_id The Customer to update.
	* @param string $params['internal_id'] Customer's internal_id.  Optional.
	* @param string $params['first_name'] Customer's first name. Optional.
	* @param string $params['last_name'] Customer's last name. Optional.
	* @param string $params['company'] Customer's company. Optional.
	* @param string $params['address_1'] Customer's address line 1. Optional.
	* @param string $params['address_2'] Customer's address line 2. Optional.
	* @param string $params['city'] Customer's city. Optional.
	* @param string $params['state'] Customer's state. Optional.
	* @param string $params['postal_code'] Customer's postal code. Optional. 
	* @param string $params['country'] Customer's country. Optional.
	* @param string $params['phone'] Customer's phone. Optional.
	* @param string $params['email'] Customer's email. Optional.
	* 
	* @return mixed Array containing new customer_id
	*/
	function UpdateCustomer($customer_id, $params)
	{
		$this->load->library('field_validation');

		if(!isset($customer_id)) {
			return FALSE;
		}

		if(isset($params['internal_id'])) {
			$update_data['internal_id'] = $params['internal_id'];
		}
		
		if(isset($params['first_name'])) {
			$update_data['first_name'] = $params['first_name'];
		}
		
		if(isset($params['last_name'])) {
			$update_data['last_name'] = $params['last_name'];
		}
		
		if(isset($params['company'])) {
			$update_data['company'] = $params['company'];
		}
		
		if(isset($params['internal_id'])) {
			$update_data['internal_id'] = $params['internal_id'];
		}
		
		if(isset($params['address_1'])) {
			$update_data['address_1'] = $params['address_1'];
		}
		
		if(isset($params['address_2'])) {
			$update_data['address_2'] = $params['address_2'];
		}
		
		if(isset($params['city'])) {
			$update_data['city'] = $params['city'];
		}
		
		if(isset($params['postal_code'])) {
			$update_data['postal_code'] = $params['postal_code'];
		}
		
		if(isset($params['country'])) {
			if ($params['country'] == '') {
				$update_data['country'] = '';
			}
			else {
				// Make sure the country is in the proper format
				$country_id = $this->field_validation->ValidateCountry($params['country']);
				
				if(!$country_id) {
					die($this->response->Error(1007));
				}
				$update_data['country'] = $country_id;
			}
		}
		
		if(isset($params['state']) and !empty($params['state'])) {
			// If the country is US or Canada, we need to validate and supply the 2 letter abbreviation
			$this->load->helper('states_helper');
			$country_array = array(124,840);
			if(isset($country_id) and in_array($country_id, $country_array)) {
				$state = GetState($params['state']);
				if($state) {
					$update_data['state'] = $state;
				} else {
					die($this->response->Error(1012));
				}
			}
			else {
				$update_data['state'] = $params['state'];
			}
		}
		
		if(isset($params['phone'])) {
			$update_data['phone'] = $params['phone'];
		}
		
		if (isset($params['email'])) {
			$update_data['email'] = $params['email'];
		}
		
		if(!isset($update_data)) {
			die($this->response->Error(6003));
		}
		
		// Make sure they update their own customer
		$this->db->where('customer_id', $customer_id);
		
		if ($this->db->update('customers', $update_data)) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Delete a customer.
	*
	* Marks a customer as deleted.  Does not actually delete the customer from the database, but only marks it as deleted.
	*
	* @param int $customer_id The customer ID
	* 
	* @return bool TRUE upon success.
	*/
	function DeleteCustomer($customer_id)
	{	
		// Make sure they update their own customer
		$this->db->where('customer_id', $customer_id);
		
		$this->db->update('customers', array('active' => 0));
		
		// cancel all active subscriptions
		$this->db->where('customer_id', $customer_id);
		
		$this->db->update('subscriptions', array('active' => 0));
		
		return TRUE;
	}
	
	/**
	* Get a list of customer details.
	*
	* Searches the database for customers belonging to the client and returns an array with all the details.
	* All search parameters are optional.
	*
	* @param string $params['internal_id'] Customer's internal ID. Optional.
	* @param string $params['first_name'] Customer's first name. Optional.
	* @param string $params['last_name'] Customer's last name. Optional.
	* @param string $params['company'] Customer's company. Optional.
	* @param string $params['address_1'] Customer's address line 1. Optional.
	* @param string $params['address_2'] Customer's address line 2. Optional.
	* @param string $params['city'] Customer's city. Optional.
	* @param string $params['state'] Customer's state. Optional.
	* @param string $params['postal_code'] Customer's postal code. Optional. 
	* @param string $params['country'] Customer's country. Optional.
	* @param string $params['phone'] Customer's phone. Optional.
	* @param string $params['email'] Customer's email. Optional.
	* @param int $params['plan_id'] Filter by active plan. Optional.
	* @param int $params['deleted'] Set to 1 for deleted customers.  Optional.
	* @param string $pararms['sort'] Used to change the order of results returned.  Possible values are date, first_name, and last_name. Optional.
	* @param string $params['sort_dir'] Used only when a sort valus is padded.  Possible values are asc and desc
	* 
	* @return mixed Array containing the search results
	*/
	
	function GetCustomers($params, $any_status = FALSE)
	{		
		if(isset($params['deleted']) and $params['deleted'] == '1') {
			$this->db->where('customers.active', '0');
		}
		elseif ($any_status == FALSE) {
			$this->db->where('customers.active', '1');
		}
		
		// Check which search paramaters are set
		if(isset($params['first_name'])) {
			$this->db->where('first_name', $params['first_name']);
		}
		
		if(isset($params['internal_id'])) {
			$this->db->where('internal_id', $params['internal_id']);
		}
		
		if(isset($params['id'])) {
			$this->db->where('customers.customer_id', $params['id']);
		}
		
		if(isset($params['customer_id'])) {
			$this->db->where('customers.customer_id', $params['customer_id']);
		}
		
		if(isset($params['last_name'])) {
			$this->db->where('last_name', $params['last_name']);
		}
		
		if(isset($params['company'])) {
			$this->db->where('company', $params['company']);
		}
		
		if(isset($params['address_1'])) {
			$this->db->where('address_1', $params['address_1']);
		}
		
		if(isset($params['address_2'])) {
			$this->db->where('address_2', $params['address_2']);
		}
		
		if(isset($params['city'])) {
			$this->db->where('city', $params['city']);
		}
		
		if(isset($params['state'])) {
			$this->db->where('state', $params['state']);
		}
		
		if(isset($params['postal_code'])) {
			$this->db->where('postal_code', $params['postal_code']);
		}
		
		if(isset($params['country'])) {
			$this->db->where('country', $params['country']);
		}
		
		if(isset($params['phone'])) {
			$this->db->where('phone', $params['phone']);
		}
		
		if(isset($params['email'])) {
			$this->db->where('email', $params['email']);
		}
		
		if (isset($params['offset'])) {
			$offset = $params['offset'];
		}
		else {
			$offset = 0;
		}
		
		if(isset($params['limit'])) {
			$this->db->limit($params['limit'], $offset);
		}
		
		$params['sort'] = isset($params['sort']) ? $params['sort'] : '';
		
		switch($params['sort'])
		{
			case 'date':
				$sort = 'date_created';
				break;
			case 'first_name':
				$sort = 'first_name';
				break;
			case 'last_name':
				$sort = 'last_name';
				break;
			default:
				$sort = 'last_name';
				break;		
		}
		
		$params['sort_dir'] = isset($params['sort_dir']) ? $params['sort_dir'] : '';
		
		switch($params['sort_dir'])
		{
			case 'asc':
				$sort_dir = 'ASC';
				break;
			case 'desc':
				$sort_dir = 'DESC';
				break;
			default:
				$sort_dir = 'DESC';
				break;		
		}
		
		$this->db->order_by($sort, $sort_dir);	
		
		if (isset($params['active_recurring']) or isset($params['plan_id'])) {
			$this->db->join('subscriptions', 'customers.customer_id = subscriptions.customer_id', 'left');
		}
			
		if (isset($params['active_recurring'])) {
			if($params['active_recurring'] == 1) {
				$this->db->where('subscriptions.active', 1);
			} elseif($params['active_recurring'] === 0) {
				$this->db->where('subscriptions.active', 0);
			}
		}
		
		if (isset($params['plan_id'])) {
			$this->db->where('subscriptions.plan_id',$params['plan_id']);
			$this->db->where('subscriptions.active','1');
		}
		
		$this->db->select('customers.*');
		$this->db->select('countries.iso2');
		
		$this->db->join('countries', 'countries.country_id = customers.country', 'left');
		$this->db->group_by('customers.customer_id');
		$query = $this->db->get('customers');
		
		$data = array();
		if($query->num_rows() > 0) {
			$i=0;
			foreach($query->result() as $row) {
				
				$data[$i]['id'] = $row->customer_id;
				$data[$i]['internal_id'] = $row->internal_id;
				$data[$i]['first_name'] = $row->first_name;
				$data[$i]['last_name'] = $row->last_name;
				$data[$i]['company'] = $row->company;
				$data[$i]['address_1'] = $row->address_1;
				$data[$i]['address_2'] = $row->address_2;
				$data[$i]['city'] = $row->city;
				$data[$i]['state'] = $row->state;
				$data[$i]['postal_code'] = $row->postal_code;
				$data[$i]['country'] = $row->iso2;
				$data[$i]['email'] = $row->email;
				$data[$i]['phone'] = $row->phone;
				$data[$i]['date_created'] = local_time($row->date_created);
				$data[$i]['status'] = ($row->active == 1) ? 'active' : 'deleted';
				
				$plans = $this->GetPlansByCustomer($row->customer_id);
				if (!empty($plans)) {
					$n=0;
					foreach($plans as $plan) {
						$data[$i]['plans'][$n]['id'] = $plan['id'];
						$data[$i]['plans'][$n]['name'] = $plan['name'];
						$data[$i]['plans'][$n]['amount'] = $plan['amount'];
						$data[$i]['plans'][$n]['interval'] = $plan['interval'];
						$data[$i]['plans'][$n]['notification_url'] = $plan['notification_url'];
						$data[$i]['plans'][$n]['status'] = $plan['active'];
						$n++;
					}
				}
				
				$i++;
			}
		} else {
			return FALSE;
		}
		
		return $data;
	}
	
	/**
	* Get customer details.
	*
	* Searches the database for customers belonging to the client and with a specific customer_id.
	*
	* @param int $customer_id Customer ID.
	* 
	* @return array|bool Customer data or FALSE upon failure.
	*/
	
	function GetCustomer($customer_id)
	{	
		$params = array('customer_id' => $customer_id);
		
		$data = $this->GetCustomers($params, TRUE);
		
		if (!empty($data)) {
			return $data[0];
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Get plans by customer ID
	*
	* Gets all plans associated with the customer
	*
	* @param int $customer_id The ID of the Customer
	*
	* @return array All the plans, including status
	*/
	function GetPlansByCustomer ($customer_id) {
		$this->db->select('*');
		$this->db->select('subscriptions.amount AS sub_amount');
		$this->db->select('subscriptions.active AS sub_active');
		$this->db->where('customer_id',$customer_id);
		$this->db->join('plans','plans.plan_id = subscriptions.plan_id','INNER');
		$result = $this->db->get('subscriptions');
		
		$plans = array();
		
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row) {
				$plans[] = array(
								'id' => $row['plan_id'],
								'name' => $row['name'],
								'amount' => $row['sub_amount'],
								'interval' => $row['interval'],
								'notification_url' => $row['notification_url'],
								'active' => ($row['sub_active'] == '1') ? 'active' : 'inactive'
							);
			}
		}
		else {
			return FALSE;
		}
		
		return $plans;
	}
}