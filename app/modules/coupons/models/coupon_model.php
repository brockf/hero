<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Coupon Model 
*
* Contains all the methods used to create, update, and delete coupons.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Coupon_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
	}
	
	//--------------------------------------------------------------------
	
	/**
	* Has Coupons?
	*
	* Does the site use any coupons?
	*
	* @return boolean
	*/
	public function has_coupons() {
		$this->db->select('coupon_id')
				 ->where('coupon_deleted','0')
				 ->where('coupon_end_date >=',date('Y-m-d'));
		$result = $this->db->get('coupons');
		
		if ($result->num_rows() > 0) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Count Uses
	*
	* @param $coupon_id
	*
	* @return int $number_of_uses
	*/
	function count_uses ($coupon_id) {
		$result = $this->db->select('subscription_id')
			               ->from('subscriptions')
			               ->where('coupon_id',$coupon_id)
			               ->get();
			               
			               
		$subscriptions = $result->num_rows();
		
		$result = $this->db->select('order_details_id')
			               ->from('order_details')
			               ->where('coupon_id',$coupon_id)
			               ->get();
			               
			               
		$orders = $result->num_rows();
		
		return (int)$subscriptions + $orders;
	}
	
	/**
	* Customer Usage
	*
	* @param int $coupon_id
	* @param int $customer_id
	*
	* @return int $number_of_uses
	*/
	function customer_usage ($coupon_id, $customer_id) {
		$result = $this->db->select('subscription_id')
			               ->from('subscriptions')
			               ->where('coupon_id',$coupon_id)
			               ->where('customer_id',$customer_id)
			               ->get();
			               
			               
		$subscriptions = $result->num_rows();
		
		$result = $this->db->select('order_details_id')
			               ->from('order_details')
			               ->where('coupon_id',$coupon_id)
			               ->where('customer_id',$customer_id)
			               ->get();
			               
			               
		$orders = $result->num_rows();
		
		return (int)$subscriptions + $orders;
	}
	
	/**
	 * Get a single coupon.
	 *
	 * @param int $id The coupon_id to find.
	 *
	 * @return array The coupon details, or FALSE on coupon not found.
	 */
	public function get_coupon ($id) 
	{
		$this->db->where('coupon_deleted', 0);
		$this->db->where('coupon_id', $id);
		$query = $this->db->get('coupons');
		
		if ($query->num_rows())
		{
			$coupon = $query->row_array();
			
			// Get any associated products
			$coupon['products'] = $this->get_related($id, 'coupons_products', 'product_id');
			
			// Get associated subscription plans
			$coupon['plans'] = $this->get_related($id, 'coupons_subscriptions', 'subscription_plan_id');
			
			// Get associated shipping
			$coupon['shipping'] = $this->get_related($id, 'coupons_shipping', 'shipping_id');
		
			return $coupon;
		}
	}
	
	//--------------------------------------------------------------------
	
	/**
	* Count Coupons
	*/
	function count_coupons ($filters = array()) {
		return $this->get_coupons($filters, TRUE);
	}
	
	/**
	* Get Coupon Usages
	*
	* @param $filters['code']
	*
	* @return array
	*/
	function get_coupon_usages ($filters = array()) {
		if (isset($filters['code'])) {
			$this->db->where('coupon_code', $filters['code']);
		}
		
		if (isset($filters['code_search'])) {
			$this->db->like('coupon_code', $filters['code_search']);
		}
		
		$this->db->select('coupons.*');
		
		$this->db->select('COUNT(`order_details`.`coupon_id`) AS `order_usages`');
		$this->db->select('COUNT(`subscriptions`.`coupon_id`) AS `subscription_usages`');
		
		$this->db->join('order_details','order_details.coupon_id = coupons.coupon_id','left');
		$this->db->join('subscriptions','coupons.coupon_id = subscriptions.coupon_id','left');
	
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'order_usages';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'DESC';
		
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		$this->db->group_by('coupons.coupon_id');
		
		$this->db->from('coupons');

		$result = $this->db->get();
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$coupons = array();
		
		foreach ($result->result_array() as $coupon) {
			$coupons[] = array(
				'id'				=> $coupon['coupon_id'],
				'order_usages'		=> $coupon['order_usages'],
				'subscription_usages'	=> $coupon['subscription_usages'],
				'total_usages'		=> (int)($coupon['order_usages'] + $coupon['subscription_usages']),
				'type_id'			=> $coupon['coupon_type_id'],
				'name'				=> $coupon['coupon_name'],
				'code'				=> $coupon['coupon_code'],
				'start_date'		=> $coupon['coupon_start_date'],
				'end_date'			=> $coupon['coupon_end_date'],
				'max_uses'			=> $coupon['coupon_max_uses'],
				'customer_limit'	=> $coupon['coupon_customer_limit'],
				'reduction_type'	=> $coupon['coupon_reduction_type'],
				'reduction_amt'		=> $coupon['coupon_reduction_amt'],
				'trial_length'		=> $coupon['coupon_trial_length'],
				'min_cart_amt'		=> $coupon['coupon_min_cart_amt']
			);
		}
		
		return $coupons;
	}
	
	
	/**
	 * Get a list of coupons
	 *
	 * @param int $filters['id'] Coupon ID
	 * @param string $filters['name'] The name of the coupon
	 * @param string $filters['code'] The coupon code (entered at checkout)
	 * @param string $filters['code_search'] (like above, with LIKE)
	 * @param date $filters['start_date'] Start date of coupon must be after or equal to this date
	 * @param date $filters['end_date'] Start date of coupon must be before or equal to this date
	 * @param int $filters['type'] Type of coupon (1 = Price Reduction, 2 = Free Trial, 3 = Free Shipping)
	 *
	 * @return array The coupons that match the filters.
	 */
	public function get_coupons ($filters=array(), $counting = FALSE) 
	{
	
		//--------------------------------------------------------------------
		// setup filters
		//--------------------------------------------------------------------
		
		// ID	
		if (isset($filters['id']))
		{
			$this->db->where('coupon_id',$filters['id']);
		}
		
		// Name
		if (isset($filters['name']))
		{
			$this->db->like('coupon_name', $filters['name']);
		}
		
		// Code
		if (isset($filters['code']) && !empty($filters['code']))
		{
			// not a LIKE, this must be exact!
			$this->db->where('coupon_code', $filters['code']);
		}
		
		if (isset($filters['code_search']) && !empty($filters['code_search']))
		{
			$this->db->like('coupon_code', $filters['code_search']);
		}
		
		// Start Date
		if (isset($filters['start_date']))
		{
			$this->db->where('coupon_start_date >=', $filters['start_date'] );
		}
		// End Date
		if (isset($filters['end_date']))
		{
			$this->db->where('coupon_start_date <=', $filters['end_date'] );
		}
		
		// Reduction Type
		if (isset($filters['type']))
		{
			$this->db->where('coupon_type_id', $filters['type'] );
		}
		
		$this->db->where('coupon_deleted', 0);
		
		if ($counting == FALSE) {
			// standard ordering and limiting
			$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'coupons.coupon_id';
			$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'DESC';
			
			$this->db->order_by($order_by, $order_dir);
			
			if (isset($filters['limit'])) {
				$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
				$this->db->limit($filters['limit'], $offset);
			}
			
			$this->db->from('coupons');
			
			$result = $this->db->get();
		}
		else {
			$this->db->select('COUNT(coupons.coupon_id) AS `counted`');
			
			$result = $this->db->get('coupons');
			$count = $result->row_array();
			return $count['counted'];
		}
		
		if ($result->num_rows() === 0)
		{
			return FALSE;
		}

		$coupons = array();
		foreach ($result->result_array() as $row)
		{ 
			$coupons[] = array(
				'id'				=> $row['coupon_id'],
				'type_id'			=> $row['coupon_type_id'],
				'name'				=> $row['coupon_name'],
				'code'				=> $row['coupon_code'],
				'start_date'		=> $row['coupon_start_date'],
				'end_date'			=> $row['coupon_end_date'],
				'max_uses'			=> $row['coupon_max_uses'],
				'customer_limit'	=> $row['coupon_customer_limit'],
				'reduction_type'	=> $row['coupon_reduction_type'],
				'reduction_amt'		=> $row['coupon_reduction_amt'],
				'trial_length'		=> $row['coupon_trial_length'],
				'min_cart_amt'		=> $row['coupon_min_cart_amt']
			);
		}

		return $coupons;
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Returns an object containing all of the coupon types in the system.
	 *
	 * @return object The coupon types in system, or FALSE if none exist.
	 */
	public function get_coupon_types () 
	{
		$query = $this->db->get('coupon_types');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		
		return FALSE;
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Validates POST data to be appropriate for a coupon.
	 *
	 * @param boolean $editing Whether in editing/new mode.
	 *
	 * @return boolean	Whether it was successful or not.
	 */
	public function validation ($editing=TRUE) 
	{
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('coupon_name', 'Coupon Name', 'trim|required|max_length[60]');
		$this->form_validation->set_rules('coupon_code', 'Coupon Code', 'trim|required|mx_length[20]');
		$this->form_validation->set_rules('coupon_start_date', 'Start Date', 'trim|required');
		$this->form_validation->set_rules('coupon_end_date', 'End Date', 'trim|required');
		$this->form_validation->set_rules('coupon_max_uses', 'Maximum Uses', 'trim|is_natural');
		$this->form_validation->set_rules('coupon_type_id', 'Coupon Type', 'trim|is_natural');
		
		switch ($this->input->post('coupon_type_id'))
		{
			// Price Reduction
			case 1: 
				$this->form_validation->set_rules('coupon_reduction_amt', 'Reduction Amount', 'trim|required|numeric');
				break;
			// Free Trial
			case 2: 
				$this->form_validation->set_rules('coupon_trial_length', 'Free Trial Length', 'trim|required|is_natural');
				$this->form_validation->set_rules('trial_subs[]', 'Subscription Plans', 'required');
				break;			
			// Free Shipping
			case 3:
				$this->form_validation->set_rules('coupon_min_cart_amt', 'Min. Cart Amount', 'trim|required|numeric');
				$this->form_validation->set_rules('ship_rates[]', 'Shipping Methods', 'required');
				break;
		}
		
		
		if ($this->form_validation->run() == FALSE) {
			$errors = rtrim(validation_errors('','||'),'|');
			$errors = explode('||',str_replace('<p>','',$errors));
			return $errors;
		}
		
		return TRUE;
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Creates a new coupon in the database.
	 *
	 * @param string $name The coupon name
	 * @param string $code The coupon code
	 * @param string $start_date The first day that the coupon can be used (ie YYYY-MM-DD)
	 * @param string $end_date The last day the coupon can be used
	 * @param int $max_uses The maximum number of times the coupon can be used
	 * @param bool $customer_limit Whether or not to restrict the customer to a single use
	 * @param int $type_id The type of coupon (Price reduction, free trial or free subscription)
	 * @param int $reduction_type Whether percent or fixed amount of reduction. Only applicable for Price Reductions.
	 * @param string $reduction_amt How much to reduce price by. Only applicable for Price Reduction.
	 * @param int $trial_length How many days the free trial will last. 
	 * @param string $min_amt The minimum amount in the cart before they qualify for free shipping.
	 * @param array $products An array of product ids to assign this coupon to.
	 * @param array $plans An array of subscription plans to assign this coupon to.
	 * @param array $ship_rates An array of shipping rates to apply this coupon to.
	 *
	 * @return int The id of the new coupon, or FALSE on failure.
	 */
	public function new_coupon ($name, $code, $start_date, $end_date, $max_uses, $customer_limit, $type_id, $reduction_type, $reduction_amt, $trial_length, $min_amt, $products, $plans, $ship_rates) 
	{		
		$insert_fields = array(
							'coupon_name'			=> $name,
							'coupon_code'			=> $code,
							'coupon_start_date'		=> $start_date,
							'coupon_end_date'		=> $end_date,
							'coupon_max_uses'		=> $max_uses,
							'coupon_customer_limit'	=> $customer_limit,
							'coupon_type_id'		=> $type_id,
							'coupon_reduction_type'	=> $reduction_type,
							'coupon_reduction_amt'	=> $reduction_amt,
							'coupon_trial_length'	=> $trial_length,
							'coupon_min_cart_amt'	=> $min_amt,
						);
		
		// Add the created_on field
		$insert_fields['created_on'] = date('Y-m-d H:i:s');
		
		// Now, time to try saving the coupon itself
		$this->db->insert('coupons', $insert_fields);
		
		$id = $this->db->insert_id();
		
		if (is_numeric($id))
		{
			// Save was successfull, so try to save our various associated parts.
			if (!empty($products))		{ $this->save_related($id, 'coupons_products', 'product_id', $products); }
			if (!empty($plans))			{ $this->save_related($id, 'coupons_subscriptions', 'subscription_plan_id', $plans); }
			if (!empty($ship_rates))	{ $this->save_related($id, 'coupons_shipping', 'shipping_id', $ship_rates); }
			
			return $id;
		}
		
		return FALSE;
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Updates an existing coupon in the database.
	 *
	 * @param int $coupon_id The id of the coupon to update
	 * @param string $name The coupon name
	 * @param string $code The coupon code
	 * @param string $start_date The first day that the coupon can be used (ie YYYY-MM-DD)
	 * @param string $end_date The last day the coupon can be used
	 * @param int $max_uses The maximum number of times the coupon can be used
	 * @param bool $customer_limit Whether or not to restrict the customer to a single use
	 * @param int $type_id The type of coupon (Price reduction, free trial or free subscription)
	 * @param int $reduction_type Whether percent or fixed amount of reduction. Only applicable for Price Reductions.
	 * @param string $reduction_amt How much to reduce price by. Only applicable for Price Reduction.
	 * @param int $trial_length How many days the free trial will last. 
	 * @param string $min_amt The minimum amount in the cart before they qualify for free shipping.
	 * @param array $products An array of product ids to assign this coupon to.
	 * @param array $plans An array of subscription plans to assign this coupon to.
	 * @param array $ship_rates An array of shipping rates to apply this coupon to.
	 *
	 * @return boolean 
	 */
	public function update_coupon($coupon_id, $name, $code, $start_date, $end_date, $max_uses, $customer_limit, $type_id, $reduction_type, $reduction_amt, $trial_length, $min_amt, $products, $plans, $ship_rates) 
	{		
		$insert_fields = array(
							'coupon_name'			=> $name,
							'coupon_code'			=> $code,
							'coupon_start_date'		=> $start_date,
							'coupon_end_date'		=> $end_date,
							'coupon_max_uses'		=> $max_uses,
							'coupon_customer_limit'	=> $customer_limit,
							'coupon_type_id'		=> $type_id,
							'coupon_reduction_type'	=> $reduction_type,
							'coupon_reduction_amt'	=> $reduction_amt,
							'coupon_trial_length'	=> $trial_length,
							'coupon_min_cart_amt'	=> $min_amt,
						);
		
		// Add the created_on field
		$insert_fields['created_on'] = date('Y-m-d H:i:s');
		
		// Now, time to try saving the coupon itself
		$this->db->where('coupon_id', $coupon_id);
		$this->db->update('coupons', $insert_fields);
		
		if ($this->db->affected_rows())
		{
			// Save was successfull, so try to save our various associated parts.
			$this->save_related($coupon_id, 'coupons_products', 'product_id', $products);
			$this->save_related($coupon_id, 'coupons_subscriptions', 'subscription_plan_id', $plans);
			$this->save_related($coupon_id, 'coupons_shipping', 'shipping_id', $ship_rates);
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	//--------------------------------------------------------------------
	
	
	/**
	 * Sets the deleted flag on a coupon (basically, saves it for the 'recycle bin')
	 *
	 * @param int $id The id of the coupon to delete.
	 *
	 * @return bool Whether the coupon is successfully 'deleted' or not.
	 */
	public function delete_coupon ($id=null) 
	{
		if (!is_numeric($id))
		{
			return FALSE;
		}
		
		$this->db->set('coupon_deleted', 1);
		$this->db->where('coupon_id', $id);
		return $this->db->update('coupons');
	}
	
	//--------------------------------------------------------------------
	
	
	/**
	 * Saves related items into their pivot tables (like products, plans, etc)
	 * that a coupon would be associated to.
	 *
	 * @param int $coupon_id The id of the coupon this data is related to.
	 * @param string $table The name of the database table to save to.
	 * @param field $field The name of the database table field to save to.
	 * @param array $items The items to save.
	 *
	 * @return void
	 */
	public function save_related ($coupon_id=null, $table='', $field='', $items=array()) 
	{	
		// First, delete any existing entries for this coupon, just in case it's an edit
		$this->db->where('coupon_id', $coupon_id);
		$this->db->delete($table);
		
		if (empty($items)) {
			// no new links!
			return TRUE;
		}

		// Now save the new values
		foreach ($items as $item)
		{ 
			$this->db->set(array('coupon_id' => $coupon_id, $field => $item));
			$this->db->insert($table);
		}
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Retrieves related records (in other db tables) to the specified coupon.
	 *
	 * @param id $coupon_id	The id of the coupon this data is related to.
	 * @param string $table The name of the database table to pull from.
	 * @param string $field The name of the database table field to retrieve.
	 *
	 * @return array The related object, or FALSE on failure.
	 */
	public function get_related($coupon_id=null, $table='', $field='') 
	{
		$this->db->where('coupon_id', $coupon_id);
		$query = $this->db->get($table);
		
		if ($query->num_rows())
		{
			$items = $query->result_array();
			
			foreach ($items as $item)
			{
				$collection[] = $item[$field];
			}
			
			return $collection;
		}
	}
	
	//--------------------------------------------------------------------
	
}

/* End of file coupon_model.php */
/* Location: ./application/modules/coupons/models/coupon_model.php */