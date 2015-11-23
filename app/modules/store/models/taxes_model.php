<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Tax Rules Model 
*
* Contains all the methods used to create, update, and delete tax rules.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Taxes_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Record Tax
	*
	* @param int $tax_id
	* @param int $charge_id
	* @param float $product_tax (default: 0)
	* @param float $subscription_tax (default: 0)
	*
	* @return int $tax_received_id
	*/
	function record_tax ($tax_id, $charge_id, $product_tax = 0, $subscription_tax = 0) {
		$insert_fields = array(
							'tax_id' => $tax_id,
							'order_id' => $charge_id,
							'tax_received_for_products' => round((float)$product_tax,2),
							'tax_received_date' => date('Y-m-d H:i:s'),
							'tax_received_for_subscription' => round((float)$subscription_tax,2)
						);
						
		$this->db->insert('taxes_received', $insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Future Subscription Tax
	*
	* Record an amount which will be recorded as the tax on all future subscription payments
	*
	* @param int $subscription_id
	* @param int $tax_id
	* @param float $tax_amount
	*
	* @return int $future_sub_tax_id
	*/
	function future_subscription_tax ($subscription_id, $tax_id, $tax_amount) {
		$insert_fields = array(
							'tax_id' => $tax_id,
							'subscription_id' => $subscription_id,
							'tax_amount' => round($tax_amount,2)
						);
						
		$this->db->insert('future_sub_tax', $insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Get Tax for Subscription
	*
	* @param int $subscription_id
	*
	* @return array with keys: "tax_id", "tax_amount"
	*/
	function get_tax_for_subscription ($subscription_id) {
		// they key here is to be able to roll back to the first subscription in a series of subscriptions
		// if they are renews, we have the the "renewed" field
		// if they are updated CC's, we have the "updated" field
		
		// however, if we have a price that's the same as the plan, there's no tax
		$CI =& get_instance();
		$CI->load->model('billing/subscription_model');
		$subscription = $CI->subscription_model->get_subscription($subscription_id);
		
		if ((float)$subscription['amount'] == (float)$subscription['plan']['amount']) {
			// there's VERY likely no tax - the recurring rate == the plan rate
			return FALSE;
		}
		
		if ((float)$subscription['amount'] == 0) {
			return FALSE;
		}
		
		// there might be tax, let's go
		// find the very first subscription in the line of subscriptions
		$result = $this->db->select('subscription_id')
						   ->where('renewed',$subscription_id)
						   ->or_where('updated',$subscription_id)
						   ->get('subscriptions');
		if ($result->num_rows() == 0) {
			// this is the first subscription in the series (most common scenario)
		}
		else {
			while ($result->num_rows() > 0) {
				$subscription = $result->row_array();
				$subscription_id = $subscription['subscription_id'];
				
				$result = $this->db->select('subscription_id')
								   ->where('renewed',$subscription_id)
								   ->or_where('updated',$subscription_id)
								   ->get('subscriptions');
			}
			
			$subscription = $CI->subscription_model->get_subscription($subscription_id);
		}
		
		// now, $subscription holds the first sub in a series of subs
		// that means we can look to see what that first tax entry holds
		
		$result = $this->db->select('*')
						   ->where('subscription_id',$subscription_id)
						   ->from('future_sub_tax')->get();
		
		if ($result->num_rows() == 0) {
			// no tax info found
			return FALSE;
		}
		else {
			$tax = $result->row_array();
			
			// calculate the tax rate, too
			$tax_percentage = ($tax['tax_amount'] / $subscription['amount']) * 100;
			
			return array(
						'tax_id' => $tax['tax_id'],
						'tax_amount' => $tax['tax_amount'],
						'tax_percentage' => $tax_percentage
						);
		}
	}
	
	/**
	* New Tax
	*
	* @param string $name
	* @param float $percentage
	* @param int $state_id
	* @param int $country_id
	*
	* @return int $tax_id
	*/
	function new_tax ($name, $percentage, $state_id, $country_id) {
		$insert_fields = array(
								'tax_name' => $name,
								'tax_percentage' => $percentage,
								'state_id' => $state_id,
								'country_id' => $country_id,
								'tax_deleted' => '0'
							);
							
		$this->db->insert('taxes',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Update Tax
	*
	* @param int $tax_id
	* @param string $name
	* @param float $percentage
	* @param int $state_id
	* @param int $country_id
	*
	* @return boolean 
	*/
	function update_tax ($tax_id, $name, $percentage, $state_id, $country_id) {
		$update_fields = array(
								'tax_name' => $name,
								'tax_percentage' => $percentage,
								'state_id' => $state_id,
								'country_id' => $country_id
							);
							
		$this->db->update('taxes',$update_fields,array('tax_id' => $tax_id));
		
		return TRUE;
	}
	
	/**
	* Delete Tax
	*
	* @param int $tax_id
	*/
	function delete_tax ($tax_id) {
		$this->db->update('taxes',array('tax_deleted' => '1'), array('tax_id' => $tax_id));
		
		return TRUE;
	}
	
	/**
	* Get Tax
	*
	* @param int $tax_id
	*
	* @return array Array of data, else FALSE
	*/
	function get_tax ($tax_id) {
		$tax = $this->get_taxes(array('id' => $tax_id), TRUE);
		
		if (!empty($tax)) {
			return $tax[0];
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Get Taxes
	*
	* @param string $filters['state']
	* @param string $filters['country']
	* @param float $filters['percentage']
	* @param string $filters['name']
	* @param int $filters['id']
	*
	* @return array $taxes
	*/
	function get_taxes ($filters = array(), $any_status = FALSE) {
		$this->db->select('*');
		$this->db->select('countries.name AS country_name');
	
		if (isset($filters['id'])) {
			$this->db->where('tax_id',$filters['id']);
		}
		if (isset($filters['state'])) {
			$this->db->where('states.name_long',$filters['state']);
		}
		if (isset($filters['country'])) {
			$this->db->where('countries.name',$filters['country']);
		}
		if (isset($filters['percentage'])) {
			$this->db->like('tax_percentage',$filters['percentage']);
		}
		if (isset($filters['name'])) {
			$this->db->like('tax_name',$filters['name']);
		}
		
		$this->db->join('states','states.state_id = taxes.state_id','LEFT');
		$this->db->join('countries','countries.country_id = taxes.country_id','LEFT');
		
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'tax_id';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		if ($any_status == FALSE) {
			$this->db->where('tax_deleted','0');
		}
		$result = $this->db->get('taxes');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		else {
			$taxes = array();
			foreach ($result->result_array() as $tax) {
				$taxes[] = array(
									'id' => $tax['tax_id'],
									'name' => $tax['tax_name'],
									'state_id' => $tax['state_id'],
									'country_id' => $tax['country_id'],
									'state' => $tax['name_long'],
									'country' => $tax['country_name'],
									'country_iso2' => $tax['iso2'],
									'state_code' => $tax['name_short'],
									'percentage' => $tax['tax_percentage']
									);
									
			}
			
			return $taxes;
		}
	}
	
	/**
	* Get Paid Tax
	*
	* @param int $paid_tax_id
	*
	* @return array 
	*/
	function get_paid_tax ($paid_tax_id) {
		$tax = $this->get_paid_taxes(array('id' => $paid_tax_id));
		
		if (empty($tax)) {
			return FALSE;
		}
		
		return $tax[0];
	}
	
	/**
	* Get Paid Taxes
	* 
	* @param int $filters['id']
	* @param int $filters['tax']
	* @param int $filters['invoice_id']
	* @param string $filters['member_name']
	* @param date $filters['date_start']
	* @param date $filters['date_end']
	* @param string $filters['sort']
	* @param string $filters['sort_dir']
	* @param int $filters['limit']
	* @param int $filters['offset']
	*
	* @return array 
	*/
	function get_paid_taxes ($filters = array()) {
		if (isset($filters['tax'])) {
			$this->db->like('taxes.tax_id',$filters['tax']);
		}
		
		if (isset($filters['id'])) {
			$this->db->where('taxes_received.tax_received_id',$filters['id']);
		}
		
		if (isset($filters['invoice_id'])) {
			$this->db->where('orders.order_id',$filters['invoice_id']);
		}
		
		if (isset($filters['member_name'])) {
			$this->db->like('users.user_last_name',$filters['member_name']);
		}
		
		if (isset($filters['date_start'])) {
			$date = date('Y-m-d H:i:s', strtotime($filters['date_start']));
			$this->db->where('tax_received_date >=', $date);
		}
		
		if (isset($filters['date_end'])) {
			$date = date('Y-m-d H:i:s', strtotime($filters['date_end']));
			$this->db->where('tax_received_date <=', $date);
		}
		
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'tax_received_date';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'DESC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		$this->db->where('orders.refunded','0');
		$this->db->where('orders.status','1');
		
		$this->db->join('orders','orders.order_id = taxes_received.order_id','inner');
		$this->db->join('customers','customers.customer_id = orders.customer_id','inner');
		$this->db->join('users','users.user_id = customers.internal_id','inner');
		$this->db->join('taxes','taxes.tax_id = taxes_received.tax_id','inner');
		
		$result = $this->db->get('taxes_received');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		else {
			$taxes = array();
			
			foreach ($result->result_array() as $payment) {
				$taxes[] = array(
									'id' => $payment['tax_received_id'],
									'invoice_id' => $payment['order_id'],
									'tax_id' => $payment['tax_id'],
									'tax_name' => $payment['tax_name'],
									'tax_rate' => $payment['tax_percentage'],
									'amount' => money_format("%!^i",($payment['tax_received_for_products'] + $payment['tax_received_for_subscription'])),
									'user_id' => $payment['user_id'],
									'user_first_name' => $payment['user_first_name'],
									'user_last_name' => $payment['user_last_name'],
									'user_email' => $payment['user_email'],
									'date' => $payment['tax_received_date']
									);
									
			}
			
			return $taxes;
		}
	}
	
	/**
	* Get Taxes Paid Total
	*
	* @param int $filters['tax']
	* @param int $filters['invoice_id']
	* @param string $filters['member_name']
	* @param date $filters['date_start']
	* @param date $filters['date_end']
	* @param string $filters['sort']
	* @param string $filters['sort_dir']
	* @param int $filters['limit']
	* @param int $filters['offset']
	*
	* @return float  
	*/
	function get_paid_taxes_total ($filters = array()) {
		if (isset($filters['tax'])) {
			$this->db->like('taxes.tax_id',$filters['tax']);
		}
		
		if (isset($filters['id'])) {
			$this->db->where('taxes_received.tax_received_id',$filters['id']);
		}
		
		if (isset($filters['invoice_id'])) {
			$this->db->where('orders.order_id',$filters['invoice_id']);
		}
		
		if (isset($filters['member_name'])) {
			$this->db->like('users.user_last_name',$filters['member_name']);
		}
		
		if (isset($filters['date_start'])) {
			$date = date('Y-m-d H:i:s', strtotime($filters['date_start']));
			$this->db->where('tax_received_date >=', $date);
		}
		
		if (isset($filters['date_end'])) {
			$date = date('Y-m-d H:i:s', strtotime($filters['date_end']));
			$this->db->where('tax_received_date <=', $date);
		}
		
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'tax_received_date';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'DESC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		$this->db->where('orders.refunded','0');
		$this->db->where('orders.status','1');
		
		$this->db->join('orders','orders.order_id = taxes_received.order_id','inner');
		$this->db->join('customers','customers.customer_id = orders.customer_id','inner');
		$this->db->join('users','users.user_id = customers.internal_id','inner');
		$this->db->join('taxes','taxes.tax_id = taxes_received.tax_id','inner');
		
		$result = $this->db->get('taxes_received');
		
		if ($result->num_rows() == 0) {
			return 0;
		}
		else {
			$total = 0;
			
			foreach ($result->result_array() as $payment) {
				$total += ($payment['tax_received_for_products'] + $payment['tax_received_for_subscription']);
									
			}
			
			return (float)$total;
		}
	}
}