<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Reports Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();

		$this->admin_navigation->parent_active('reports');
	}

	function cronjob () {
		$this->admin_navigation->parent_active('configuration');

		$this->admin_navigation->module_link('Run Cronjob Manually',site_url('cron/update/' . setting('cron_key')));

		return $this->load->view('cronjob');
	}

	function coupons () {
		$this->load->library('dataset');

		$columns = array(
						array(
							'name' => 'Coupon Name',
							'type' => 'text',
							'width' => '30%',
							'sort_column' => 'coupons.coupon_name'),
						array(
							'name' => 'Coupon Code',
							'type' => 'text',
							'width' => '30%',
							'filter' => 'code_search',
							'sort_column' => 'coupons.coupon_code'),
						array(
							'name' => 'Subscription Uses',
							'type' => 'text',
							'width' => '15%',
							'sort_column' => 'order_usages'
							),
						array(
							'name' => 'Product Uses',
							'type' => 'text',
							'width' => '15%',
							'sort_column' => 'subscription_usages'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('coupons/coupon_model','get_coupon_usages');
		$this->dataset->base_url(site_url('admincp/reports/coupons'));

		// initialize the dataset
		$this->dataset->initialize(FALSE);

		// count total rows
		$this->load->model('coupons/coupon_model');
		$total_rows = $this->coupon_model->count_coupons($this->dataset->get_unlimited_parameters());
		$this->dataset->total_rows($total_rows);
		$this->dataset->initialize_pagination();

		$this->load->view('coupons');
	}

	function invoices () {
		$this->load->library('dataset');

		// get gateway options
		$this->load->model('billing/gateway_model');
		$gateways = $this->gateway_model->GetGateways();

		$gateway_options = array();
		if (!empty($gateways)) {
			foreach ($gateways as $gateway) {
				$gateway_options[$gateway['id']] = $gateway['gateway'];
			}
		}

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '10%',
							'filter' => 'id'),
						array(
							'name' => 'Customer Name',
							'sort_column' => 'customers.last_name',
							'type' => 'text',
							'width' => '15%',
							'filter' => 'member_name'),
						array(
							'name' => 'Amount',
							'sort_column' => 'amount',
							'type' => 'text',
							'width' => '7%',
							'filter' => 'amount'),
						array(
							'name' => 'Date',
							'sort_column' => 'timestamp',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'timestamp',
							'field_start_date' => 'start_date',
							'field_end_date' => 'end_date'),
						array(
							'name' => 'Gateway',
							'sort_column' => 'gateways.alias',
							'type' => 'select',
							'options' => $gateway_options,
							'width' => '15%',
							'filter' => 'gateway'),
						array(
							'name' => 'Subscription',
							'width' => '10%',
							'type' => 'text',
							'filter' => 'subscription_id'
							),
						array(
							'name' => '',
							'width' => '23%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('billing/invoice_model','get_invoices');
		$this->dataset->base_url(site_url('admincp/reports/invoices'));

		// initialize the dataset
		$this->dataset->initialize(FALSE);

		// count total rows
		$this->load->model('billing/invoice_model');
		$total_rows = $this->invoice_model->count_invoices($this->dataset->get_unlimited_parameters());
		$this->dataset->total_rows($total_rows);
		$this->dataset->initialize_pagination();

		$this->load->model('billing/invoice_model');

		// get total charges
		$total_amount = $this->invoice_model->get_invoices_total($this->dataset->params);

		$data = array(
					'total_amount' => $total_amount
					);

		$this->load->view('invoices', $data);
	}

	function invoice_actions ($action, $id) {
		$this->load->model('billing/invoice_model');
		$invoice = $this->invoice_model->get_invoice($id);

		if ($action == 'details') {
			redirect('admincp/reports/invoice/' . $invoice['id']);
		}
		elseif ($action == 'refund') {
			redirect('admincp/reports/do_refund/' . $invoice['id']);
		}
		elseif ($action == 'profile') {
			redirect('admincp/users/profile/' . $invoice['user_id']);
		}
		elseif ($action == 'related_charges') {
			header('Location: ' . dataset_link('admincp/reports/invoices', array('subscription_id' => $invoice['subscription_id'])));
		}

		return TRUE;
	}

	function invoice ($invoice_id) {
		$this->admin_navigation->module_link('Back to Invoices',site_url('admincp/reports/invoices'));

		$this->load->helper('format_street_address');

		$this->load->model('billing/invoice_model');
		$invoice = $this->invoice_model->get_invoice($invoice_id);

		// refund?
		if ($invoice['refunded'] == FALSE) {
			$this->admin_navigation->module_link('Issue Refund',site_url('admincp/reports/do_refund/' . $invoice['id']));
		}

		// get invoice lines
		$invoice_lines = $this->invoice_model->invoice_lines($invoice['id']);

		// get shipping address if there is one
		if ($invoice['order_details_id']) {
			$this->load->model('store/order_model');
			$order = $this->order_model->get_order($invoice['order_details_id']);
		}
		else {
			$order = FALSE;
		}

		$data = array(
						'invoice' => $invoice,
						'order' => $order,
						'invoice_lines' => $invoice_lines
					);

		$this->load->view('invoice', $data);
	}

	function do_refund ($invoice_id) {
		$this->load->model('billing/invoice_model');
		$invoice = $this->invoice_model->get_invoice($invoice_id);

		if (empty($invoice)) {
			die(show_error('No invoice by that ID.'));
		}

		$this->load->model('billing/gateway_model');
		if ($this->gateway_model->Refund($invoice['id'])) {
			redirect('admincp/reports/invoice/' . $invoice['id']);
			return TRUE;
		}
		else {
			return $this->load->view('mark_refund', array('invoice' => $invoice));
		}
	}

	function mark_refunded ($invoice_id) {
		$this->load->model('billing/invoice_model');
		$invoice = $this->invoice_model->get_invoice($invoice_id);

		if (empty($invoice)) {
			die(show_error('No invoice by that ID.'));
		}

		$this->load->model('billing/charge_model');
		$this->charge_model->MarkRefunded($invoice['id']);

		redirect('admincp/reports/invoice/' . $invoice['id']);
		return TRUE;
	}


	function products () {
		$this->load->library('dataset');
		$this->load->helper('format_street_address');

		$columns = array(
						array(
							'name' => 'Invoice #',
							'type' => 'id',
							'width' => '7%',
							'filter' => 'invoice'),
						array(
							'name' => 'Product',
							'sort_column' => 'products.product_name',
							'type' => 'text',
							'width' => '23%',
							'filter' => 'product_name'),
						array(
							'name' => 'QTY',
							'width' => '2%'
							),
						array(
							'name' => 'Date',
							'sort_column' => 'timestamp',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'timestamp',
							'field_start_date' => 'start_date',
							'field_end_date' => 'end_date'),
						array(
							'name' => 'Shipped',
							'width' => '12%',
							'type' => 'select',
							'options' => array('1' => 'Shipped', '0' => 'Not Shipped'),
							'filter' => 'shipped'
							),
						array(
							'name' => 'Shipping Details',
							'width' => '15%'
							),
						array(
							'name' => '',
							'width' => '31%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('store/order_model','get_order_products', array('refunded' => FALSE));
		$this->dataset->base_url(site_url('admincp/reports/products'));

		$this->load->model('store/order_model');
		$total_rows = $this->order_model->count_orders($this->dataset->get_unlimited_parameters());
		$this->dataset->total_rows($total_rows);

		$this->dataset->Initialize();

		$this->load->view('products');
	}

	function shipped_no ($order_products_id) {
		$this->load->model('store/order_model');

		$this->order_model->mark_as_not_shipped($order_products_id);
	}

	function shipped_yes ($order_products_id) {
		$this->load->model('store/order_model');

		$this->order_model->mark_as_shipped($order_products_id);
	}

	function subscriptions () {
		$this->load->library('dataset');

		// get subscription plans
		$this->load->model('billing/subscription_plan_model');
		$plans = $this->subscription_plan_model->get_plans();

		if (empty($plans)) {
			$plan_options = array();
		}
		else {
			foreach ($plans as $plan) {
				$plan_options[$plan['id']] = $plan['name'];
			}
		}

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '7%',
							'filter' => 'id'),
						array(
							'name' => 'Member',
							'sort_column' => 'users.user_last_name',
							'type' => 'text',
							'width' => '18%',
							'filter' => 'member_name'),
						array(
							'name' => 'Subscription',
							'sort_column' => 'plans.name',
							'width' => '15%',
							'type' => 'select',
							'options' => $plan_options,
							'filter' => 'plan_id'
							),
						array(
							'name' => 'Price',
							'sort_column' => 'subscriptions.amount',
							'width' => '10%',
							'type' => 'text',
							'filter' => 'amount'
							),
						array(
							'name' => 'Start Date',
							'sort_column' => 'start_date',
							'type' => 'date',
							'width' => '10%',
							'filter' => 'start_date',
							'field_start_date' => 'created_after',
							'field_end_date' => 'created_before'),
						array(
							'name' => 'Next Charge',
							'sort_column' => 'next_charge',
							'width' => '10%'
							),
						array(
							'name' => 'End Date',
							'sort_column' => 'end_date',
							'type' => 'date',
							'width' => '10%',
							'filter' => 'end_date',
							'field_start_date' => 'end_date_after',
							'field_end_date' => 'end_date_before'
							),
						array(
							'name' => 'Status',
							'width' => '12%',
							'type' => 'select',
							'options' => array('recurring' => 'Recurring', 'will_expire' => 'Will Expire', 'expired' => 'Expired', 'renewed' => 'Renewed', 'updated' => 'Updated CC'),
							'filter' => 'status'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('billing/subscription_model','get_subscriptions');
		$this->dataset->base_url(site_url('admincp/reports/subscriptions'));
		$this->dataset->initialize(FALSE);

		// count total rows
		$this->load->model('billing/subscription_model');
		$total_rows = $this->subscription_model->count_subscriptions($this->dataset->get_unlimited_parameters());
		$this->dataset->total_rows($total_rows);
		$this->dataset->initialize_pagination();

		$this->load->view('subscriptions');
	}

	function subscription_actions ($action, $id) {
		$this->load->model('billing/subscription_model');
		$subscription = $this->subscription_model->get_subscription($id);

		if ($action == 'cancel') {
			if ($this->subscription_model->cancel_subscription($subscription['id'])) {
				$this->notices->SetNotice('Subscription cancelled successfully.');
			}
			else {
				$this->notices->SetError('There was an error cancelling this subscription.');
			}
			header('Location: ' . dataset_link('admincp/reports/subscriptions', array('id' => $subscription['id'])));
		}
		elseif ($action == 'change_plan') {
			redirect('admincp/billing/change_plan/' . $subscription['id']);
		}
		elseif ($action == 'change_price') {
			redirect('admincp/billing/change_price/' . $subscription['id']);
		}
		elseif ($action == 'profile') {
			redirect('admincp/users/profile/' . $subscription['user_id']);
		}
		elseif ($action == 'related_charges') {
			header('Location: ' . dataset_link('admincp/reports/invoices', array('subscription_id' => $subscription['id'])));
		}

		return TRUE;
	}
	
	function subscription_log ($subscription_id) {
		$this->load->library('dataset');

		// get subscription plans
		$this->load->model('billing/subscription_model');
		$subscription = $this->subscription_model->get_subscription($subscription_id);
		
		if (empty($subscription)) {
			die(show_error('Unable to locate subscription record.'));
		}

		$columns = array(
						array(
							'name' => 'Date',
							'type' => 'text',
							'width' => '15%'),
						array(
							'name' => 'Event/Data',
							'type' => 'text',
							'width' => '20%'),
						array(
							'name' => 'IP',
							'width' => '15%',
							'type' => 'text'),
						array(
							'name' => 'Browser',
							'width' => '25%',
							'type' => 'text'),
						array(
							'name' => 'Code',
							'width' => '25%',
							'type' => 'text')
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('billing/subscription_model','get_log', array('id' => $subscription['id']));
		$this->dataset->base_url(site_url('admincp/reports/subscription_log'));
		$this->dataset->initialize(TRUE);

		$this->load->view('subscription_log');
	}

	function cancellations () {
		$this->load->library('dataset');

		// get subscription plans
		$this->load->model('billing/subscription_plan_model');
		$plans = $this->subscription_plan_model->get_plans();

		if (empty($plans)) {
			$plan_options = array();
		}
		else {
			foreach ($plans as $plan) {
				$plan_options[$plan['id']] = $plan['name'];
			}
		}

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '7%',
							'filter' => 'id'),
						array(
							'name' => 'Member',
							'sort_column' => 'users.user_last_name',
							'type' => 'text',
							'width' => '18%',
							'filter' => 'member_name'),
						array(
							'name' => 'Subscription',
							'sort_column' => 'plans.name',
							'width' => '15%',
							'type' => 'select',
							'options' => $plan_options,
							'filter' => 'plan_id'
							),
						array(
							'name' => 'Cancel Date',
							'sort_column' => 'cancel_date',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'cancel_date',
							'field_start_date' => 'cancel_date_after',
							'field_end_date' => 'cancel_date_before'
							),
						array(
							'name' => 'Expiry Date',
							'sort_column' => 'end_date',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'end_date',
							'field_start_date' => 'end_date_after',
							'field_end_date' => 'end_date_before'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('billing/subscription_model','get_subscriptions', array('active' => '0', 'sort' => 'subscriptions.cancel_date', 'sort_dir' => 'DESC'));
		$this->dataset->base_url(site_url('admincp/reports/cancellations'));
		$this->dataset->Initialize();

		$this->load->view('cancellations');
	}

	function expirations () {
		$this->load->library('dataset');

		// get subscription plans
		$this->load->model('billing/subscription_plan_model');
		$plans = $this->subscription_plan_model->get_plans();

		if (empty($plans)) {
			$plan_options = array();
		}
		else {
			foreach ($plans as $plan) {
				$plan_options[$plan['id']] = $plan['name'];
			}
		}

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '7%',
							'filter' => 'id'),
						array(
							'name' => 'Member',
							'sort_column' => 'users.user_last_name',
							'type' => 'text',
							'width' => '18%',
							'filter' => 'member_name'),
						array(
							'name' => 'Subscription',
							'sort_column' => 'plans.name',
							'width' => '15%',
							'type' => 'select',
							'options' => $plan_options,
							'filter' => 'plan_id'
							),
						array(
							'name' => 'Cancel Date',
							'sort_column' => 'cancel_date',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'cancel_date',
							'field_start_date' => 'cancel_date_after',
							'field_end_date' => 'cancel_date_before'
							),
						array(
							'name' => 'Expiry Date',
							'sort_column' => 'end_date',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'end_date',
							'field_start_date' => 'end_date_after',
							'field_end_date' => 'end_date_before'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('billing/subscription_model','get_subscriptions', array('status' => 'expired', 'sort' => 'subscriptions.end_date', 'sort_dir' => 'DESC'));
		$this->dataset->base_url(site_url('admincp/reports/expirations'));
		$this->dataset->Initialize();

		$this->load->view('expirations');
	}

	function taxes () {
		$this->load->library('dataset');

		// get tax options
		$this->load->model('store/taxes_model');
		$taxes = $this->taxes_model->get_taxes();

		$tax_options = array();

		if (!empty($taxes)) {
			foreach ($taxes as $tax) {
				$tax_options[$tax['id']] = $tax['name'];
			}
		}

		$columns = array(
						array(
							'name' => 'Invoice ID #',
							'sort_column' => 'invoice_id',
							'type' => 'id',
							'width' => '7%',
							'filter' => 'invoice_id'),
						array(
							'name' => 'Member',
							'sort_column' => 'users.user_last_name',
							'type' => 'text',
							'width' => '18%',
							'filter' => 'member_name'),
						array(
							'name' => 'Tax',
							'sort_column' => 'taxes.tax_name',
							'width' => '15%',
							'type' => 'select',
							'options' => $tax_options,
							'filter' => 'tax'
							),
						array(
							'name' => 'Rate',
							'width' => '6%'
							),
						array(
							'name' => 'Tax Amount',
							'width' => '9%'
							),
						array(
							'name' => 'Date',
							'sort_column' => 'tax_received_date',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'date',
							'field_start_date' => 'date_start',
							'field_end_date' => 'date_end'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('store/taxes_model','get_paid_taxes');
		$this->dataset->base_url(site_url('admincp/reports/taxes'));
		$this->dataset->Initialize();

		// get total
		$total_amount = $this->taxes_model->get_paid_taxes_total($this->dataset->params);

		$data = array(
					'total_amount' => $total_amount
				);

		$this->load->view('taxes', $data);
	}

	function tax_actions ($action, $id) {
		$this->load->model('store/taxes_model');
		$tax = $this->taxes_model->get_paid_tax($id);

		if ($action == 'invoice') {
			redirect('admincp/reports/invoice/' . $tax['invoice_id']);
		}
		elseif ($action == 'profile') {
			redirect('admincp/users/profile/' . $tax['user_id']);
		}

		return TRUE;
	}

	function registrations () {
		$this->load->library('dataset');

		// get usergroups
		$this->load->model('users/usergroup_model');
	    $usergroups = $this->usergroup_model->get_usergroups();

	    $options = array();
	    foreach ($usergroups as $group) {
	    	$options[$group['id']] = $group['name'];
	    }
	    $usergroups = $options;

		$columns = array(
						array(
							'name' => 'Member ID #',
							'type' => 'id',
							'width' => '7%',
							'filter' => 'id'),
						array(
							'name' => 'First Name',
							'sort_column' => 'users.user_first_name',
							'type' => 'text',
							'width' => '10%',
							'filter' => 'first_name'),
						array(
							'name' => 'Last Name',
							'sort_column' => 'users.user_last_name',
							'type' => 'text',
							'width' => '10%',
							'filter' => 'last_name'),
						array(
							'name' => 'Email',
							'sort_column' => 'users.email',
							'type' => 'text',
							'width' => '20%',
							'filter' => 'email'),
						array(
							'name' => 'Date',
							'sort_column' => 'users.user_signup_date',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'date',
							'field_start_date' => 'signup_date_start',
							'field_end_date' => 'signup_date_end'
							),
						array(
							'name' => 'Usergroups',
							'width' => '10%'
							),
						array(
							'name' => '',
							'width' => '20%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('users/user_model','get_users', array('sort' => 'users.user_signup_date', 'sort_dir' => 'DESC'));
		$this->dataset->base_url(site_url('admincp/reports/registrations'));
		$this->dataset->Initialize();

		$data = array(
					'usergroups' => $usergroups
				);

		$this->load->view('registrations', $data);
	}

	function user_actions ($action, $id) {
		$this->load->model('users/user_model');
		$user = $this->user_model->get_user($id);

		if ($action == 'invoices') {
			header('Location: ' . dataset_link('admincp/reports/invoices', array('member_name' => $user['id'])));
		}
		if ($action == 'subscriptions') {
			header('Location: ' . dataset_link('admincp/reports/subscriptions', array('member_name' => $user['id'])));
		}
		elseif ($action == 'profile') {
			redirect('admincp/users/profile/' . $user['id']);
		}

		return TRUE;
	}

	function popular () {
		$this->load->library('dataset');

		$this->load->model('publish/content_type_model');
		$content_types = $this->content_type_model->get_content_types();

		$content_type_options = array();
		if (!empty($content_types)) {
			foreach ($content_types as $type) {
				$content_type_options[$type['id']] = $type['name'];
			}
		}

		$columns = array(
						array(
							'name' => 'Content ID #',
							'type' => 'id',
							'width' => '10%',
							'filter' => 'id'),
						array(
							'name' => 'Title',
							'sort_column' => 'content.content_title',
							'type' => 'text',
							'width' => '30%',
							'filter' => 'title'),
						array(
							'name' => 'Type',
							'sort_column' => 'content.content_type_id',
							'type' => 'select',
							'options' => $content_type_options,
							'width' => '15%',
							'filter' => 'type'),
						array(
							'name' => 'Hits',
							'sort_column' => 'content.content_hits',
							'type' => 'text',
							'width' => '7%',
							'filter' => 'hits'),
						array(
							'name' => 'Publish Date',
							'sort_column' => 'content.content_date',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'date',
							'field_start_date' => 'start_date',
							'field_end_date' => 'end_date'
							),
						array(
							'name' => '',
							'width' => '18%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('publish/content_model','get_contents', array('sort' => 'content.content_hits', 'sort_dir' => 'DESC'));
		$this->dataset->base_url(site_url('admincp/reports/popular'));
		$this->dataset->Initialize();

		$this->load->view('popular');
	}

	function content_actions ($action, $id) {
		$this->load->model('publish/content_model');
		$content = $this->content_model->get_content($id, TRUE);

		if ($action == 'edit') {
			redirect('admincp/publish/edit/' . $content['id']);
		}
		if ($action == 'view') {
			header('Location: ' . $content['url']);
		}

		return TRUE;
	}
}