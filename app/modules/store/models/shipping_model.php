<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Shipping Rates Model 
*
* Contains all the methods used to create, update, and delete shipping rates.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Shipping_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Record Paid Shipping
	*
	* @param int $charge_id
	* @param int $shipping_id
	* @param float $shipping_charge
	* @param string $shipping_name
	*
	* @return boolean 
	*/
	function record_shipping ($charge_id, $shipping_id, $shipping_charge, $shipping_name = null) {
		$insert_fields = array(
							'order_id' => $charge_id,
							'shipping_id' => $shipping_id,
							'shipping_received_amount' => $shipping_charge
						);
						
		/*
			Are we using a dynamic shipping module? If so, we'll need
			to store it's sub_type so we can display it again later.
		*/
		if (!empty($shipping_name))
		{
			$insert_fields['shipping_desc'] = $shipping_name;
		}
						
		$this->db->insert('shipping_received', $insert_fields);
		
		return TRUE;
	}
	
	/**
	* New Shipping
	*
	* @param string $name
	* @param string $type ("weight", "product", "flat")
	* @param float $rate
	* @param int $state_id
	* @param int $country_id
	* @param boolean $taxable (default: FALSE)
	* @param float $max_weight (default: FALSE)
	*
	* @return int $rate_id
	*/
	function new_rate ($name, $type, $rate, $state_id, $country_id, $taxable = FALSE, $max_weight = FALSE) {
		$insert_fields = array(
								'shipping_name' => $name,
								'shipping_rate_type' => $type,
								'shipping_rate' => $rate,
								'state_id' => $state_id,
								'country_id' => $country_id,
								'shipping_is_taxable' => (empty($taxable)) ? '0' : '1',
								'shipping_deleted' => '0',
								'shipping_max_weight' => $max_weight
							);
							
		$this->db->insert('shipping',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Update Shipping
	*
	* @param int $rate_id
	* @param string $name
	* @param string $type ("weight", "product", "flat")
	* @param float $rate
	* @param int $state_id
	* @param int $country_id
	* @param boolean $taxable (default: FALSE)
	* @param float $max_weight (default: FALSE)
	*
	* @return int $rate_id
	*/
	function update_rate ($rate_id, $name, $type, $rate, $state_id, $country_id, $taxable = FALSE, $max_weight = FALSE) {
		$update_fields = array(
								'shipping_name' => $name,
								'shipping_rate_type' => $type,
								'shipping_rate' => $rate,
								'state_id' => $state_id,
								'country_id' => $country_id,
								'shipping_is_taxable' => (empty($taxable)) ? '0' : '1',
								'shipping_max_weight' => $max_weight
							);
							
		$this->db->update('shipping',$update_fields,array('shipping_id' => $rate_id));
		
		return TRUE;
	}
	
	/**
	* Delete Rate
	*
	* @param int $rate_id
	*
	* @return boolean 
	*/
	function delete_rate ($rate_id) {
		$this->db->update('shipping',array('shipping_deleted' => '1'), array('shipping_id' => $rate_id));
		
		return TRUE;
	}
	
	/**
	* Get Shipping
	*
	* @param int $rate_id
	*
	* @return array Array of data, else FALSE
	*/
	function get_rate ($rate_id) {
		$rate = $this->get_rates(array('id' => $rate_id), TRUE);
		
		if (!empty($rate)) {
			return $rate[0];
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Get Rates for Address
	*
	* Takes a shipping address array and returns possible shipping rates, else the default rate
	*
	* @param array $cart
	* @param array $shipping_address Created in the billing_shipping phase of checkout.
	*
	* @return array $rates The array of rates, else FALSE if we can't ship there
	*/
	function get_rates_for_address ($cart, $shipping_address) {
		// can we ship to this country?
		
		if (setting('shipping_available_countries') != '' and !in_array($shipping_address['country'], unserialize(setting('shipping_available_countries')))) {
			// this isn't an available country, and we don't ship worldwide
			return FALSE;
		}
		
		// we'll store available rates here
		$available_rates = array();
		
		// find matching shipping rates at the start
		$rates = $this->get_rates();

		// tally weight
		$weight = 0;
		foreach ($cart as $item) {
			if ($item['is_subscription'] == FALSE) {
				$weight = $weight + ($item['qty'] * $item['weight']);
			}
		}
		
		if (!empty($rates)) {
			foreach ($rates as $rate) {
				// check for max weight
				if (!empty($rate['max_weight']) and (float)$weight > (float)$rate['max_weight']){
					continue;
				}
				
				// Get any types we might have in a shipping module
				if ($this->config->item('shipping_module'))
				{
					$shipping_module = $this->config->item('shipping_module');
					
					$this->load->library($shipping_module .'/'. $shipping_module);
					
					$types = $this->$shipping_module->get_types();
				}
				
				if ($rate['state_code'] == $shipping_address['state'] or $rate['country_iso2'] == $shipping_address['country']) {
					// calculate total_rate based on cart
					if ($rate['type'] == 'flat') {
						$rate['total_rate'] = $rate['rate'];
					}
					elseif ($rate['type'] == 'product') {
						// tally quantity
						$quantity = 0;
						foreach ($cart as $item) {
							if ($item['is_subscription'] == FALSE) {
								$quantity += $item['qty'];
							}
						}
						
						$rate['total_rate'] = money_format("%!^i",$quantity * $rate['rate']);
					}
					elseif ($rate['type'] == 'weight') {
						// tally weight
						// $weight tallied above
						
						$rate['total_rate'] = money_format("%!^i",$weight * $rate['rate']);
					}
					else if (isset($types) && is_array($types) && isset($types[$rate['type']]))
					{
						$my_rates = $this->$shipping_module->get_rates_for_address($rate['type'], $cart, $shipping_address);
	
						if (is_array($my_rates))
						{
							// We'll cheat here and add the new rates as copies of the current 
							foreach ($my_rates as $name => $value)
							{
								$data = $rate;
								$data['sub_type']	= $value['type_name'];
								$data['name']		= $name;
								$data['total_rate'] = money_format("%!^i", $value['cost']);
								
								$available_rates[] = $data;
								$preset = true;
							}
						}
						else
						{
							// An error? 
							if (!empty($this->shipping_model->error))
							{
								$error = $this->shipping_model->error;
								log_message($error, 'error');
							}
						}
					}
					else {
						die(show_error('Shipping rate, "' . $rate['name'] . '", has an improperly configured rate type.'));
					}
					
					if (!isset($preset))
					{
						$available_rates[$rate['id']] = $rate;
					}
				}
			}
		}
		
		//die('<pre>'. print_r($available_rates, true));
			
		// no rates?  show the default
		if (empty($available_rates)) {
			$rate_type = setting('shipping_default_type');
			$rate = setting('shipping_default_rate');
			
			if ($rate_type == 'flat') {
				$total_rate = $rate;
			}
			elseif ($rate_type == 'product') {
				// tally quantity
				$quantity = 0;
				foreach ($cart as $item) {
					if ($item['is_subscription'] == FALSE) {
						$quantity += $item['qty'];
					}
				}
				
				$total_rate = money_format("%!^i",$quantity * $rate);
			}
			elseif ($rate_type == 'weight') {
				// tally weight
				$weight = 0;
				foreach ($cart as $item) {
					if ($item['is_subscription'] == FALSE) {
						$weight = $weight + ($item['qty'] * $item['weight']);
					}
				}
				
				$total_rate = money_format("%!^i",$weight * $rate);
			}
			else if ($this->config->item('shipping_module'))
			{
				// Get any types we might have in a shipping module
				$shipping_module = $this->config->item('shipping_module');
				$this->load->library($shipping_module .'/'. $shipping_module);
				$types = $this->$shipping_module->get_types();
				
				if (!is_array($types) || !isset($types[$rate_type]))
				{
					break;
				}
			
				$my_rates = $this->$shipping_module->get_rates_for_address($rate_type, $cart, $shipping_address);
				
				if (is_array($my_rates))
				{
					// We'll cheat here and add the new rates as copies of the current 
					foreach ($my_rates as $name => $value)
					{
						$data = array(
							'type'			=> $rate_type,
							'sub_type'		=> $value['type_name'],
							'name'			=> $name,
							'total_rate' 	=> money_format("%!^i", $value['cost'])
						);
						
						$available_rates['0'] = $data;
						$preset = true;
					}
				}
			}
			
			if (!isset($preset))
			$available_rates['0'] = array(
										'name' => 'Default',
										'total_rate' => $total_rate,
										'rate' => $rate,
										'type' => $rate_type
									);
		}

		uasort($available_rates, array($this, '_sort_rates_array'));
		
		return $available_rates;
	}
	
	private function _sort_rates_array ($a, $b) {
		if (!isset($a['total_rate']) || !isset($b['total_rate']))
		{
			return 0;
		}
	
		if ($a['total_rate'] < $b['total_rate']) {
			return -1;
		}
		elseif ($a['total_rate'] > $b['total_rate']) {
			return +1;
		}
		else {
			return 0;
		}
	}
	
	/**
	* Get Shipping Rates
	*
	* @param int $filters['id']
	* @param string $filters['state']
	* @param string $filters['country']
	* @param string $filters['name']
	*
	* @return array $rates
	*/
	function get_rates ($filters = array(), $any_status = FALSE) {
		$this->db->select('*');
		$this->db->select('countries.name AS country_name');
	
		if (isset($filters['id'])) {
			$this->db->where('shipping_id',$filters['id']);
		}
		if (isset($filters['state'])) {
			$this->db->where('states.name_long',$filters['state']);
		}
		if (isset($filters['country'])) {
			$this->db->where('countries.name',$filters['country']);
		}
		if (isset($filters['name'])) {
			$this->db->like('shipping_name',$filters['name']);
		}
		
		$this->db->join('states','states.state_id = shipping.state_id','LEFT');
		$this->db->join('countries','countries.country_id = shipping.country_id','LEFT');
		
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'shipping_name';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		if ($any_status == FALSE) {
			$this->db->where('shipping_deleted','0');
		}
		$result = $this->db->get('shipping');

		if ($result->num_rows() == 0) {
			return FALSE;
		}
		else {
			$shipping = array();
			foreach ($result->result_array() as $rate) {
				$shipping[] = array(
									'id' => $rate['shipping_id'],
									'name' => $rate['shipping_name'],
									'state_id' => $rate['state_id'],
									'country_id' => $rate['country_id'],
									'state' => $rate['name_long'],
									'country' => $rate['country_name'],
									'country_iso2' => $rate['iso2'],
									'state_code' => $rate['name_short'],
									'type' => $rate['shipping_rate_type'],
									'rate' => money_format("%!^i",$rate['shipping_rate']),
									'taxable' => (!empty($rate['shipping_is_taxable'])) ? TRUE : FALSE,
									'max_weight' => (!empty($rate['shipping_max_weight'])) ? $rate['shipping_max_weight'] : FALSE 
									);
									
			}
			
			return $shipping;
		}
	}
}