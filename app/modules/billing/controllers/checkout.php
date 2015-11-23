<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Checkout Module
*
* Checks the user out with products and/or a subscription
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Checkout extends Front_Controller {
	var $requires_shipping; // BOOLEAN - Do we need to require a shipping address?
	var $product_cart; // ARRAY - A local copy of the shopping cart via cart_model->get_cart();
	
	function __construct() {
		parent::__construct();
		
		// if we don't have any products, let's pull the chute
		$this->load->model('store/cart_model');
	}
	
	/**
	* Get Errors and Notices
	*
	* If we have errors/notices in session data, we'll add them to the template for viewing.
	* Note: This should be called within each controller method
	*/
	function get_errors_and_notices () {
		if ($this->session->userdata('errors')) {
			$this->smarty->assign('errors', $this->session->userdata('errors'));
			$this->session->unset_userdata('errors');
		}
		else {
			$this->smarty->assign('errors',FALSE);
		}
		
		if ($this->session->userdata('notices')) {
			$this->smarty->assign('notices', $this->session->userdata('notices'));
			$this->session->unset_userdata('notices');
		}
		else {
			$this->smarty->assign('notices',FALSE);
		}
		
		return TRUE;
	}
	
	/**
	* Prep Cart
	*
	* If the cart has items requiring shipping, we'll assign the $is_shippable var as TRUE.
	* Puts all cart items into a variable.
	*/
	function prep_cart () {
		$this->load->model('store/cart_model');
		
		$requires_shipping = FALSE;
		
		// get cart
		$cart = $this->cart_model->get_cart();
		
		// no cart?
		$totals = $this->cart_model->calculate_totals();
		if (!$totals) {
			die(show_error('You do not have any products or subscriptions in your shopping cart.  <a href="javascript:history.go(-1)">Go back</a>.'));
		}
		else {
			$this->smarty->assign('totals', $totals);
		}
		
		foreach ($cart as $item) {
			if ($item['requires_shipping'] == TRUE) {
				$requires_shipping = TRUE;
			}
		}
		reset($cart);
		
		$this->smarty->assign('cart', $cart);
		$this->smarty->assign('requires_shipping', $requires_shipping);
		
		$this->requires_shipping = $requires_shipping;
		$this->product_cart = $cart;
		
		return;
	}
	
	/**
	* Require Login
	*
	* If this method is called, the user must be logged in - else they get booted back to the account phase
	*/
	function require_login () {
		if ($this->user_model->logged_in() === FALSE) {
			$this->session->set_userdata('errors','<p>You must be logged in at this point of checkout.  Please login and continue checking out.</p>');
			
			return redirect('checkout/account');
		}
		
		return TRUE;
	}
	
	/**
	* Account
	*/
	function index () {
		$this->get_errors_and_notices();
		$this->prep_cart();
		
		// unset coupon
		$this->session->set_userdata('active_coupon',FALSE);
		
		// let's reset everything in our cart
		$this->cart_model->reset_to_precoupon();
	
		if ($this->user_model->logged_in() === FALSE) {
			if ($this->input->get('email') and $this->input->get('email') != '') {
				$email = query_value_decode($this->input->get('email'));
			}
			else {
				$email = '';
			}
			
			$this->smarty->assign('email', $email);
			return $this->smarty->display('checkout_templates/account.thtml');
		}
		else {
			// they are already logged in, pass them forward
			redirect('checkout/billing_shipping');
		}
	}
	
	/**
	* Account Redirect
	*/
	function account () {
		return $this->index();
	}
	
	/**
	* Post Account
	*/
	function post_account () {
		$this->load->library('form_validation');
		$this->load->helper('form');
		
		$this->form_validation->set_rules('type','Account Type','required');
		$this->form_validation->set_rules('email','Email Address','required');
		
		if ($this->form_validation->run() === FALSE) {
			$this->session->set_userdata('errors',validation_errors());
			
			return redirect('checkout?email=' . query_value_encode($this->input->post('email')));
		}

		// are we logging in or creating a new account?
		if ($this->input->post('type') == 'existing_account') {
			// validate fields
			$this->form_validation->set_rules('password','Password','trim|required');
			
			if ($this->form_validation->run() == FALSE) {
				$this->session->set_userdata('errors',validation_errors());
			
				return redirect('checkout?email=' . query_value_encode($this->input->post('email')));
			}
			
			// attempt login
			if ($this->user_model->login($this->input->post('email'), $this->input->post('password'))) {
				// success!
				// redirect to billing/shipping address step
				return redirect('checkout/billing_shipping');
			}
			else {
				if ($this->user_model->failed_due_to_activation == TRUE) {
					$this->session->set_userdata('errors','<p>Login failed.  Your account email has not been activated yet.  Please click the link in your activation email to activate your account.  If you cannot find the email in your inbox or junk folders, contact website support for assistance.');
				}
				else {
					$this->session->set_userdata('errors','<p>Login failed.  Please verify your email and password.');
				}
			
				return redirect('checkout?email=' . query_value_encode($this->input->post('email')));
			}
		}
		else {
			// let's make SURE that this email doesn't already have an account
			if ($this->user_model->unique_email($this->input->post('email')) === FALSE) {
				// this email is already in the system
				// let's show them that error and kick them back
				$this->session->set_userdata('errors','The email address, "' . $this->input->post('email') . '", is already linked to an account.  If you have an account already, please enter your password below and login.  You do not need to register again.');
			
				return redirect('checkout?email=' . query_value_encode($this->input->post('email')));
			}
			else {
				return redirect('checkout/register?email=' . query_value_encode($this->input->post('email')));
			}
		}
	}
	
	/**
	* Register
	*/
	function register () {
		$this->get_errors_and_notices();
		$this->prep_cart();
		
		// do we have a return URL?
		$return = site_url('checkout/billing_shipping?new_account=true');
		
		// get custom fields
		$this->load->model('custom_fields_model');
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => '1'));
		
		// get email
		$email = ($this->input->get('email')) ? query_value_decode($this->input->get('email')) : '';
		
		$this->smarty->assign('email', $email);
		$this->smarty->assign('custom_fields',$custom_fields);
		$this->smarty->assign('return', $return);
		return $this->smarty->display('checkout_templates/register.thtml');
	}
	
	/**
	* Billing & Shipping Addresses
	*/
	function billing_shipping () {
		// is this a free cart?  if so, it doesn't require an address
		// by doing this prior to the methods below, we can retain any notices that may need to be shown
		$this->load->model('store/cart_model');
		if ($this->cart_model->free_cart()) {
			return redirect('checkout/free_confirm');
		}
		
		$this->get_errors_and_notices();
		$this->prep_cart();
		$this->require_login();
		
		// show a new account thank you if we just registered a new account
		if ($this->input->get('new_account') == 'true') {
			$this->session->set_userdata('notices','<p>You have successfully created a new account.  You may continue your checkout below.</p>');
			
			return redirect('checkout/billing_shipping');
		}
		
		// do we have a valid billing address on file?
		$valid_billing_address = ($this->user_model->validate_billing_address($this->user_model->get('id')) == TRUE) ? TRUE : FALSE;
		$billing_address = $this->user_model->get_billing_address($this->user_model->get('id'));
		
		$this->load->helper('format_street_address');
		$formatted_billing_address = format_street_address($billing_address);
		
		// get states & countries
		$this->load->model('states_model');
		$countries = $this->states_model->GetCountries();
		$states = $this->states_model->GetStates();
		
		// billing address values
		if ($this->input->get('billing_values')) {
			$billing_values = unserialize(query_value_decode($this->input->get('billing_values')));
		}
		else {
			if (empty($billing_address)) {
				$billing_values = array(
								'first_name' => $this->user_model->get('first_name'),
								'last_name' => $this->user_model->get('last_name'),
								'address_1' => '',
								'address_2' => '',
								'company' => '',
								'city' => '',
								'state' => '',
								'country' => '',
								'postal_code' => '',
								'phone_number' => ''
								);	
			}
			else {
				$billing_values = array(
								'first_name' => $billing_address['first_name'],
								'last_name' => $billing_address['last_name'],
								'address_1' => $billing_address['address_1'],
								'address_2' => $billing_address['address_2'],
								'company' => $billing_address['company'],
								'city' => $billing_address['city'],
								'state' => $billing_address['state'],
								'country' => $billing_address['country'],
								'postal_code' => $billing_address['postal_code'],
								'phone_number' => $billing_address['phone_number']
								);
			}
		}
		
		// shipping address values
		if ($this->input->get('shipping_values')) {
			$shipping_values = unserialize(query_value_decode($this->input->get('shipping_values')));
		}
		else {
			$shipping_values = array(
							'first_name' => $this->user_model->get('first_name'),
							'last_name' => $this->user_model->get('last_name'),
							'company' => '',
							'city' => '',
							'state' => '',
							'country' => '',
							'postal_code' => '',
							'phone_number' => ''
							);	
		}
		
		$this->smarty->assign('billing_values', $billing_values);
		$this->smarty->assign('shipping_values', $shipping_values);
		$this->smarty->assign('countries', $countries);
		$this->smarty->assign('states', $states);
		$this->smarty->assign('formatted_billing_address',$formatted_billing_address);
		$this->smarty->assign('valid_billing_address',$valid_billing_address);
		$this->smarty->assign('billing_address', $billing_address);
		return $this->smarty->display('checkout_templates/billing_shipping.thtml');
	}
	
	/**
	* Post Billing/Shipping
	*/
	function post_billing_shipping () {
		$this->prep_cart();
		$this->require_login();
		
		$this->load->library('form_validation');
		
		// new or existing billing address?
		if ($this->input->post('billing_address') == 'existing') {
			$billing_address_type = 'existing';
		}
		else {
			$billing_address_type = 'new';
		}
		
		// billing address validation
		
		if ($billing_address_type == 'new') {
			$this->form_validation->set_rules('first_name','First Name','required');
			$this->form_validation->set_rules('last_name','Last Name','required');
			$this->form_validation->set_rules('address_1','Address','required');
			$this->form_validation->set_rules('city','City','required');
			$this->form_validation->set_rules('country','Country','required');
			if ($this->input->post('state') == '') {
				$this->form_validation->set_rules('state_select','State/Province','required');
			}
			$this->form_validation->set_rules('postal_code','Postal/Zip Code','required');
			
			if (isset($_POST['phone_number'])) {
				// we only require this field if it was sent
				// it was added in 3.73 and we don't want to break old sites
				$this->form_validation->set_rules('phone_number','Phone Number','required');
			}
		}
		
		// shipping address validation
		if ($this->requires_shipping == TRUE) {
			$this->form_validation->set_rules('shipping_address','Shipping Address Type','required');
			
			if ($this->input->post('shipping_address') == 'new') {
				$this->form_validation->set_rules('shipping_first_name','First Name','required');
				$this->form_validation->set_rules('shipping_last_name','Last Name','required');
				$this->form_validation->set_rules('shipping_address_1','Address','required');
				$this->form_validation->set_rules('shipping_city','City','required');
				$this->form_validation->set_rules('shipping_country','Country','required');
				if ($this->input->post('shipping_state') == '') {
					$this->form_validation->set_rules('shipping_state_select','State/Province','required');
				}
				$this->form_validation->set_rules('shipping_postal_code','Postal/Zip Code','required');
				
				if (isset($_POST['shipping_phone_number'])) {
					// we only require this field if it was sent
					// it was added in 3.73 and we don't want to break old sites
					$this->form_validation->set_rules('shipping_phone_number','Phone Number','required');
				}
			}
		}
		
		// build arrays of values in case we need to redirect back to the form
		if ($billing_address_type == 'new') {
			$billing_values = array(
								'first_name' => $this->input->post('first_name'),
								'last_name' => $this->input->post('last_name'),
								'company' => $this->input->post('company'),
								'address_1' => $this->input->post('address_1'),
								'address_2' => $this->input->post('address_2'),
								'city' => $this->input->post('city'),
								'country' => $this->input->post('country'),
								'postal_code' => $this->input->post('postal_code'),
								'state' => ($this->input->post('state_select') == '') ? $this->input->post('state') : $this->input->post('state_select'),
								'phone_number' => $this->input->post('phone_number')
							);
		}
		else {
			$billing_values = array();
		}
		
		if ($this->requires_shipping == TRUE and $this->input->post('shipping_address') == 'new') {
			$shipping_values = array(
								'first_name' => $this->input->post('shipping_first_name'),
								'last_name' => $this->input->post('shipping_last_name'),
								'company' => $this->input->post('shipping_company'),
								'address_1' => $this->input->post('shipping_address_1'),
								'address_2' => $this->input->post('shipping_address_2'),
								'city' => $this->input->post('shipping_city'),
								'country' => $this->input->post('shipping_country'),
								'postal_code' => $this->input->post('shipping_postal_code'),
								'state' => ($this->input->post('shipping_state_select') == '') ? $this->input->post('shipping_state') : $this->input->post('shipping_state_select'),
								'phone_number' => $this->input->post('shipping_phone_number')
							);
		}
		else {
			$shipping_values = array();
		}
		
		if (!empty($this->form_validation->_config_rules) and $this->form_validation->run() === FALSE) {
			$this->session->set_userdata('errors',validation_errors());
			
			redirect('checkout/billing_shipping?billing_values=' . query_value_encode(serialize($billing_values)) . '&shipping_values=' . query_value_encode(serialize($shipping_values)));
		}
		
		// we are validated
		
		if ($billing_address_type == 'new') {
			// update their billing address
			
			// to stay compatible with the old UpdateCustomer code
			$billing_values['phone'] = $billing_values['phone_number'];
		
			$this->user_model->update_billing_address($this->user_model->get('id'), $billing_values);
		}
		
		// deal with shipping address
		if ($this->requires_shipping == FALSE) {
			$shipping_address = FALSE;
		}
		elseif ($this->input->post('shipping_address') == 'same') {
			$shipping_address = $this->user_model->get_billing_address($this->user_model->get('id'));
		}
		elseif ($this->input->post('shipping_address') == 'new') {
			$shipping_address = $shipping_values;
		}
		
		// save shipping address in session
		$this->session->set_userdata('shipping_address', $shipping_address);
		
		// trigger checkout_billing_shipping
		$this->load->library('app_hooks');
		$this->app_hooks->trigger('checkout_billing_shipping');
		
		// do we need to redirect to the shipping method selection page?
		if ($shipping_address and $this->requires_shipping) {
			return redirect('checkout/shipping_method');
		}
		else {
			return redirect('checkout/payment');
		}
	}
	
	/**
	* Choose Shipping Method
	*/
	function shipping_method () {
		$this->require_login();
		$this->get_errors_and_notices();
		$this->prep_cart();
		
		// shipping methods?
		if ($this->requires_shipping == TRUE) {
			$this->load->model('store/shipping_model');
			$shipping_rates = $this->shipping_model->get_rates_for_address($this->cart_model->get_cart(), $this->session->userdata('shipping_address'));
			
			if (!$shipping_rates) {
				// we aren't able to ship to this person's country... damn.
				$this->session->set_userdata('errors','<p>Unfortunately, we are unable to ship to the country you selected.  Please choose another shipping address.  We apologize for the inconvenience.</p>');
				
				return redirect('checkout/billing_shipping');
			}
		}
		else {
			$shipping_rates = FALSE;
		}
		
		$this->smarty->assign('shipping_rates', $shipping_rates);
		return $this->smarty->display('checkout_templates/shipping_method.thtml');
	}
	
	/**
	* Post Shipping Method
	*/
	function post_shipping_method () {
		// get available rates
		$this->load->model('store/shipping_model');
		$shipping_rates = $this->shipping_model->get_rates_for_address($this->cart_model->get_cart(), $this->session->userdata('shipping_address'));
		
		if (!array_key_exists($this->input->post('shipping_method'), $shipping_rates)) {
			$this->session->set_userdata('errors','<p>You have selected an invalid shipping method.  Please select another method.</p>');
			
			return redirect('checkout/shipping_method');
		}
		
		// we have a shipping rate
		$rate = $shipping_rates[$this->input->post('shipping_method')];
		
		// get shipping rate to check tax status
		$shipping = $this->shipping_model->get_rate($this->input->post('shipping_method'));
//die('<pre>'. print_r($rate, true));
		// save the shipping rate
		$this->session->set_userdata('shipping_id', $this->input->post('shipping_method'));
		$this->session->set_userdata('shipping_method', $rate['name']);
		$this->session->set_userdata('shipping_rate', $rate['total_rate']);
		$this->session->set_userdata('shipping_is_taxable', $shipping['taxable']);
		
		// trigger checkout_shipping_method
		$this->load->library('app_hooks');
		$this->app_hooks->trigger('checkout_shipping_method');
		
		return redirect('checkout/payment');
	}
	
	/**
	* Payment
	*/
	function payment () {
		$this->require_login();
		$this->get_errors_and_notices();
		$this->prep_cart();
		
		// get cart totals
		$this->load->model('store/cart_model');
		$totals = $this->cart_model->calculate_totals();
		
		if (empty($totals)) {
			die(show_error('Your cart does not have any products or subscriptions in it.  There is nothing to purchase.  <a href="javascript:history.go(-1)">Go back</a>.'));
		}
		
		// redirect to free?
		if ($this->cart_model->free_cart()) {
			return redirect('checkout/free_confirm');
		}
	
		// get gateways
		$this->load->model('billing/gateway_model');
		$gateways = $this->gateway_model->GetGateways(array());
		
		if (is_array($gateways)) {
			foreach ($gateways as $key => $gateway) {
				// is disabled?
				if ($gateway['enabled'] != TRUE) {
					unset($gateways[$key]);
					continue;
				}
				
				// get settings
				$this->load->library('billing/payment/' . $gateway['name']);
				$settings = $this->$gateway['name']->Settings();
				$gateways[$key]['external'] = $settings['external'];
				$gateways[$key]['no_credit_card'] = isset($settings['no_credit_card']) ? $settings['no_credit_card'] : FALSE;
			}
		}
		else {
			die(show_error('The administrator has not yet setup any payment gateways.  These can be configured in the control panel in Configuration > Payment Gateways.'));
		}
		
		// month options
		$month_options = array();
		
		for ($i = 1; $i <= 12; $i++) {
			$month = str_pad($i, 2, "0", STR_PAD_LEFT);
			$month_text = date('M',strtotime('2010-' . $month . '-01'));
			$month_options[$month] = $month_text;
		}
		
		// year options
		$year_options = array();
		
		$now = date('Y');
		$future = $now + 15;
		for ($i = $now; $i <= $future; $i++) {
			$year_options[$i] = $i;
		}
		
		// do we have a shipping method name?
		if ($this->session->userdata('shipping_method')) {
			$shipping_method = $this->session->userdata('shipping_method');
		}
		else {
			$shipping_method = FALSE;
		}
		
		// has coupons
		$this->load->model('coupons/coupon_model');
		$has_coupons = $this->coupon_model->has_coupons();
		
		if ($this->session->userdata('active_coupon')) {
			$coupon = $this->coupon_model->get_coupon($this->session->userdata('active_coupon'));
			$active_coupon = $coupon['coupon_code'];
		}
		else {
			$active_coupon = FALSE;
		}
		
		$this->smarty->assign('active_coupon', $active_coupon);
		$this->smarty->assign('has_coupons',$has_coupons);
		$this->smarty->assign('shipping_method', $shipping_method);
		$this->smarty->assign('month_options', $month_options);
		$this->smarty->assign('year_options', $year_options);
		$this->smarty->assign('totals', $totals);
		$this->smarty->assign('gateways', $gateways);
		return $this->smarty->display('checkout_templates/payment.thtml');
	}
	
	/**
	* Post Payment
	*/
	function post_payment () {
		$this->require_login();
		
		// calculate totals to make sure we're not getting scammed
		$this->load->model('store/cart_model');
		$totals = $this->cart_model->calculate_totals();
		
		if (empty($totals)) {
			$this->session->set_userdata('errors','<p>Your cart is empty.</p>');
			
			return redirect('checkout/payment');
		}
		
		// is this a coupon post?
		if ($this->input->post('coupon') and $this->input->post('coupon') != '') {
			$this->load->model('coupons/coupon_model');
			$coupon = $this->coupon_model->get_coupons(array(
												'code' => $this->input->post('coupon')
											));
			
			// no coupon
			if (empty($coupon)) {
				$this->session->set_userdata('errors','<p>The coupon code you entered is invalid.</p>');		
				return redirect('checkout/payment');
			}

			$coupon = $coupon[0];
			
			// coupon not yet started
			if (strtotime($coupon['start_date']) > time()) {
				$this->session->set_userdata('errors','<p>The coupon code you entered is invalid.</p>');
			
				return redirect('checkout/payment');
			}
			
			// coupon expired or too many uses
			if (strtotime($coupon['end_date'])+(60*60*24) < time() or (!empty($coupon['max_uses']) and $coupon['max_uses'] <= $this->coupon_model->count_uses($coupon['id']))) {
				$this->session->set_userdata('errors','<p>The coupon code you entered has expired.</p>');
			
				return redirect('checkout/payment');
			}
			
			// one per customer limit?
			$customer_id = $this->user_model->get_customer_id($this->session->userdata('user_id'));
			if (!empty($coupon['customer_limit']) and $coupon['customer_limit'] <= $this->coupon_model->customer_usage($coupon['id'], $customer_id)) {
				$this->session->set_userdata('errors','<p>You have reached the limit for usage of this coupon.</p>');
			
				return redirect('checkout/payment');
			}
			
			// shipping min. cart amount
			if ($coupon['type_id'] == '3' and $coupon['min_cart_amt'] > $totals['order_sub_total']) {
				$this->session->set_userdata('errors','<p>You must have ' . setting('currency_symbol') . money_format($coupon['min_cart_amt']) . ' in your cart to be eligible for free shipping.</p>');
			
				return redirect('checkout/payment');
			}
			
			// allow for custom coupon validation
			$this->app_hooks->data('member', $this->user_model->get('id'));
			$this->app_hooks->trigger('coupon_validate', $coupon['id']);
			
			// the coupon must be valid and good!
			
			// let's reset everything in our cart
			$this->cart_model->reset_to_precoupon();
			
			// does the coupon get applied?
			$p_applied = null;
			$s_applied = null;
			
			if ($coupon['type_id'] == '1') {
				// price reduction
				$is_percentage = $coupon['reduction_type'] == '1' ? FALSE : TRUE;
				
				// get linked subscriptions/products
				$subs = $this->coupon_model->get_related($coupon['id'], 'coupons_subscriptions', 'subscription_plan_id');
				$products = $this->coupon_model->get_related($coupon['id'], 'coupons_products', 'product_id');
				
				$this->cart_model->reduce_subscription_prices($subs, $coupon['reduction_amt'], $is_percentage);
				$p_applied = $this->cart_model->reduce_product_prices($products, $coupon['reduction_amt'], $is_percentage);
			}
			elseif ($coupon['type_id'] == '2') {
				// get linked subscriptions/products
				$subs = $this->coupon_model->get_related($coupon['id'], 'coupons_subscriptions', 'subscription_plan_id');
				
				$this->cart_model->update_subscription_trial($subs, $coupon['trial_length']);
			}
			elseif ($coupon['type_id'] == '3') {
				$this->session->set_userdata('shipping_rate','0');
			}
			
			$this->session->set_userdata('active_coupon', $coupon['id']);
			
			// Set our default coupon applied message...
			$this->session->set_userdata('notices','<p>Coupon successfully applied.</p>');
			
			// If it didn't match a specific product, change the notice. 
			if ($p_applied === 1)
			{
				$this->session->set_userdata('notices','<p>Coupon does not match any products in your cart.</p>');
			}
						
			return redirect('checkout/payment');
		}
		
		// get gateway
		$this->load->model('billing/gateway_model');
		$gateway = $this->gateway_model->GetGatewayDetails($this->input->post('method'));
		
		if (empty($gateway)) {
			$this->session->set_userdata('errors','<p>The payment method you selected is no longer valid.</p>');
			
			return redirect('checkout/payment');
		}
		
		$this->load->library('billing/payment/' . $gateway['name']);
		$settings = $this->$gateway['name']->Settings();

		// validate fields
		if ($settings['no_credit_card'] == FALSE) {
			$this->load->library('form_validation');
			$this->form_validation->set_rules('cc_number','Credit Card Number','required');
			$this->form_validation->set_rules('cc_name','Name on Card','required');
			$this->form_validation->set_rules('cc_expiry_month','Credit Card Expiry Month','required|is_natural');
			$this->form_validation->set_rules('cc_expiry_year','Credit Card Expiry Year','required|is_natural');
			
			if ($this->form_validation->run() === FALSE) {
				$this->session->set_userdata('errors',validation_errors());
				
				return redirect('checkout/payment');
			}
		}
		
		// set URL's in case they are using an external gateway
		$return_url = site_url('checkout/complete');
		$cancel_url = site_url('checkout/payment');
		
		// prep credit card array
		if ($settings['no_credit_card'] == FALSE) {
			$credit_card = array(
						'card_num' => preg_replace('/[^0-9]+/','',$this->input->post('cc_number')),
						'name' => $this->input->post('cc_name'),
						'exp_month' => $this->input->post('cc_expiry_month'),
						'exp_year' => $this->input->post('cc_expiry_year'),
						'cvv' => $this->input->post('cc_cvv2')
					);
		}
		else {
			$credit_card = FALSE;
		}
					
		// get customer ID
		$customer_id = $this->user_model->get_customer_id($this->session->userdata('user_id'));
		
		// get customer IP
		$customer_ip = $this->input->ip_address();
		
		// coupon ID
		$coupon_id = ($this->session->userdata('active_coupon')) ? $this->session->userdata('active_coupon') : 0;
		
		// if we don't have a subscription, we'll send a normal Charge
		if ($this->cart_model->get_subscription_cart() == FALSE) {
			$response = $this->gateway_model->Charge($gateway['gateway_id'], $totals['order_total'], $credit_card, $customer_id, FALSE, $customer_ip, $return_url, $cancel_url);
		}
		else {
			$subscription = $this->cart_model->get_subscription_cart();
			
			$this->load->model('billing/subscription_plan_model');
			$plan = $this->subscription_plan_model->get_plan($subscription['id']);
			
			$recur = array(
							'plan_id' => $plan['plan_id'],
							'amount' => $totals['recurring_total'],
							'interval' => $subscription['interval'],
							'free_trial' => $subscription['free_trial'],
							'occurrences' => $subscription['occurrences']
						);
						
			$renew = $subscription['renew_subscription_id'];
							
			$response = $this->gateway_model->Recur($gateway['gateway_id'], $totals['order_total'], $credit_card, $customer_id, FALSE, $customer_ip, $recur, $return_url, $cancel_url, $renew, $coupon_id);
		}
		
		// deal with the response (both Charge and Recur have the same response format)
		if (!is_array($response)) {
			$this->session->set_userdata('errors','<p>There was an unexpected server error.  Please contact the administrators to report your issue.</p>');
			
			return redirect('checkout/payment');
		}
		elseif (isset($response['error'])) {
			$this->session->set_userdata('errors','<p>There was an unexpected server error: ' . $response['error_text'] . ' (#' . $response['error'] . ').</p>');
			
			return redirect('checkout/payment');
		}
		elseif (isset($response['response_code']) and $response['response_code'] != 1 and $response['response_code'] != 100) {
			$response['reason'] = (!isset($response['reason']) or empty($response['reason'])) ? '' : '.  ' . $response['reason'];
			$this->session->set_userdata('errors','<p>There was an error processing your transaction: ' . $response['response_text'] . $response['reason'] . ' (#' . $response['response_code'] . ')</p>');
			
			return redirect('checkout/payment');
		}
		else {
			// success!  the charge went through
			
			// trigger checkout_payment
			$this->load->library('app_hooks');
			$this->app_hooks->trigger('checkout_payment');
			
			$this->process_checkout($response, $totals, $coupon_id);
		}
	}
	
	/**
	* Free Confirm - The cart is free, just confirm the purchase
	*/
	function free_confirm () {
		$this->require_login();
		$this->get_errors_and_notices();
		
		// is this cart really free?
		$this->load->model('store/cart_model');
		if ($this->cart_model->free_cart() == FALSE) {
			$this->session->set_userdata('errors','<p>Your cart is not free.  You must enter billing information to complete your purchase.');
			
			return redirect('checkout/billing_shipping');
		}
		
		// get cart totals
		$totals = $this->cart_model->calculate_totals();
		
		if (empty($totals)) {
			die(show_error('Your cart does not have any products or subscriptions in it.  There is nothing to purchase.  <a href="javascript:history.go(-1)">Go back</a>.'));
		}
		
		$this->smarty->assign('totals', $totals);
		return $this->smarty->display('checkout_templates/free_confirm.thtml');
	}
	
	/**
	* Post Free Confirm
	*/
	function post_free_confirm () {
		$this->require_login();
		
		// is this cart really free?
		$this->load->model('store/cart_model');
		if ($this->cart_model->free_cart() == FALSE) {
			$this->session->set_userdata('errors','<p>Your cart is not free.  You must enter billing information to complete your purchase.');
			
			return redirect('checkout/billing_shipping');
		}
		
		// get cart totals
		$totals = $this->cart_model->calculate_totals();
		
		if (empty($totals)) {
			$this->session->set_userdata('errors','<p>Your cart is empty.</p>');
			
			return redirect('checkout/free_confirm');
		}
		
		// set null information
		$credit_card = FALSE;
		$return_url = FALSE;
		$cancel_url = FALSE;
					
		// get customer ID
		$customer_id = $this->user_model->get_customer_id($this->session->userdata('user_id'));
		
		// get customer IP
		$customer_ip = $this->input->ip_address();
		
		// coupon ID
		$coupon_id = ($this->session->userdata('active_coupon')) ? $this->session->userdata('active_coupon') : 0;
		
		// use default gateway - it doesn't matter
		$this->load->model('billing/gateway_model');
		$gateway = $this->gateway_model->GetGatewayDetails($this->config->item('default_gateway'));
		
		if (empty($gateway)) {
			$this->session->set_userdata('errors','<p>There is no default payment gateway enabled to accept this free payment.  You must at least create an Offline payment gateway and set it as the default.</p>');
			
			return redirect('checkout/free_confirm');
		}
		
		$this->load->library('billing/payment/' . $gateway['name']);
		$settings = $this->$gateway['name']->Settings();
		
		// if we don't have a subscription, we'll send a normal Charge
		if ($this->cart_model->get_subscription_cart() == FALSE) {
			$response = $this->gateway_model->Charge($gateway['gateway_id'], $totals['order_total'], $credit_card, $customer_id, FALSE, $customer_ip, $return_url, $cancel_url);
		}
		else {
			$subscription = $this->cart_model->get_subscription_cart();
			
			$this->load->model('billing/subscription_plan_model');
			$plan = $this->subscription_plan_model->get_plan($subscription['id']);
			
			$recur = array(
							'plan_id' => $plan['plan_id'],
							'amount' => $totals['recurring_total'],
							'interval' => $subscription['interval'],
							'free_trial' => $subscription['free_trial'],
							'occurrences' => $subscription['occurrences']
						);
						
			$renew = $subscription['renew_subscription_id'];
						
			$response = $this->gateway_model->Recur($gateway['gateway_id'], $totals['order_total'], $credit_card, $customer_id, FALSE, $customer_ip, $recur, $return_url, $cancel_url, $renew, $coupon_id);
		}
		
		// deal with the response (both Charge and Recur have the same response format)
		if (!is_array($response)) {
			$this->session->set_userdata('errors','<p>There was an unexpected server error.  Please contact the administrators to report your issue.</p>');
			
			return redirect('checkout/payment');
		}
		elseif (isset($response['error'])) {
			$this->session->set_userdata('errors','<p>There was an unexpected server error: ' . $response['error_text'] . ' (#' . $response['error'] . ').</p>');
			
			return redirect('checkout/payment');
		}
		elseif (isset($response['response_code']) and $response['response_code'] != 1 and $response['response_code'] != 100) {
			$response['reason'] = (!isset($response['reason']) or empty($response['reason'])) ? '' : '.  ' . $response['reason'];
			$this->session->set_userdata('errors','<p>There was an error processing your transaction: ' . $response['response_text'] . $response['reason'] . ' (#' . $response['response_code'] . ')</p>');
			
			return redirect('checkout/payment');
		}
		else {
			// success!  the charge went through
			
			// trigger checkout_payment
			$this->load->library('app_hooks');
			$this->app_hooks->trigger('checkout_payment');
			
			$this->process_checkout($response, $totals, $coupon_id);
		}
	}
	
	/**
	* Process Checkout
	*
	* This method is called after a free/paid checkout and does various processing things.
	* For gateways that redirect to external payment, it stores the "charge_id" so that all
	* post-payment processing occurs when the user comes back to the "Complete" page.
	* In the complete function, we check to make sure the order status == 1 to stop people from
	* somehow avoiding checkout and going direct to the complete page.
	*/
	function process_checkout ($response, $totals, $coupon_id = 0) {
		$this->load->model('store/taxes_model');
		$this->load->model('billing/charge_data_model');
		$this->load->model('store/cart_model');
		
		// When the user has a $0 products total but a recurring charge,
		// they will get here without a charge_id because the start_date of their subscription
		// isn't for X days (after the free trial).  This is no good.
		// Without the charge ID, we can't process their cart.
		// So, in this case, we will create a charge ID for $0 and add it to the $response...
		if ((!isset($response['charge_id']) or empty($response['charge_id'])) and $this->cart_model->has_products()) {
			// create a $0 order for today's payment
			$this->load->model('billing/charge_model');
			
			$customer_id = $this->user_model->get_customer_id();
			$customer_ip = $this->input->ip_address();
			
			$order_id = $this->charge_model->CreateNewOrder($this->config->item('default_gateway'), 0, array(), $response['recurring_id'], $customer_id, $customer_ip);
			$this->charge_model->SetStatus($order_id, 1);
			
			$response['charge_id'] = $order_id;
		}
	
		// We don't process the shopping cart, here.  We will just record the charge_id in the
		// user database so that when the charge goes through and the trigger is tripped,
		// the cart will be processed.
		// We may not have a charge_id if it's just a subscription and it's delayed, but that means
		// we don't have any products so that's not a huge deal.
		if (isset($response['charge_id'])) {
			$this->user_model->set_charge_id($this->user_model->get('id'), $response['charge_id']);
			
			// let's save the shipping address and cart totals for processing
			$this->load->model('billing/charge_data_model');
			
			if ($this->session->userdata('shipping_address')) {
				$this->charge_data_model->Save($response['charge_id'], 'shipping_address', serialize($this->session->userdata('shipping_address')));
			}
			
			$this->charge_data_model->Save($response['charge_id'], 'totals', serialize($totals));
			$this->charge_data_model->Save($response['charge_id'], 'coupon_id', $coupon_id);
		}
		
		// if there is a recurring charge, we need to record the recurring tax so that future recurring charges will be taxed
		// the tax for this initial charge will be handled in the store/order_model->process_order() method
		if (isset($response['recurring_id']) and !empty($totals['tax_id']) and !empty($totals['recurring_tax'])) {
			$this->taxes_model->future_subscription_tax($response['recurring_id'], $totals['tax_id'], (float)$totals['recurring_tax']);
		}
		
		// for future use in the "complete" step, we may want the totals for affiliate integration
		$this->session->set_userdata('processed_totals', $totals);
		
		// we're done our stuff - do we need to redirect?
		if (isset($response['redirect'])) {
			header('Location: ' . $response['redirect']);
			return;
		}
		else {
			redirect('checkout/complete');
			return;
		}
	}
	
	/**
	* Complete
	*/
	function complete () {
		$this->require_login();
		
		// we need to finish this order
		if ($this->user_model->get('pending_charge_id')) {
			// has the order been completed?  or are they trying to scam by skipping an external payment?
			$this->db->where('order_id',$this->user_model->get('pending_charge_id'));
			$this->db->where('status','1');
			$result = $this->db->get('orders');
			
			if ($result->num_rows() == 1) {
				// process this order
				$this->load->model('billing/charge_data_model');
				$charge_data = $this->charge_data_model->Get($this->user_model->get('pending_charge_id'));
				
				$shipping_address = (isset($charge_data['shipping_address'])) ? unserialize($charge_data['shipping_address']) : FALSE;
				$totals = unserialize($charge_data['totals']);

				// coupon?
				$coupon_id = isset($charge_data['coupon_id']) ? $charge_data['coupon_id'] : FALSE;
				
				// Shipping Name? These are used for dynamic shipping modules only.
				$shipping_name = $this->session->userdata('shipping_method');
				
				$this->load->model('store/order_model');
				$this->order_model->process_order($this->user_model->get('pending_charge_id'), $this->user_model->get('id'), $totals, $shipping_address, $coupon_id, $shipping_name);
				
				$this->user_model->remove_charge_id($this->user_model->get('id'));
				
				// clear the cart and those variables
				$this->session->unset_userdata('shipping_address');
				$this->session->unset_userdata('totals');
				$this->session->unset_userdata('cart_contents');
				$this->session->unset_userdata('shipping_method');
				$this->session->unset_userdata('shipping_rate');
				
				$this->db->update('users',array('user_cart' => ''), array('user_id' => $this->user_model->get('id')));
				
				$this->cart->destroy();
			}
		}
		
		// contains all the totals of the previous transaction, for affiliate integration
		// these are created prior to the redirect/processing above
		$totals = $this->session->userdata('processed_totals');
				
		$this->smarty->assign('totals', $totals);
		return $this->smarty->display('checkout_templates/complete.thtml');
	}
}