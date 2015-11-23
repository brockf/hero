<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admincp extends Admincp_Controller {

	//--------------------------------------------------------------------
	
	public function __construct() 
	{
		parent::__construct();
		
		// Set the active nav tab to 'Storefront'
		$this->admin_navigation->parent_active('storefront');
		
		$this->load->model('coupon_model');
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Manage Coupons
	 * 
	 * Displays the list of current coupons.
	 */ 
	public function index() 
	{
		$this->admin_navigation->module_link('Add Coupon',site_url('admincp/coupons/add'));
	
		$this->load->library('dataset');
		
		// Get coupon types
		$coupon_types = $this->coupon_model->get_coupon_types();
		foreach ($coupon_types as $type)
		{
			$coupon_options[$type->coupon_type_id] = $type->coupon_type_name; 
		}
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'id'),
						array(
							'name' => 'Coupon Name',
							'sort_column' => 'coupon_name',
							'type' => 'text',
							'width' => '20%',
							'filter' => 'name'),
						array(
							'name' => 'Code',
							'type'	=> 'text',
							'width' => '15%',
							'filter' => 'code'
							),
						array(
							'name' => 'Active Dates',
							'type'	=> 'date',
							'sort_column' => 'coupon_start_date',
							'width' => '15%',
							'filter' => 'timestamp',
							'field_start_date' => 'start_date',
							'field_end_date' => 'end_date'
							),
						array(
							'name'	=> 'Coupon Type',
							'type'	=> 'select',
							'options' => $coupon_options,
							'width' => '15%',
							'filter'	=> 'type'
						)
					);
	
		$this->dataset->columns($columns);
		$this->dataset->datasource('coupon_model','get_coupons');
		$this->dataset->base_url(site_url('admincp/coupons'));
		$this->dataset->rows_per_page(1000);
		
		// total rows
		$this->db->select('coupon_id');
		$this->db->where('coupon_deleted', '0');
		$total_rows = $this->db->get('coupons')->num_rows(); 
		$this->dataset->total_rows($total_rows);
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/coupons/delete_coupons');
	
		$this->load->view('coupons', array('coupon_options'=>$coupon_options));
	}
	
	//--------------------------------------------------------------------
	
	public function add() 
	{
		$form = $this->build_coupon_form();
		
		// Prep our page
		$data = array(
			'form'			=> $form->display(),
			'form_title'	=> 'Create New Coupon',
			'action'		=> 'new',
			'form_action'	=> site_url('admincp/coupons/post_coupon/new')
		);
		
		$this->load->view('add', $data);
	}
	
	//--------------------------------------------------------------------
	
	public function edit($id=0) 
	{
		// Grab our coupon data
		$coupon = $this->coupon_model->get_coupon($id);
	
		$form = $this->build_coupon_form($coupon, $id);
		
		// Prep our page
		$data = array(
			'form'			=> $form->display(),
			'form_title'	=> 'Edit Coupon',
			'action'		=> 'edit',
			'form_action'	=> site_url('admincp/coupons/post_coupon/edit/'.$id)
		);
		
		$this->load->view('edit', $data);
	}
	
	//--------------------------------------------------------------------
	
	
	public function post_coupon($action = 'edit', $id = FALSE) 
	{	
		$editing = $action == 'edit' ? TRUE : FALSE;
	
		$validated = $this->coupon_model->validation($editing);
		if ($validated !== TRUE) {
			$this->notices->SetError(implode('<br />',$validated));
			$error = TRUE;
		}
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/coupons/add');
				return FALSE;
			}
			else {
				redirect('admincp/coupons/edit/' . $id);
				return FALSE;
			}	
		}
		
		if ($action == 'new')
		{
			// New coupon
			$coupon = $this->build_coupon_form_input();
			$coupon_id = $this->coupon_model->new_coupon(
													$coupon['coupon_name'],
													$coupon['coupon_code'],
													$coupon['coupon_start_date'],
													$coupon['coupon_end_date'],
													$coupon['coupon_max_uses'],
													$coupon['coupon_customer_limit'],
													$coupon['coupon_type_id'],
													$coupon['coupon_reduction_type'],
													$coupon['coupon_reduction_amt'],
													$coupon['coupon_trial_length'],
													$coupon['coupon_min_cart_amt'],
													$coupon['products'],
													$coupon['plans'],
													$coupon['ship_rates']
												);
			
			if ($coupon_id)
			{
				$this->notices->SetNotice('Coupon added successfully.');
			} else 
			{
				$this->notices->SetError('Unable to create coupon.');
			}
		} else 
		{
			// Edited coupon
			$coupon_id = $this->input->post('coupon_id');
			$coupon = $this->build_coupon_form_input();
			$result = $this->coupon_model->update_coupon(
													$coupon_id,
													$coupon['coupon_name'],
													$coupon['coupon_code'],
													$coupon['coupon_start_date'],
													$coupon['coupon_end_date'],
													$coupon['coupon_max_uses'],
													$coupon['coupon_customer_limit'],
													$coupon['coupon_type_id'],
													$coupon['coupon_reduction_type'],
													$coupon['coupon_reduction_amt'],
													$coupon['coupon_trial_length'],
													$coupon['coupon_min_cart_amt'],
													$coupon['products'],
													$coupon['plans'],
													$coupon['ship_rates']
												);
			
			if ($result)
			{
				$this->notices->SetNotice('Coupon saved successfully.');
			} else 
			{
				$this->notices->SetError('Unable to save coupon.');
			}
		}
		
		redirect('admincp/coupons');
		
		return TRUE;
	}
	
	//--------------------------------------------------------------------
	
	function delete_coupons ($coupons, $return_url) {
		$this->load->library('asciihex');
		
		$coupons = unserialize(base64_decode($this->asciihex->HexToAscii($coupons)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($coupons as $coupon) {
			$this->coupon_model->delete_coupon($coupon);
		}
		
		$this->notices->SetNotice('Coupon(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	//--------------------------------------------------------------------
	// PRIVATE METHODS
	//--------------------------------------------------------------------
	
	private function build_coupon_form($coupon=array(), $id=0) 
	{
		$this->load->model('store/shipping_model');
		$this->load->model('store/products_model');
		$this->load->model('billing/subscription_plan_model');
		
		// Get the required options
		$coupon_products 		= isset($coupon['products']) ? $coupon['products'] : array();
		$coupon_plans			= isset($coupon['plans']) ? $coupon['plans'] : array();
		$coupon_shipping 		= isset($coupon['shipping']) ? $coupon['shipping'] : array();
		
		$coupon_types = $this->coupon_model->get_coupon_types();
		
		foreach ($coupon_types as $type)
		{
			$type_options[$type->coupon_type_id] = $type->coupon_type_name;
		}
		
		$reduction_options = array(
			0	=> '%',
			1	=> setting('currency_symbol')
		);
		
		$products = $this->products_model->get_products();
		$product_options = array();
		$product_options['-1'] = 'not available for any products';
		
		if (is_array($products)) {
			foreach ($products as $product)
			{
				$product_options[$product['id']] = $product['name'];
			}
		}
		
		$plans = $this->subscription_plan_model->get_plans();
		$plan_options = array();
		$plan_options['-1'] = 'not available for any subscriptions';
		
		if (is_array($plans)) {
			foreach ($plans as $plan)
			{
				$plan_options[$plan['id']] = $plan['name'];
			}
		}
		
		$shipping = $this->shipping_model->get_rates();
		$shipping_options = array();
		
		if (is_array($shipping)) {
			foreach ($shipping as $rate)
			{
				$shipping_options[$rate['id']] = $rate['name'];
			}
		}
		
		// Build the form
		$this->load->library('admin_form');
		
		$form = new Admin_form;
		$form->fieldset('Coupon Information');
		$form->hidden('coupon_id', $id);
		$form->text('Coupon Name', 'coupon_name', isset($coupon['coupon_name']) ? $coupon['coupon_name'] : null, 'Something for you to recognize the coupon by.', TRUE);
		$form->text('Coupon Code', 'coupon_code', isset($coupon['coupon_code']) ? $coupon['coupon_code'] : null, 'The code the customer must enter.', TRUE);
		$form->date('Start Date', 'coupon_start_date', isset($coupon['coupon_start_date']) ? $coupon['coupon_start_date'] : null, null, TRUE, FALSE, FALSE, '8em');
		$form->date('Expiry Date', 'coupon_end_date', isset($coupon['coupon_end_date']) ? $coupon['coupon_end_date'] : null, null, TRUE, FALSE, FALSE, '8em');
		$form->text('Maximum Uses', 'coupon_max_uses', (isset($coupon['coupon_max_uses']) and !empty($coupon['coupon_max_uses'])) ? $coupon['coupon_max_uses'] : null, 'The maximum number of customers that can use the coupon.', FALSE, FALSE, FALSE, '6em');
		$form->checkbox('One Per Customer?', 'coupon_customer_limit', '1', isset($coupon['coupon_customer_limit']) && $coupon['coupon_customer_limit'] == 1 ? TRUE : FALSE, 'Check to limit each customer to a single use.');
		$form->dropdown('Coupon Type', 'coupon_type_id', $type_options, isset($coupon['coupon_type_id']) ? $coupon['coupon_type_id'] : FALSE, FALSE, FALSE, FALSE, FALSE, 'coupon_type');
		
		$form->fieldset('Price Reduction', array('coupon_reduction'));
		$form->dropdown('Reduction Type', 'coupon_reduction_type', $reduction_options, isset($coupon['coupon_reduction_type']) ? $coupon['coupon_reduction_type'] : FALSE);
		$form->text('Reduction Amount', 'coupon_reduction_amt', isset($coupon['coupon_reduction_amt']) ? $coupon['coupon_reduction_amt'] : null, 'The amount of the discount.', FALSE, FALSE, FALSE, '6em');
		$form->dropdown('Products', 'products[]', $product_options, $coupon_products, TRUE, FALSE, 'Leave all unselected to make available for all products.');
		$form->dropdown('Subscription Plans', 'plans[]', $plan_options, $coupon_plans, TRUE, FALSE, 'Leave all unselected to make available for all subscriptions.');
		
		$form->fieldset('Free Trial', array('coupon_trial'));
		$form->text('Free Trial Length', 'coupon_trial_length', isset($coupon['coupon_trial_length']) ? $coupon['coupon_trial_length'] : null, null, FALSE, 'in days', FALSE, '6em');
		$form->dropdown('Subscription Plans', 'trial_subs[]', $plan_options, $coupon_plans, TRUE, 'If left blank, will select ALL SUBSCRIPTION PLANS');
		
		$form->fieldset('Free Shipping', array('coupon_shipping'));
		$form->text('Min. Cart Amount', 'coupon_min_cart_amt', isset($coupon['coupon_min_cart_amt']) ? $coupon['coupon_min_cart_amt'] : null, 'The minimum order amount before the coupon may be used.', FALSE, FALSE, FALSE, '6em');
		$form->dropdown('Shipping Methods', 'ship_rates[]', $shipping_options, $coupon_shipping, TRUE, 'If left blank, will select ALL SHIPPING PLANS');
		
		return $form;
	}
	
	//--------------------------------------------------------------------
	
	
	/**
	 * builds the coupon item from form input data.
	 * This is used by both add and edit forms.
	 */
	private function build_coupon_form_input() 
	{	
		$coupon = array(
			'coupon_name'			=> $this->input->post('coupon_name'),
			'coupon_code'			=> $this->input->post('coupon_code'),
			'coupon_start_date'		=> $this->input->post('coupon_start_date'),
			'coupon_end_date'		=> $this->input->post('coupon_end_date'),
			'coupon_max_uses'		=> $this->input->post('coupon_max_uses'),
			'coupon_customer_limit'	=> $this->input->post('coupon_customer_limit'),
			'coupon_type_id'		=> $this->input->post('coupon_type_id'),
			'coupon_reduction_type' => null,	// Null the following so we at least have placeholder values.
			'coupon_reduction_amt'	=> null,
			'coupon_trial_length'	=> null,
			'coupon_min_cart_amt'	=> null,
			'products'				=> null,
			'plans'					=> null,
			'ship_rates'			=> null
		);
		
		
		switch($this->input->post('coupon_type_id')) {
			// Price Reduction
			case 1: 
				$coupon['coupon_reduction_type'] = $this->input->post('coupon_reduction_type');
				$coupon['coupon_reduction_amt'] = $this->input->post('coupon_reduction_amt');
				$coupon['products'] = $this->input->post('products');
				$coupon['plans'] = $this->input->post('plans');
				break;
		
			// Free Trial
			case 2: 
				$coupon['coupon_trial_length'] = $this->input->post('coupon_trial_length');
				$coupon['plans'] = $this->input->post('trial_subs');
				break;
		
			// Free Subscription
			case 3: 
				$coupon['coupon_min_cart_amt'] = $this->input->post('coupon_min_cart_amt');
				$coupon['ship_rates'] = $this->input->post('ship_rates');	
				break;
		}
			
		return $coupon;
	}
	
	//--------------------------------------------------------------------
	
}

/* End of file admincp.php */
/* Location: ./app/modules/coupons/admincp.php */