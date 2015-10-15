<?php

/**
* Order Model
*
* Handles product orders
*
* @author Electric Function, Inc.
* @package Hero Framework
* @copyright Electric Function, Inc.
*
*/

class Order_model extends CI_Model {
	private $cache;

	function __construct() {
		parent::__construct();
	}

	/**
	* Process Order
	*
	* Track ordered products, shipping address, totals, etc.
	*
	* @param int $charge_id
	* @param int $user_id
	* @param array $totals
	* @param array $shipping_address (default: FALSE)
	* @param int $coupon_id (default: FALSE)
	*
	* @return boolean TRUE
	*/
	function process_order ($charge_id, $user_id, $totals, $shipping_address = FALSE, $coupon = FALSE, $shipping_name=null) {
		$CI =& get_instance();
		$CI->load->model('store/cart_model');

		$user = $CI->user_model->get_user($user_id);

		if (empty($user)) {
			return FALSE;
		}

		// we can record taxes and shipping costs even if they don't have a cart
		// record in taxes_received
		if (!empty($totals['tax_id'])) {
			// record the tax for this order
			$CI->load->model('store/taxes_model');
			$CI->taxes_model->record_tax($totals['tax_id'], $charge_id, (float)$totals['order_tax_products'], (float)$totals['order_tax_subscription']);
		}

		// record in shipping_received
		// we use a === FALSE comparison here because the shipping_id might be "0" if its the default ship option
		if ($totals['shipping_id'] !== FALSE) {
			$CI->load->model('store/shipping_model');
			$CI->shipping_model->record_shipping($charge_id, $totals['shipping_id'], (float)$totals['shipping'], $shipping_name);
		}

		// check for cart and products processing
		$cart = $CI->cart_model->get_cart();

		if (empty($cart) or !$this->cart_model->has_products()) {
			return FALSE;
		}

		// get customer ID
		$customer_id = $CI->user_model->get_customer_id($user['id']);

		// create order record
		$insert_fields = array(
							'order_id' => $charge_id,
							'customer_id' => $customer_id,
							'affiliate' => '0',
							'coupon_id' => (!empty($coupon)) ? $coupon : '0'
							);

		if (!empty($shipping_address)) {
			 $insert_fields['shipping_first_name'] = $shipping_address['first_name'];
			 $insert_fields['shipping_last_name'] = $shipping_address['last_name'];
			 $insert_fields['shipping_company'] = $shipping_address['company'];
			 $insert_fields['shipping_address_1'] = $shipping_address['address_1'];
			 $insert_fields['shipping_address_2'] = $shipping_address['address_2'];
			 $insert_fields['shipping_city'] = $shipping_address['city'];
			 $insert_fields['shipping_state'] = $shipping_address['state'];
			 $insert_fields['shipping_country'] = $shipping_address['country'];
			 $insert_fields['shipping_postal_code'] = $shipping_address['postal_code'];
			 $insert_fields['shipping_phone_number'] = $shipping_address['phone_number'];
		}

		$this->db->insert('order_details', $insert_fields);
		$order_details_id = $this->db->insert_id();

		$CI->load->model('store/products_model');
		foreach ($cart as $item) {

			// To allow for other modules to interact with the cart, we'll check for the 'module' tag being listed here
			if (isset($item['module']))
			{
				// The method should be in the modules' {module}_lib library class, process_order_item method.
				$cname = $item['module'] .'_lib';
				$CI->load->library($item['module'] .'/'. $cname);

				$this->$cname->process_order_item($item, $charge_id, $user_id, $totals, $shipping_address, $coupon, $shipping_name);

				// Update the order details to include the module name
				$this->db->where('order_details_id', $order_details_id)->update('order_details', array('module' => $item['module']));
			}

			// the cart may have some non-products in it, so we'll make sure that this is a product by looking or an ID
			elseif (isset($item['id'])) {
				if ($item['is_subscription'] == FALSE) {
					$product = $this->products_model->get_product($item['id']);

					// downloadable product?
					if ($product['is_download']) {
						// create hash
						$hash = md5($product['download_name'] . time());

						// insert into DB
						$this->db->insert('download_links', array('download_link_hash' => $hash, 'download_link_path' => $product['download_name'], 'download_link_downloads' => '0'));

						// download link
						$download_link = site_url('store/download/' . $hash);

						// hook
						$this->app_hooks->data('product', $item['id']);
						$this->app_hooks->data('member', $user_id);
						$this->app_hooks->data('invoice', $charge_id);
						$this->app_hooks->data_var('download_link', $download_link);
						$this->app_hooks->trigger('store_order_product_downloadable', $charge_id, $item['id']);
						$this->app_hooks->reset();
					}

					// add member to usergroup with purchase?
					if (!empty($product['promotion'])) {
			    		$CI->user_model->add_group($user['id'], $product['promotion']);
			    	}

					// inventory tracking?
					if ($product['track_inventory'] == TRUE) {
						// knock down the inventory by the quantity ordered
						$CI->products_model->knock_inventory($product['id'], $item['qty']);
					}

					// track product in database as part of order
					$order_shipped = ($product['is_download']) ? '1' : '0';

					$insert_fields = array(
										'order_details_id' => $order_details_id,
										'product_id' => $item['id'],
										'order_products_quantity' => $item['qty'],
										'order_products_price' => $item['price'],
										'order_products_options' => (isset($item['options'])) ? serialize($item['options']) : '',
										'order_products_shipped' => $order_shipped
									);

					$this->db->insert('order_products', $insert_fields);

					// hook
					$this->app_hooks->data('product', $item['id']);
					$this->app_hooks->data('member', $user_id);
					$this->app_hooks->data('invoice', $charge_id);
					$this->app_hooks->trigger('store_order_product', $charge_id, $item['id']);
					$this->app_hooks->reset();
				}
			}
		}

		// hook
		$this->app_hooks->data('member', $user_id);
		$this->app_hooks->data('invoice', $charge_id);
		$this->app_hooks->data('order', $charge_id);

		$this->app_hooks->data_var('totals', $totals);

		$this->app_hooks->trigger('store_order', $charge_id);

		// reset hook data
		$this->app_hooks->reset();
	}

