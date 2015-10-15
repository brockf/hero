<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Charge Data Model
*
* Stores miscellaneous data relating to a charge (e.g., "return URL")
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework
*
*/
class Charge_data_model extends CI_Model
{
	function Charge_data_model()
	{
		parent::__construct();
	}
	
	/**
	* Save charge data
	*
	* Saves a piece of charge data
	*
	* @param int $charge_id The charge ID
	* @param string $key
	* @param string $value
	*
	*/
	
	function Save($order_id, $key, $value)
	{
		$insert_data = array(
							'order_id' => $order_id,
							'order_data_key' => $key,
							'order_data_value' => $value
							);
		
		$this->db->insert('order_data', $insert_data);
	}
	
	/**
	* Get charge data
	*
	* Gets all the data for a charge
	*
	* @param int $charge The order ID
	*
	* @return mixed Array containg authorization details
	*/
	
	function Get ($order_id)
	{
		$this->db->where('order_id', $order_id);
		$query = $this->db->get('order_data');
		if($query->num_rows() > 0) {
			$return = array();
			foreach ($query->result_array() as $row) {
				$return[$row['order_data_key']] = $row['order_data_value'];
			}
			return $return;
		} else {
			return FALSE;
		}
	}
	
	/**
	* Delete Order Data
	*
	* @param int $order_id
	*
	* @return void
	*/
	function Delete ($order_id) {
		$this->db->delete('order_data', array('order_id' => $order_id));
	}
}