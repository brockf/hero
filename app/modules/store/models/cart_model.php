<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Cart Model 
*
* Contains all the methods used to create, update, and delete the user's cart.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Cart_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Load Library
	*
	* This is a repetive call, and we may want to run some config options
	*
	*/
	private function _load_library () {
		$this->load->library('cart');
	}
	
	/**
	* Add to Cart
	*
	* @param int $product_id
	* @param int $quantity_id (default: 1)
	* @param array $options Selected product options, if available (default: array())
	*
	* @return boolean TRUE
	*/
	function add_to_cart ($product_id, $quantity = 1, $options = array()) {
		$this->_load_library();
		
		$this->load->model('store/products_model');
		
		// prep quantity
		if (!is_numeric($quantity) or (int)$quantity < 0) {
			$quantity = 1;
		}
	
		$product = $this->products_model->get_product($product_id);
		
		// get proper pricing
		$product['price'] = $this->products_model->get_price($product['id'], $options);
		
		$this->cart->insert(array(
								'id' => $product_id,
								'is_subscription' => FALSE,
								'qty' => $quantity,
								'price' => $product['price'],
								'name' => $product['name'],
								'requires_shipping' => $product['requires_shipping'],
								'weight' => $product['weight'],
								'options' => $options,
								'recurring_price' => '0',
								'free_trial' => '0',
								'free_trial_no_billing' => FALSE,
								'interval' => '0',
								'renew_subscription_id' => FALSE
							));
		
		$this->save_cart_to_db();
							
		return TRUE;
	}
	
	/**
	* Add Subscription to Cart
	*
	* @param int $subscription_plan_id
	* @param int $renew_subscription_id (optional) - renew this subscription
	*
	* @return boolean TRUE
	*/
	function add_subscription_to_cart ($subscription_plan_id, $renew_subscription_id = FALSE) {
		$this->_load_library();
		
		// remove all subscriptions, we can only have one in the cart at a time
		$cart = $this->get_cart();
		
		if (is_array($cart)) {
			foreach ($cart as $key => $item) {
				if ($item['is_subscription'] == TRUE) {
					$this->remove_from_cart($key);
				}
			}
		}
	
		// add subscription to cart
		$CI =& get_instance();
		$CI->load->model('billing/subscription_plan_model');
		$CI->load->model('billing/subscription_model');
		
		$plan = $CI->subscription_plan_model->get_plan($subscription_plan_id);
		
		if (empty($plan)) {
			die(show_error('Unable to retrieve plan details when adding subscription to cart.'));
		}
		
		// get previous subscriptions to this plan
		$previous_subscriptions = $this->subscription_model->get_subscriptions_friendly(array('plan_id' => $plan['id']));
		
		// this subscription rate may be made FREE if the conditions are met:
		//		- free trial
		//		- no billing info required for free trial
		//		- first time with this package
		$free_trial_no_billing = FALSE;
		if (!empty($plan['free_trial']) and $plan['require_billing_for_trial'] === FALSE and empty($previous_subscriptions)) {
			// it looks good - let's do it!
			$plan['initial_charge'] = 0;
			$plan['amount'] = 0;
			$plan['occurrences'] = 1;
			$plan['interval'] = $plan['free_trial'];
			$plan['free_trial'] = 0;
			
			// mark this is a free trial with no billing info - we might need this
			// in a future checkout processor update (?)
			$free_trial_no_billing = TRUE;
		}
		
		// we need to negate a free trial in the plan if they already have used it
		if (!empty($previous_subscriptions) and !empty($plan['free_trial'])) {
			$plan['free_trial'] = 0;
			$plan['initial_charge'] = $plan['amount'];
		}
		
		// is this a renewal?
		if ($renew_subscription_id !== FALSE) {
			// get the old subscription ID
			$subscription = $CI->subscription_model->get_subscription($renew_subscription_id);

			if (!empty($subscription)) {
				// calculate days to give as a free trial until this subscription ends
				$next_charge_date = strtotime($subscription['next_charge_date']);
				$end_date = strtotime($subscription['end_date']);
				
				if (!empty($subscription['next_charge_date']) and $next_charge_date < $end_date) {
					$end_date = $next_charge_date;
				}
				
				if ($end_date > time()) {
					$difference_in_seconds = $end_date - time();
					$difference_in_days = floor($difference_in_seconds / 86400);
					
					if ((int)$difference_in_days > 0) {
						$plan['initial_charge'] = 0;
						$plan['free_trial'] = (int)$difference_in_days;
					}
				}
				
				$renew_subscription_id = $subscription['id'];
			}
			else {
				$renew_subscription_id = FALSE;
			}
		}
		else {
			$renew_subscription_id = FALSE;
		}
		
		$result = $this->cart->insert(array(
								'id' => $subscription_plan_id,
								'is_subscription' => TRUE,
								'qty' => 1,
								'price' => $plan['initial_charge'],
								'recurring_price' => $plan['amount'],
								'free_trial' => $plan['free_trial'],
								'free_trial_no_billing' => $free_trial_no_billing,
								'interval' => $plan['interval'],
								'occurrences' => $plan['occurrences'],
								'name' => $plan['name'],
								'renew_subscription_id' => $renew_subscription_id,
								'weight' => '0',
								'requires_shipping' => FALSE
							));
		
		$this->save_cart_to_db();
		
		return TRUE;
	}
	
	/**
	* Get Subscription From Cart
	*
	* @return array Subscription product, else FALSE
	*/
	function get_subscription_cart () {
		$cart = $this->get_cart();
		
		if (empty($cart)) {
			return FALSE;
		}
		
		foreach ($cart as $item) {
			if ($item['is_subscription'] == TRUE) {
				return $item;
			}
		}
		
		return FALSE;
	}
	
	/**
	* Reset to Precoupon
	*
	* Reset all prices/trials to their precoupon states (we are trying to process another coupon)
	*
	* @return boolean
	*/
	function reset_to_precoupon () {
		$cart = $this->get_cart();
		
		if (empty($cart)) {
			return TRUE;
		}
		
		foreach ($cart as $item) {
			if ($item['is_subscription'] == TRUE and isset($item['precoupon_price'])) {
				$data = array(
								'rowid' => $item['rowid'],
								'price' => $item['precoupon_price'],
								'recurring_price' => $item['precoupon_recurring_price']
							);
				
				$this->cart->update($data);
			}
			
			if ($item['is_subscription'] == TRUE and isset($item['precoupon_trial'])) {
				$data = array(
								'rowid' => $item['rowid'],
								'free_trial' => $item['precoupon_free_trial']
							);
				
				$this->cart->update($data);
			}
			
			if ($item['is_subscription'] == FALSE and isset($item['precoupon_price'])) {
				$data = array(
								'rowid' => $item['rowid'],
								'price' => $item['precoupon_price'],
								'subtotal' => $item['qty'] * $item['precoupon_price']
							);
				
				$this->cart->update($data);
			}
		}	
		
		return TRUE;
	}
	
	/**
	* Reduce Subscription Prices
	*
	* @param array $allowed_subscription_plan_ids
	* @param float $discount
	* @param boolean $is_percentage (default: FALSE)
	*
	* @return boolean
	*/
	function reduce_subscription_prices ($allowed_subscription_plan_ids, $discount, $is_percentage = FALSE) {
		if (!$this->has_subscription()) {
			return FALSE;
		}
		
		$cart = $this->get_cart();
		
		if (empty($cart)) {
			return FALSE;
		}
		
		// make sure this is an array
		$allowed_subscription_plan_ids = empty($allowed_subscription_plan_ids) ? array() : $allowed_subscription_plan_ids;
		
		foreach ($cart as $item) {
			if ($item['is_subscription'] == TRUE) {
				if ((empty($allowed_subscription_plan_ids) or in_array($item['id'], $allowed_subscription_plan_ids)) and (!in_array('-1', $allowed_subscription_plan_ids))) {
					// we either have no restriction or it meets the restriction
					if ($is_percentage == TRUE) {
						$price = $item['price'] * (100 - $discount) * 0.01;
						$recurring_price = $item['recurring_price'] * (100 - $discount) * 0.01;
					}
					else {
						$price = $item['price'] - $discount;
						$recurring_price = $item['recurring_price'] - $discount;
					}
					
					if ($price < 0) {
						$price = 0;
					}
					if ($recurring_price < 0) {
						$recurring_price = 0;
					}
					
					// save pre-coupon prices so coupon modifications are NOT additive
					
					$data = array(
									'rowid' => $item['rowid'],
									'price' => $price,
									'recurring_price' => $recurring_price,
									'precoupon_price' => $item['price'],
									'precoupon_recurring_price' => $item['recurring_price']
								);
					
					$this->cart->update($data);
					
					return TRUE;
				}
			}
		}	
		
		return FALSE;
	}
	
	/**
	* Update Subscription Trial
	*
	* @param array $allowed_subscription_plan_ids
	* @param int $trial_days
	*
	* @return boolean
	*/
	function update_subscription_trial ($allowed_subscription_plan_ids, $trial_days) {
		if (!$this->has_subscription()) {
			return FALSE;
		}
		
		$cart = $this->get_cart();
		
		if (empty($cart)) {
			return FALSE;
		}
		
		foreach ($cart as $item) {
			if ($item['is_subscription'] == TRUE) {
				if (empty($allowed_subscription_plan_ids) or in_array($item['id'], $allowed_subscription_plan_ids)) {
					$data = array(
									'rowid' => $item['rowid'],
									'free_trial' => $trial_days,
									'price' => '0',
									'precoupon_price' => $item['price'],
									'precoupon_free_trial' => $item['free_trial']
								);								
					
					$this->cart->update($data);
					
					return TRUE;
				}
			}
		}	
		
		return FALSE;
	}
	
	/**
	* Reduce Product Prices
	*
	* @param array $allowed_product_ids
	* @param float $discount
	* @param boolean $is_percentage (default: FALSE)
	*
	* @return boolean
	*/
	function reduce_product_prices ($allowed_product_ids, $discount, $is_percentage = FALSE) {
		if (!$this->has_products()) {
			return FALSE;
		}
		
		$cart = $this->get_cart();
		
		if (empty($cart)) {
			return FALSE;
		}
		
		// make sure this is an array
		$allowed_product_ids = empty($allowed_product_ids) ? array() : $allowed_product_ids;
		
		// Track whether the coupon was successfully applied or not
		$applied = false;

		foreach ($cart as $item) {
			if ($item['is_subscription'] == FALSE) {
				if ((empty($allowed_product_ids) or in_array($item['id'], $allowed_product_ids)) and !in_array('-1', $allowed_product_ids)) {
					// we either have no restriction or it meets the restriction
					
					if ($is_percentage == TRUE) {
						$price = $item['price'] * (100 - $discount) * 0.01;
					}
					else {
						$price = $item['price'] - $discount;
					}
					
					if ($price < 0) {
						$price = 0;
					}
					
					$data = array(
									'rowid' => $item['rowid'],
									'price' => $price,
									'subtotal' => $price * $item['qty'],
									'precoupon_price' => $item['price']
								);
					
					$this->cart->update($data);
					
					// This coupon was applied at least once.
					$applied = true;
				}
			}
		}
		
		if (!$applied)
		{
			return 1;
		}
		
		return TRUE;
	}
	
	/**
	* Remove from Cart
	*
	* @param string $rowid The unique "rowid" from the $this->cart->contents() array
	* 
	* @return boolean TRUE
	*/
	function remove_from_cart ($rowid) {
		$this->_load_library();
		
		$this->cart->update(array('rowid' => $rowid, 'qty' => '0'));
		
		$this->save_cart_to_db();
		
		return TRUE;
	}
	
	/**
	* Get Cart
	*
	* @return array|boolean Array of cart or FALSE
	*/
	function get_cart () {
		$this->_load_library();
		$cart = $this->cart->contents();
		
		if (empty($cart)) {
			return FALSE;
		}
		
		foreach ($cart as $key => $item) {
			// format price
			$cart[$key]['price'] = money_format("%!^i", $item['price']);
			$cart[$key]['subtotal'] = money_format("%!^i", $item['subtotal']);
				
			// add removal links
			$cart[$key]['remove_link'] = site_url('store/remove_from_cart/' . $item['rowid']);
		}
		
		return $cart;
	}
	
	/**
	* Has Subscripton?
	*
	* Does the shopping cart have subscriptions?
	*
	* @return boolean
	*/
	function has_subscription () {
		$cart = $this->get_cart();
		
		if (empty($cart)) {
			return FALSE;
		}
		
		foreach ($cart as $item) {
			if ($item['is_subscription'] == TRUE) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	* Get Subscription
	*
	* Get the subscription item from the cart if there is one
	*
	* @return array|boolean
	*/
	function get_subscription () {
		$cart = $this->get_cart();
		
		if (empty($cart)) {
			return FALSE;
		}
		
		foreach ($cart as $item) {
			if ($item['is_subscription'] == TRUE) {
				return $item;
			}
		}
		
		return FALSE;
	}
	
	/**
	* Has Products?
	*
	* Does the shopping cart have products or just a subscription?
	*
	* @return boolean
	*/
	function has_products () {
		$cart = $this->get_cart();
		
		if (empty($cart)) {
			return FALSE;
		}
		
		foreach ($cart as $item) {
			if ($item['is_subscription'] == FALSE) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	* Get Cart Total
	*
	* Gets the total price of cart
	*
	* @return float $total
	*/
	function get_total () {
		$this->_load_library();
		
		$cart = $this->get_cart();
		if (empty($cart)) {
			return '0.00';
		}
		
		$total = $this->cart->total();
		
		// get rid of initial charges for subscriptions with a free trial
		if ($this->has_subscription()) {
			$subscription = $this->get_subscription_cart();
			
			if (isset($subscription['free_trial']) and $subscription['free_trial'] > 0) {
				$total = $total - $subscription['price'];
			}
		}
		
		return money_format("%!^i",$total);
	}
	
	/**
	* Save Cart to DB
	*
	* @param array $cart_array
	*/
	function save_cart_to_db () {
		if ($this->session->userdata('user_id') and $this->session->userdata('user_id') != '') {		
			$user_id = $this->session->userdata('user_id');
		
			$cart_array = $this->session->userdata('cart_contents');
			
			if (!empty($cart_array)) {			
				$this->db->update('users',array('user_cart' => serialize($cart_array)), array('user_id' => $user_id));
			}
			else {
				$this->db->update('users',array('user_cart' => ''), array('user_id' => $user_id));
			}
			
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* User Login
	*
	* @param array $user Userdata with keys like 'id', 'user_groups', 'username', 'cart'
	*
	* @return boolean TRUE
	*/
	function user_login ($user) {
		if ($this->get_cart() != FALSE) {
			$this->load->model('store/products_model');
			
			// look for pricing that includes membership pricing
			$cart = $this->get_cart();
			
			foreach ($cart as $key => $product) {
				if ($product['is_subscription'] == FALSE) {
					$cart[$key]['price'] = $this->products_model->get_price($product['id']);
					unset($cart[$key]['rowid']);
					
					// remove the original item
					$this->remove_from_cart($key);
					
					// insert new item
					$this->cart->insert($cart);
				}
			}
			
			$this->save_cart_to_db();
		}
		else {
			// we don't have an active cart, but maybe there's a cart in the database to load?
			if (!empty($user['cart'])) {
				// load it into the active session cart
				$this->session->set_userdata('cart_contents', $user['cart']);
			}
		}
		
		return TRUE;
	}
	
	/**
	* Update Quantity
	*
	* @param string $rowid Unique row ID of cart product
	* @param int $quantity New quantity value
	*
	* @return boolean TRUE
	*/
	function update_quantity ($rowid, $quantity) {
		$this->_load_library();
		
		$this->cart->update(array('rowid' => $rowid, 'qty' => $quantity));
		
		return TRUE;
	}
	
	/**
	* Calculate Totals
	*
	* @return array Array of cart total figures, else FALSE
	*/
	function calculate_totals () {
		$CI =& get_instance();
		$CI->load->model('store/products_model');
		$CI->load->model('billing/subscription_plan_model');
		
		$products = $this->get_cart();
		$subscription = $this->get_subscription_cart();
		
		if ($subscription or $products) {
			$product_sub_total = $this->get_total();
		}
		else {
			return FALSE;
		}
		
		// find tax rate
		// note: tax rate is not additive
		
		// we need the current state/country
		
		// build from shipping or billing address?
		if ($CI->session->userdata('shipping_address')) {
			$address = $CI->session->userdata('shipping_address');
		}
		else {
			$address = $CI->user_model->get_billing_address($CI->user_model->get('id'));
		}
		
		// get tax details
		$CI->load->model('store/taxes_model');
		$taxes = $CI->taxes_model->get_taxes();		
		
		// defaults
		$tax_rate = 0;
		$tax_id = 0;

		if (!empty($taxes)) {
			foreach ($taxes as $tax) {
				$match = FALSE;
				if ($tax['country_iso2'] == $address['country']) {
					$match = TRUE;
				}
				elseif ($tax['state_code'] == $address['state']) {
					$match = TRUE;
				}
				
				if ($match == TRUE) {
					if ($tax_rate < $tax['percentage']) {
						$tax_rate = $tax['percentage'];
						$tax_id = $tax['id'];
					}
				}
			}
		}
		
		// adjust tax rate to a percentage
		$tax_rate = $tax_rate / 100;
		
		// do we have a shipping rate?
		if ($this->session->userdata('shipping_rate')) {
			$shipping = money_format("%!^i", $this->session->userdata('shipping_rate'));
			
			if ($this->session->userdata('shipping_is_taxable') == TRUE) {
				$product_tax = ($shipping * $tax_rate);
			}
			
			$shipping_id = $this->session->userdata('shipping_id');
		}
		else {
			$shipping = 0;
			$shipping_id = FALSE;
		}
				
		if (!empty($products)) {
			// $product_tax may already have been initiated in the shipping code above
			$product_tax = (!isset($product_tax)) ? 0 : $product_tax;
			$order_tax_products = $product_tax;
			$order_tax_subscription = 0;
			
			// calculate tax
			$cart = $this->get_cart();
			
			foreach ($cart as $item) {
				if ($item['is_subscription'] == TRUE) {
					if ($item['free_trial'] == 0) {
						$this_sub = $CI->subscription_plan_model->get_plan($item['id']);
						
						if ($this_sub['is_taxable']) {
							$product_tax += ($item['subtotal'] * $tax_rate);
							$order_tax_subscription =+ ($item['subtotal'] * $tax_rate);
						}
					}
				}
				else {
					$this_product = $CI->products_model->get_product($item['id']);
					
					if ($this_product['is_taxable']) {
						$product_tax += ($item['subtotal'] * $tax_rate);
						$order_tax_products += ($item['subtotal'] * $tax_rate);
					}
				}
			}
			
			$product_tax = money_format("%!^i", $product_tax);
			$product_total = money_format("%!^i",$product_tax + $product_sub_total + $shipping);
		}
		
		// subscription
		if (!empty($subscription)) {
			$recurring_sub_total = money_format("%!^i",$subscription['recurring_price']);
			$recurring_tax = 0;
			
			$this_sub = $CI->subscription_plan_model->get_plan($subscription['id']);
			if ($this_sub['is_taxable']) {
				$recurring_tax = $subscription['recurring_price'] * $tax_rate;
			}
			
			$recurring_tax = money_format("%!^i", $recurring_tax);
			$recurring_total = money_format("%!^i",$recurring_sub_total + $recurring_tax);
		}
		
		// get coupon discount
		$discount = 0;
		
		$cart = $this->get_cart();
		foreach ($cart as $item) {
			if (isset($item['precoupon_price'])) {
				$discount += ($item['precoupon_price'] - $item['price'])*$item['qty'];
			}
		}
		
		$discount = money_format("%!^i", $discount);
		
		if ((int)$discount <= 0) {
			$discount = FALSE;
		}
		
		// return array
		$totals = array();
		$totals['shipping'] = (!empty($shipping)) ? $shipping : FALSE;
		// we use a === FALSE comparison here because the shipping_id might be "0" if its the default ship option
		$totals['shipping_id'] = ($shipping_id !== FALSE) ? $shipping_id : FALSE;
		$totals['tax_rate'] = $tax_rate;
		$totals['tax_id'] = $tax_id;
		$totals['order_sub_total'] = $product_sub_total;
		$totals['order_tax'] = isset($product_tax) ? $product_tax : FALSE;
		$totals['order_tax_subscription'] = isset($order_tax_subscription) ? $order_tax_subscription : FALSE;
		$totals['order_tax_products'] = isset($order_tax_products) ? $order_tax_products : FALSE;
		$totals['order_total'] = isset($product_total) ? $product_total : FALSE;
		$totals['recurring_sub_total'] = isset($recurring_sub_total) ? $recurring_sub_total : FALSE;
		$totals['recurring_tax'] = isset($recurring_tax) ? $recurring_tax : FALSE;
		$totals['recurring_total'] = isset($recurring_total) ? $recurring_total : FALSE;
		$totals['recurring_interval'] = isset($subscription['interval']) ? $subscription['interval'] : FALSE;
		$totals['recurring_first_charge'] = (isset($subscription['free_trial']) and $subscription['free_trial'] > 0) ? date('M j, Y', strtotime('now +' . $subscription['free_trial'] . ' days')) : FALSE;
		$totals['recurring_last_charge'] = (isset($subscription['occurrences']) and $subscription['occurrences'] > 0) ? date('M j, Y', strtotime('now +' . ($subscription['occurrences']*$subscription['interval']) . ' days')) : FALSE;
		$totals['discount'] = $discount;
		
		// save calculations in session
		foreach ($totals as $key => $total) {
			$this->session->set_userdata($key, $total);
		}
		reset($totals);
		
		return $totals;
	}
	
	/**
	* Free Cart
	*
	* Is this cart free?  I.e., are there not payments today or recurring?
	*
	* @return boolean 
	*/
	function free_cart () {
		$cart = $this->get_cart();
		
		if (empty($cart)) {
			return TRUE;
		}
		
		foreach ($cart as $item) {
			if ($item['is_subscription']) {
				if ($item['price'] > 0 or $item['recurring_price'] > 0) {
					return FALSE;
				}
			}
			else {
				if ($item['price'] > 0) {
					return FALSE;
				}
			}
		}
		
		return TRUE;
	}
}