	/**
	* Get Order
	*
	* @param int $order_details_id
	* @param string $field_to_match (default: order_details_id)
	*
	* @return array
	*/
	function get_order ($id, $field_to_match = 'order_details_id') {
		$cache_id = $id . $field_to_match;
		if (isset($this->cache[$cache_id])) {
			return $this->cache[$cache_id];
		}

		$this->db->where($field_to_match, $id);
		$result = $this->db->get('order_details');

		if ($result->num_rows() == 0) {
			return FALSE;
		}

		$row = $result->row_array();

		$order = array();

		$order['customer_id'] = $row['customer_id'];
		$order['charge_id'] = $row['order_id'];
		$order['invoice_id'] = $row['order_id']; // duplication but, for consistency, let's have invoice_id available
		$order['affiliate'] = $row['affiliate'];

		if (!empty($row['shipping_first_name'])) {
			$order['shipping'] = array();

			$order['shipping']['first_name'] = $row['shipping_first_name'];
			$order['shipping']['last_name'] = $row['shipping_last_name'];
			$order['shipping']['company'] = $row['shipping_company'];
			$order['shipping']['address_1'] = $row['shipping_address_1'];
			$order['shipping']['address_2'] = $row['shipping_address_2'];
			$order['shipping']['city'] = $row['shipping_city'];
			$order['shipping']['state'] = $row['shipping_state'];
			$order['shipping']['country'] = $row['shipping_country'];
			$order['shipping']['postal_code'] = $row['shipping_postal_code'];
			$order['shipping']['phone_number'] = $row['shipping_phone_number'];
		}
		else {
			$order['shipping'] = FALSE;
		}

		// get order data
		// things like discount, total, tax, etc.
		$CI =& get_instance();
		$CI->load->model('billing/invoice_model');
		$data = $CI->invoice_model->get_invoice_data($row['order_id']);

		$order['totals'] = $data;

		$this->cache[$cache_id] = $order;
		return $order;
	}

	//--------------------------------------------------------------------

