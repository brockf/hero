<?php

/**
* Invoice Model
*
* Contains all the methods used to view invoices.
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework

*/
class Invoice_model extends CI_Model
{
	private $cache;

	function Invoice_model()
	{
		parent::__construct();
	}

	/**
	* Invoice Lines
	*
	* @param int $invoice_id
	*
	* @return array with each row's keys: "line", "quantity", "sub_total"
	*/
	function invoice_lines ($invoice_id) {
		$invoice = $this->get_invoice($invoice_id);

		$CI =& get_instance();

		// holds the lines
		$lines = array();

		// Deal with any sub-modules that might have put the order in here...
		if (isset($invoice['module']) && !empty($invoice['module']))
		{
			$cname = $invoice['module'] .'_lib';
			$CI->load->library($invoice['module'] .'/'. $cname);

			$lines = $CI->$cname->invoice_lines($invoice);
		}

		// build subscription line, with tax
		else if (!empty($invoice['subscription_id'])) {
			// get subscription information
			$CI->load->model('billing/subscription_model');
			$subscription = $CI->subscription_model->get_subscription($invoice['subscription_id']);

			// is there a tax here?
			$CI->load->model('store/taxes_model');
			$tax = $CI->taxes_model->get_tax_for_subscription($subscription['id']);

			// get the proper amount for the subscription (initial charge or not)
			$this->load->model('billing/charge_data_model');

			// we'll check for charge_data
			// this means that this is the initial charge
			// this is the most accurate means of getting the initial subscription price because
			// coupons, etc. may be augmenting it
			if ($charge_data = $this->charge_data_model->get($invoice['id'])) {
				$totals = unserialize($charge_data['totals']);
				$sub_price = $totals['recurring_sub_total'];
			}
			else {
				$sub_price = $subscription['amount'];

				// subtract tax from this?
				// we only do this when we aren't using the initial charge because the initial charge
				// is the raw value from the plan while this is the actual charging value
				if ($tax['tax_amount'] !== FALSE) {
					$sub_price = $sub_price - $tax['tax_amount'];
				}
			}

			$lines[] = array(
							'line' => '(Subscription) ' . $subscription['plan']['name'],
							'quantity' => '1',
							'sub_total' => money_format("%!^i",$sub_price)
						);
		}

		// build lines from products
		if (!empty($invoice['order_details_id'])) {
			$result = $this->db->select('products.*')
							   ->select('order_products.*')
							   ->from('order_products')
							   ->join('products','products.product_id = order_products.product_id')
							   ->where('order_details_id',$invoice['order_details_id'])
							   ->get();

			if ($result->num_rows() > 0) {
				foreach ($result->result_array() as $product) {
					$line = $product['product_name'];

					// do we have product options to show?
					if (!empty($product['order_products_options']) and $product['order_products_options'] != serialize(array())) {
						$product['order_products_options'] = unserialize($product['order_products_options']);

						$line .= ' (';

						foreach ($product['order_products_options'] as $label => $value) {
							$line .= $label . ': ' . $value . ', ';
						}

						$line = rtrim($line, ', ');

						$line .= ')';
					}

					$lines[] = array(
								'line' => $line,
								'quantity' => $product['order_products_quantity'],
								'original_price' => $product['product_price'],
								'sub_total' => $product['order_products_price'],
								'shipped' => ($product['order_products_shipped'] == '1') ? TRUE : FALSE,
								'order_products_id' => $product['order_products_id'],
								'product_options' => $product['order_products_options'],
								'product_name'	=> $product['product_name'],
								'sku' => $product['product_sku']
							);
				}
			}
		}

		return $lines;
	}

	/**
	* Get Invoice Data
	*
	* @param int $invoice_id
	*
	* @return array $data
	*/
	function get_invoice_data ($invoice_id) {
		$CI =& get_instance();

		$CI->load->model('billing/charge_data_model');
		$charge_data = $CI->charge_data_model->get($invoice_id);

		$charge_data = unserialize($charge_data['totals']);

		$data = array(
						'shipping' => money_format("%!^i",$charge_data['shipping']),
						'subtotal' => money_format("%!^i",$charge_data['order_sub_total']),
						'tax' => money_format("%!^i",$charge_data['order_tax']),
						'total' => money_format("%!^i",$charge_data['order_total']),
						'discount' => money_format("%!^i",$charge_data['discount'])
					);

		return $data;
	}

	/**
	* Get Invoice
	*
	* @param int $invoice_id
	*
	* @return array $invoice
	*/
	function get_invoice ($invoice_id) {
		if (isset($this->cache[$invoice_id]) and !empty($this->cache[$invoice_id])) {
			return $this->cache[$invoice_id];
		}

		$invoice = $this->get_invoices(array('id' => $invoice_id));

		if (empty($invoice)) {
			return FALSE;
		}

		$this->cache[$invoice_id] = $invoice[0];
		return $invoice[0];
	}

