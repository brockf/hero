<?php
/**
* Order Authorization Model 
*
* Contains all the methods used to save and retrieve order authorization details.
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework

*/
class Order_authorization_model extends CI_Model
{
	private $CI;
	
	function Order_authorization_model()
	{
		parent::__construct();
		
		$this->CI =& get_instance();
		$this->CI->load->library('billing/transaction_log');
	}
	
	/**
	* Save order authorization details.
	*
	* Save the order authorization number returned from the payment gateway
	*
	* @param int $order_id The order ID
	* @param string $tran_id Transaction ID. Optional
	* @param string $authorization_code Authorization code. Optional
	*
	*/
	function SaveAuthorization($order_id, $tran_id = '', $authorization_code = '', $security_key = '')
	{
		$insert_data = array(
							'order_id' => (!empty($order_id)) ? $order_id : '',
							'tran_id'	=> (!empty($tran_id)) ? $tran_id : '',
							'authorization_code' => (!empty($authorization_code)) ? $authorization_code : '',
							'security_key' => (!empty($security_key)) ? $security_key : ''
							);
		
		$this->db->insert('order_authorizations', $insert_data);
		
		$this->CI->transaction_log->log_event($order_id, FALSE, 'authorization_saved', $insert_data, __FILE__, __LINE__);
		
		return TRUE;
	}
	
	/**
	* Get Authorization Details.
	*
	* Gets the authorization details for an order_id
	*
	* @param int $order_id The order ID
	*
	* @return mixed Array containg authorization details
	*/
	
	function GetAuthorization($order_id)
	{
		$this->db->where('order_id', $order_id);
		$query = $this->db->get('order_authorizations');
		if($query->num_rows() > 0) {
			return $query->row();
		} else {
			return FALSE;
		}
	}
}