	public function count_orders($filters=array())
	{
		if (isset($filters['start_date'])) {
			$start_date = date('Y-m-d H:i:s', strtotime($filters['start_date']));
			$this->db->where('orders.timestamp >=', $start_date);
		}

		if (isset($filters['end_date'])) {
			$end_date = date('Y-m-d H:i:s', strtotime($filters['end_date']));
			$this->db->where('orders.timestamp <=', $end_date);
		}

		if(isset($filters['amount'])) {
			$this->db->where('orders.amount', $filters['amount']);
		}

		if (isset($filters['invoice_id'])) {
			$this->db->where('orders.order_id', $filters['invoice_id']);
		}

		if (isset($filters['customer_id'])) {
			$this->db->where('orders.customer_id', $filters['customer_id']);
		}

		if (isset($filters['user_id'])) {
			$this->db->where('customers.internal_id', $filters['user_id']);
		}

		if (isset($filters['gateway'])) {
			$this->db->like('gateways.gateway_id', $filters['gateway']);
		}

		if (isset($filters['member_name'])) {
			$this->db->like('users.user_last_name', $filters['member_name']);
		}

		if (isset($filters['refunded']) and $filters['refunded'] == TRUE) {
			$this->db->where('orders.refunded','1');
		}

		if (isset($filters['product_name'])) {
			$this->db->like('products.product_name',$filters['product_name']);
		}

		if (isset($filters['shipped'])) {
			if ($filters['shipped'] == TRUE) {
				$filters['shipped'] = '1';
			}
			else {
				$filters['shipped'] = '0';
			}

			$this->db->where('order_products.order_products_shipped',$filters['shipped']);
		}

		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'orders.order_id';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'DESC';
		$this->db->order_by($order_by, $order_dir);

		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}

		$this->db->where('orders.status','1');

		$count = $this->db->count_all_results('orders');