	/**
	* Get Invoice Total
	*
	* @param int $filters['user_id'] Member ID
	* @param date $filters['start_date'] Only orders after or on this date will be returned. Optional.
	* @param date $filters['end_date'] Only orders before or on this date will be returned. Optional.
	* @param int $filters['id'] The charge ID.  Optional.
	* @param string $filters['amount'] The amount of the charge.  Optional.
	* @param boolean $filters['subscription_id'] Only charges linked to this subscription.
	* @param int $filters['card_last_four'] Last 4 digits of credit card
	* @param int $filters['offset'] Offsets the database query.
	* @param int $filters['limit'] Limits the number of results returned. Optional.
	* @param string $filters['sort'] Column used to sort the results.
	* @param string $filters['sort_dir'] Used when a sort param is supplied.  Possible values are asc and desc. Optional.
	* @param boolean $counting Set to TRUE to receive the total number of matching invoices
	*
	* @return float $total
	*/
	function get_invoices_total ($filters)
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

		if (isset($filters['id'])) {
			$this->db->where('orders.order_id', $filters['id']);
		}

		if (isset($filters['member_name'])) {
			if (is_numeric($filters['member_name'])) {
				// we are passed a member id
				$this->db->where('users.user_id',$filters['member_name']);
			} else {
				$this->db->like('users.user_last_name', $filters['member_name']);
			}
		}

		if (isset($filters['customer_id'])) {
			$this->db->where('orders.customer_id', $filters['customer_id']);
		}

		if (isset($filters['user_id'])) {
			$this->db->where('customers.internal_id', $filters['user_id']);
			$this->db->join('customers', 'customers.customer_id = orders.customer_id', 'inner');
		}

		if (isset($filters['gateway'])) {
			$this->db->like('gateways.alias', $filters['gateway']);
			$this->db->join('gateways', 'gateways.gateway_id = orders.gateway_id', 'left');
		}

		if (isset($filters['subscription_id'])) {
			$this->db->where('orders.subscription_id', $filters['subscription_id']);
		}

		if (isset($filters['card_last_four'])) {
			$this->db->where('orders.card_last_four', $filters['card_last_four']);
		}

		$this->db->join('customers', 'customers.customer_id = orders.customer_id', 'inner');
		$this->db->join('users', 'customers.internal_id = users.user_id', 'inner');

		$this->db->where('orders.status','1');

		$this->db->select('orders.amount');

		$this->db->group_by('orders.order_id');

		$result = $this->db->get('orders');

		if ($result->num_rows() == 0) {
			return 0;
		}

		$total = 0;

		foreach($result->result_array() as $invoice) {
			$total += $invoice['amount'];
		}

		return $total;
	}

	function count_invoices ($filters = array()) {
		$invoices = $this->get_invoices($filters, TRUE);

		return $invoices;
	}

	/**
	* View Invoices
	*
	* Returns an array of results based on submitted search criteria.  All fields are optional.
	*
	* @param int $filters['user_id'] Member ID
	* @param date $filters['start_date'] Only orders after or on this date will be returned. Optional.
	* @param date $filters['end_date'] Only orders before or on this date will be returned. Optional.
	* @param int $filters['id'] The charge ID.  Optional.
	* @param string $filters['amount'] The amount of the charge.  Optional.
	* @param boolean $filters['subscription_id'] Only charges linked to this subscription.
	* @param int $filters['card_last_four'] Last 4 digits of credit card
	* @param int $filters['offset'] Offsets the database query.
	* @param int $filters['limit'] Limits the number of results returned. Optional.
	* @param string $filters['sort'] Column used to sort the results.
	* @param string $filters['sort_dir'] Used when a sort param is supplied.  Possible values are asc and desc. Optional.
	* @param boolean $counting Set to TRUE to receive the total number of matching invoices
	*
	* @return array|bool Invoice results or FALSE if none
	*/
	function get_invoices ($filters, $counting = FALSE)
	{
		if (isset($filters['start_date'])) {
			$start_date = date('Y-m-d H:i:s', strtotime($filters['start_date']));
			$this->db->where('timestamp >=', $start_date);
		}

		if (isset($filters['end_date'])) {
			$end_date = date('Y-m-d H:i:s', strtotime($filters['end_date']));
			$this->db->where('timestamp <=', $end_date);
		}

		if(isset($filters['amount'])) {
			$this->db->where('orders.amount', $filters['amount']);
		}

		if (isset($filters['id'])) {
			$this->db->where('orders.order_id', $filters['id']);
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
			if (is_numeric($filters['member_name'])) {
				// we are passed a member id
				$this->db->where('users.user_id',$filters['member_name']);
			} else {
				$this->db->like('users.user_last_name', $filters['member_name']);
			}
		}

		if (isset($filters['subscription_id'])) {
			$this->db->where('orders.subscription_id', $filters['subscription_id']);
		}

		if (isset($filters['card_last_four'])) {
			$this->db->where('orders.card_last_four', $filters['card_last_four']);
		}

		if (isset($filters['status'])) {
			$this->db->where('orders.status',$filters['status']);
		}
		else {
			$this->db->where('orders.status','1');
		}

		if (isset($filters['refunded']) and $filters['refunded'] == TRUE) {
			$this->db->where('orders.refunded','1');
		}

		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'orders.order_id';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'DESC';
		$this->db->order_by($order_by, $order_dir);

		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}

		$this->db->join('customers', 'customers.customer_id = orders.customer_id', 'inner');
		$this->db->join('users', 'customers.internal_id = users.user_id', 'inner');
		$this->db->join('countries', 'countries.country_id = customers.country', 'left');
		$this->db->join('gateways', 'gateways.gateway_id = orders.gateway_id', 'left');
		$this->db->join('order_details', 'order_details.order_id = orders.order_id','left');
		$this->db->join('taxes_received', 'orders.order_id = taxes_received.order_id','left');
		$this->db->join('taxes', 'taxes.tax_id = taxes_received.tax_id','left');
		$this->db->join('shipping_received', 'orders.order_id = shipping_received.order_id','left');
		$this->db->join('shipping', 'shipping.shipping_id = shipping_received.shipping_id','left');
		$this->db->join('coupons', 'order_details.coupon_id = coupons.coupon_id','left');

		$this->db->group_by('orders.order_id');

		if ($counting == TRUE) {
			$this->db->select('orders.order_id');
			$result = $this->db->get('orders');

			$total_rows = $result->num_rows();
			$result->free_result();
			return $total_rows;
		}
		else {
			$this->db->select('*');
			$this->db->select('orders.order_id AS invoice_id');
			$this->db->select('orders.subscription_id AS true_subscription_id');
		}

		$this->db->from('orders');
		$result = $this->db->get();

		if ($result->num_rows() == 0) {
			return FALSE;
		}