		return $count;
	}

	//--------------------------------------------------------------------

	/**
	* Get Order Products
	*
	* Get all product orders as individual prodects, with filters.  Instead of retrieving individual
	* orders, this method will retrieve 3 records for 1 order of 3 products.
	*
	* @param date $filters['start_date']
	* @param date $filters['end_date']
	* @param float $filters['amount']
	* @param int $filters['invoice_id']
	* @param int $filters['customer_id']
	* @param int $filters['user_id']
	* @param int $filters['gateway']
	* @param string $filters['member_name']
	* @param boolean $filters['refunded']
	* @param string $filters['product_name']
	* @param boolean $filters['shipped']
	* @param string $filters['sort']
	* @param string $filters['sort_dir']
	* @param int $filters['limit']
	* @param int $filters['offset']
	*
	* @return array Product/order records
	*/
	function get_order_products ($filters = array()) {
		if (isset($filters['start_date'])) {
			$start_date = date('Y-m-d H:i:s', strtotime($filters['start_date']));
			$this->db->where('orders.timestamp >=', $start_date);
		}

		if (isset($filters['end_date'])) {
			$end_date = date('Y-m-d H:i:s', strtotime($filters['end_date']));
			$this->db->where('orders.timestamp <=', $end_date);
		}

		if(isset($filters['amount'])) {
			$this->db->where('orders.amount', $filters['amount']);
		}

		if (isset($filters['invoice_id'])) {
			$this->db->where('orders.order_id', $filters['invoice_id']);
		}

		if (isset($filters['customer_id'])) {
			$this->db->where('orders.customer_id', $filters['customer_id']);
		}

		if (isset($filters['user_id'])) {
			$this->db->where('customers.internal_id', $filters['user_id']);
		}

		if (isset($filters['gateway'])) {
			$this->db->like('gateways.gateway_id', $filters['gateway']);
		}

		if (isset($filters['member_name'])) {
			$this->db->like('users.user_last_name', $filters['member_name']);
		}

		if (isset($filters['refunded']) and $filters['refunded'] == TRUE) {
			$this->db->where('orders.refunded','1');
		}

		if (isset($filters['product_name'])) {
			$this->db->like('products.product_name',$filters['product_name']);
		}

		if (isset($filters['shipped'])) {
			if ($filters['shipped'] == TRUE) {
				$filters['shipped'] = '1';
			}
			else {
				$filters['shipped'] = '0';
			}

			$this->db->where('order_products.order_products_shipped',$filters['shipped']);
		}

		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'orders.order_id';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'DESC';
		$this->db->order_by($order_by, $order_dir);

		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}

		$this->db->where('orders.status','1');

		$this->db->select('*');
		$this->db->select('orders.order_id AS invoice_id');

		$this->db->join('customers', 'customers.customer_id = orders.customer_id', 'inner');
		$this->db->join('users', 'customers.internal_id = users.user_id', 'inner');
		$this->db->join('countries', 'countries.country_id = customers.country', 'left');
		$this->db->join('gateways', 'gateways.gateway_id = orders.gateway_id', 'left');
		$this->db->join('order_details', 'order_details.order_id = orders.order_id','inner');
		$this->db->join('order_products', 'order_details.order_details_id = order_products.order_details_id','inner');
		$this->db->join('products', 'products.product_id = order_products.product_id','inner');
		$this->db->join('taxes_received', 'orders.order_id = taxes_received.order_id','left');
		$this->db->join('taxes', 'taxes.tax_id = taxes_received.tax_id','left');
		$this->db->join('shipping_received', 'orders.order_id = shipping_received.order_id','left');
		$this->db->join('shipping', 'shipping.shipping_id = shipping_received.shipping_id','left');

		$this->db->group_by('order_products.order_products_id');

		$this->db->from('orders');

		$result = $this->db->get();

		if ($result->num_rows() == 0) {
			return FALSE;
		}

		$products = array();

		foreach($result->result_array() as $product) {
			if (!empty($product['shipping_first_name'])) {
				$shipping_address = array(
									'first_name' => $product['shipping_first_name'],
									'last_name' => $product['shipping_last_name'],
									'company' => $product['shipping_company'],
									'address_1' => $product['shipping_address_1'],
									'address_2' => $product['shipping_address_2'],
									'city' => $product['shipping_city'],
									'state' => $product['shipping_state'],
									'country' => $product['shipping_country'],
									'postal_code' => $product['shipping_postal_code'],
									'phone_number' => $product['shipping_phone_number']
								);
			}
			else {
				$shipping_address = FALSE;
			}

			$products[] = array(
								'invoice_id' => $product['invoice_id'],
								'order_products_id' => $product['order_products_id'],
								'name' => $product['product_name'],
								'quantity' => $product['order_products_quantity'],
								'price' => money_format("%!^i",$product['order_products_price']),
								'shipped' => ($product['order_products_shipped'] == '1') ? TRUE : FALSE,
								'options' => (!empty($product['order_products_options'])) ? unserialize($product['order_products_options']) : FALSE,
								'gateway_id' => $product['gateway_id'],
								'gateway' => $product['alias'],
								'date' => local_time($product['timestamp']),
								'user_id' => $product['user_id'],
								'user_first_name' => $product['user_first_name'],
								'user_last_name' => $product['user_last_name'],
								'user_email' => $product['user_email'],
								'user_groups' => $product['user_groups'],
								'amount' => money_format("%!^i",(float)$product['amount']),
								'refunded' => ($product['refunded'] == '1') ? TRUE : FALSE,
								'card_last_four' => $product['card_last_four'],
								'is_refunded' => (empty($product['refunded'])) ? FALSE : TRUE,
								'refund_date' => (empty($product['refunded'])) ? FALSE : local_time($product['refund_date']),
								'tax_name' => (empty($product['tax_name'])) ? FALSE : $product['tax_name'],
								'tax_paid' => money_format("%!^i",(float)($product['tax_received_for_products'] + $product['tax_received_for_subscription'])),
								'tax_rate' => (empty($product['tax_percentage'])) ? FALSE : $product['tax_percentage'],
								'shipping_id' => (!empty($product['shipping_id'])) ? $product['shipping_id'] : FALSE,
								'shipping_name' => (!empty($product['shipping_name'])) ? $product['shipping_name'] : 'Default',
								'shipping_charge' => (!empty($product['shipping_received_amount'])) ? money_format("%!^i", (float)$product['shipping_received_amount']) : FALSE,
								'order_details_id' => (!empty($product['order_details_id'])) ? $product['order_details_id'] : FALSE,
								'shipping_address' => $shipping_address,
								'billing_address' => array(
														'first_name' => $product['first_name'],
														'last_name' => $product['last_name'],
														'company' => $product['company'],
														'address_1' => $product['address_1'],
														'address_2' => $product['address_2'],
														'city' => $product['city'],
														'state' => $product['state'],
														'country' => $product['iso2'],
														'postal_code' => $product['postal_code'],
														'email' => $product['email'],
														'phone_number' => $product['phone']
												),
							);
		}

		return $products;
	}

	/**
	* Mark Product Order as Shipped
	*
	* @param int $order_products_id
	*
	* @return boolean
	*/
	function mark_as_shipped ($order_products_id) {
		$this->db->update('order_products', array('order_products_shipped' => '1'), array('order_products_id' => $order_products_id));

		return TRUE;
	}

	/**
	* Mark Product Order as Not Shipped
	*
	* @param int $order_products_id
	*
	* @return boolean
	*/
	function mark_as_not_shipped ($order_products_id) {
		$this->db->update('order_products', array('order_products_shipped' => '0'), array('order_products_id' => $order_products_id));

		return TRUE;
	}
}