//	die('<pre>'. print_r($result->result(), true));
		$invoices = array();

		foreach($result->result_array() as $invoice) {
			$t = array(
								'id' => $invoice['invoice_id'],
								'gateway_id' => $invoice['gateway_id'],
								'gateway' => $invoice['alias'],
								'date' => local_time($invoice['timestamp']),
								'user_id' => $invoice['user_id'],
								'user_first_name' => $invoice['user_first_name'],
								'user_last_name' => $invoice['user_last_name'],
								'user_email' => $invoice['user_email'],
								'user_groups' => $invoice['user_groups'],
								'amount' => money_format("%!^i",(float)$invoice['amount']),
								'refunded' => ($invoice['refunded'] == '1') ? TRUE : FALSE,
								'card_last_four' => $invoice['card_last_four'],
								'is_refunded' => (empty($invoice['refunded'])) ? FALSE : TRUE,
								'refund_date' => (empty($invoice['refunded'])) ? FALSE : local_time($invoice['refund_date']),
								'subscription_id' => $invoice['true_subscription_id'],
								'tax_name' => (empty($invoice['tax_name'])) ? FALSE : $invoice['tax_name'],
								'tax_paid' => money_format("%!^i",(float)($invoice['tax_received_for_products'] + $invoice['tax_received_for_subscription'])),
								'tax_rate' => (empty($invoice['tax_percentage'])) ? FALSE : $invoice['tax_percentage'],
								'shipping_id' => (!empty($invoice['shipping_id'])) ? $invoice['shipping_id'] : FALSE,
								'shipping_name' => (!empty($invoice['shipping_name'])) ? $invoice['shipping_name'] : 'Default',
								'shipping_charge' => (!empty($invoice['shipping_received_amount'])) ? money_format("%!^i", (float)$invoice['shipping_received_amount']) : FALSE,
								'order_details_id' => (!empty($invoice['order_details_id'])) ? $invoice['order_details_id'] : FALSE,
								'coupon_name' => (!empty($invoice['coupon_name'])) ? $invoice['coupon_name'] : FALSE,
								'coupon_code' => (!empty($invoice['coupon_code'])) ? $invoice['coupon_code'] : FALSE,
								'coupon_id' => (!empty($invoice['coupon_id'])) ? $invoice['coupon_id'] : FALSE,
								'billing_address' => array(
														'first_name' => $invoice['first_name'],
														'last_name' => $invoice['last_name'],
														'company' => $invoice['company'],
														'address_1' => $invoice['address_1'],
														'address_2' => $invoice['address_2'],
														'city' => $invoice['city'],
														'state' => $invoice['state'],
														'country' => $invoice['iso2'],
														'postal_code' => $invoice['postal_code'],
														'email' => $invoice['email'],
														'phone_number' => $invoice['phone']
												),
							);

			// If we're using a dynamic shipping module, then we might
			// have a better description of the name we can use.
			if (isset($invoice['shipping_desc']) && !empty($invoice['shipping_desc']))
			{
				$t['shipping_name'] = $invoice['shipping_desc'];
			}

			// If there's any chance a module was associated with this order, send that along
			if (isset($invoice['module']) && !empty($invoice['module']))
			{
				$t['module'] = $invoice['module'];
			}

			$invoices[] = $t;
		}

		return $invoices;
	}
}