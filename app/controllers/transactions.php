<?php
/**
* Transactions Controller 
*
* Manage transactions, create new transactions
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/
class Transactions extends Controller {

	function Transactions()
	{
		parent::Controller();
		
		// perform control-panel specific loads
		CPLoader();
	}
	
	function index()
	{	
		$this->navigation->PageTitle('Transactions');
		
		$this->load->model('cp/dataset','dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'sort_column' => 'id',
							'type' => 'id',
							'width' => '10%',
							'filter' => 'id'),
						array(
							'name' => 'Status',
							'sort_column' => 'status',
							'type' => 'select',
							'options' => array('1' => 'ok', '2' => 'refunded', '0' => 'failed'),
							'width' => '10%',
							'filter' => 'status'),
						array(
							'name' => 'Date',
							'sort_column' => 'timestamp',
							'type' => 'date',
							'width' => '20%',
							'filter' => 'timestamp',
							'field_start_date' => 'start_date',
							'field_end_date' => 'end_date'),
						array(
							'name' => 'Amount',
							'sort_column' => 'amount',
							'type' => 'text',
							'width' => '10%',
							'filter' => 'amount'),
						array(
							'name' => 'Customer Name',
							'sort_column' => 'customers.last_name',
							'type' => 'text',
							'width' => '20%',
							'filter' => 'customer_last_name'),
						array(
							'name' => 'Credit Card',
							'sort_column' => 'card_last_four',
							'type' => 'text',
							'width' => '12%',
							'filter' => 'card_last_four'),
						array(
							'name' => 'Recurring',
							'width' => '12%',
							'type' => 'text',
							'filter' => 'recurring_id'
							),
						array(
							'name' => '',
							'width' => '16%'
							)
					);
		
		$this->dataset->Initialize('charge_model','GetCharges',$columns);
		
		$this->load->model('charge_model');
		
		// get total charges
		$total_amount = $this->charge_model->GetTotalAmount($this->dataset->params);
		
		// sidebar
		$this->navigation->SidebarButton('Recurring Charges','transactions/recurring');
		$this->navigation->SidebarButton('New Charge','transactions/create');
		
		$data = array(
					'total_amount' => $total_amount
					);
		
		$this->load->view(branded_view('cp/transactions.php'), $data);
	}
	
	function all_recurring ()
	{	
		$this->navigation->PageTitle('Recurring Charges');
		
		$this->load->model('cp/dataset','dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'sort_column' => 'id',
							'type' => 'id',
							'width' => '7%',
							'filter' => 'id'),
						array(
							'name' => 'Status',
							'sort_column' => 'active',
							'type' => 'select',
							'options' => array('1' => 'active','0' => 'inactive'),
							'width' => '10%',
							'filter' => 'active'),
						array(
							'name' => 'Date Created',
							'width' => '13%'),
						array(
							'name' => 'Last Charge',
							'width' => '13%'
							),
						array(
							'name' => 'Next Charge',
							'width' => '12%'
							),
						array(
							'name' => 'Amount',
							'sort_column' => 'amount',
							'type' => 'text',
							'width' => '10%',
							'filter' => 'amount'),
						array(
							'name' => 'Customer Name',
							'sort_column' => 'customers.last_name',
							'type' => 'text',
							'width' => '15%',
							'filter' => 'customer_last_name')
						);
						
		// handle recurring plans if they exist
		$this->load->model('plan_model');
		$plans = $this->plan_model->GetPlans(array());
		
		if ($plans) {
			// build $options
			$options = array();
			while (list(,$plan) = each($plans)) {
				$options[$plan['id']] = $plan['name'];
			}
			
			$columns[] = array(
							'name' => 'Plan',
							'type' => 'select',
							'options' => $options,
							'filter' => 'plan_id',
							'width' => '15%'
							);
		}
		else {
			$columns[] = array(
				'name' => 'Plan',
				'width' => '15%'
				);
		}
		
		$columns[] = array(
							'name' => '',
							'width' => '10%'
							);
		
		$this->dataset->Initialize('recurring_model','GetRecurrings',$columns);
		
		// sidebar
		$this->navigation->SidebarButton('Charge Records','transactions');
		$this->navigation->SidebarButton('New Charge','transactions/create');
		
		$this->load->view(branded_view('cp/recurrings.php'));
	}
	
	/**
	* New Charge
	*
	* Creates a new one-time or recurring-charge
	*
	* @return string viewe
	*/
	function create() {
		$this->navigation->PageTitle('New Transaction');
	
		$this->load->model('states_model');
		$countries = $this->states_model->GetCountries();
		$states = $this->states_model->GetStates();
		
		// load plans if they exist
		$this->load->model('plan_model');
		$plans = $this->plan_model->GetPlans(array());
		
		// load existing customers
		$this->load->model('customer_model');
		$customers = $this->customer_model->GetCustomers(array());
		
		// load existing gateways
		$this->load->model('gateway_model');
		$gateways = $this->gateway_model->GetGateways(array());
		
		$data = array(
					'states' => $states,
					'countries' => $countries,
					'plans' => $plans,
					'customers' => $customers,
					'gateways' => $gateways
					);
					
		$this->load->view(branded_view('cp/new_transaction.php'), $data);
		return true;
	}
	
	/**
	* Post Charge
	*/
	function post() {
		$this->load->library('opengateway');
		
		if ($this->input->post('recurring') == '0') {
			$charge = new Charge;
		}
		else {
			$charge = new Recur;
		}
		
		$api_url = site_url('api');
		$api_url = ($this->config->item('ssl_active') == TRUE) ? str_replace('http://','https://',$api_url) : $api_url;
		
		$charge->Authenticate(
						$this->user->Get('api_id'),
						$this->user->Get('secret_key'),
						site_url('api')
					);
		
		$charge->Amount($this->input->post('amount'));
		
		$charge->CreditCard(
						$this->input->post('cc_name'),
						$this->input->post('cc_number'),
						$this->input->post('cc_expiry_month'),
						$this->input->post('cc_expiry_year'),
						$this->input->post('cc_security')
					);
					
		if ($this->input->post('recurring') == '1') {
			$charge->UsePlan($this->input->post('recurring_plan'));
		}		
		elseif ($this->input->post('recurring') == '2') {
			$free_trial = $this->input->post('free_trial');
			$free_trial = empty($free_trial) ? FALSE : $free_trial;
			
			$occurrences = ($this->input->post('recurring_end') == 'occurrences') ? $this->input->post('occurrences') : FALSE;
			
			$start_date = $this->input->post('start_date_year') . '-' . $this->input->post('start_date_month') . '-' . $this->input->post('start_date_day');
			$end_date = $this->input->post('end_date_year') . '-' . $this->input->post('end_date_month') . '-' . $this->input->post('end_date_day');
			
			$end_date = ($this->input->post('recurring_end') == 'date') ? $end_date : FALSE;
			
			$charge->Schedule(
						$this->input->post('interval'),
						$free_trial,
						$occurrences,
						$start_date,
						$end_date
					);
		}
		
		if ($this->input->post('customer_id') != '') {
			$charge->UseCustomer($this->input->post('customer_id'));
		}
		else {
			$first_name = ($this->input->post('first_name') == 'First Name') ? '' : $this->input->post('first_name');
			$last_name = ($this->input->post('last_name') == 'Last Name') ? '' : $this->input->post('last_name');
			$email = ($this->input->post('email') == 'email@example.com') ? '' : $this->input->post('email');
			$state = ($this->input->post('country') == 'US' or $this->input->post('country') == 'CA') ? $this->input->post('state_select') : $this->input->post('state');
			
			if (!empty($first_name) and !empty($last_name)) {
				$charge->Customer(
								$first_name,
								$last_name,
								$this->input->post('company'),
								$this->input->post('address_1'),
								$this->input->post('address_2'),
								$this->input->post('city'),
								$state,
								$this->input->post('country'),
								$this->input->post('postal_code'),
								$this->input->post('phone'),
								$email
						);	
			}	
		}
		
		if ($this->input->post('gateway_type') == 'specify') {
			$charge->UseGateway($this->input->post('gateway'));
		}
		
		$response = $charge->Charge();
		
		if (!is_array($response) or isset($response['error'])) {
			$this->notices->SetError($this->lang->line('transaction_error') . $response['error_text'] . ' (#' . $response['error'] . ')');
		}
		elseif (isset($response['response_code']) and $response['response_code'] == '2') {
			$this->notices->SetError($this->lang->line('transaction_error') . $response['response_text'] . '. ' . $response['reason'] . ' (#' . $response['response_code'] . ')');
		}
		else {
			$this->notices->SetNotice($this->lang->line('transaction_ok'));
		}
		
		if (isset($response['recurring_id'])) {
			$redirect = site_url('transactions/recurring/' . $response['recurring_id']);
		}
		elseif (isset($response['charge_id'])) {
			$redirect = site_url('transactions/charge/' . $response['charge_id']);
		}
		else {
			$redirect = site_url('transactions/create');
		}
		
		redirect($redirect);
		
		return true;
	}
	
	/**
	* View Individual Charge
	*
	*/
	function charge ($id) {
		$this->navigation->PageTitle('Charge #' . $id);
	
		$this->load->model('charge_model');
		$charge = $this->charge_model->GetCharge($id);
		
		$data = $charge;
		
		$this->load->model('gateway_model');
		$gateway = $this->gateway_model->GetGatewayDetails($charge['gateway_id'],TRUE);
		
		$data['gateway'] = $gateway;
		
		$details = $this->charge_model->GetChargeGatewayInfo($id);
		
		$data['details'] = $details;
		 
		$this->load->view(branded_view('cp/charge'), $data);
		
		return true;
	}
	
	/**
	* View Recurring Charge
	*/
	function recurring ($id = FALSE) {
		if (!$id) {
			// pass to recurring index
			return $this->all_recurring();
		}
		
		$this->navigation->PageTitle('Recurring Charge #' . $id);
		
		$this->load->model('recurring_model');
		$recurring = $this->recurring_model->GetRecurring($id);
		
		$data = $recurring;
		
		$this->load->model('gateway_model');
		$gateway = $this->gateway_model->GetGatewayDetails($recurring['gateway_id'],TRUE);
		
		$data['gateway'] = $gateway;
		
		// they may need to change plans
		if (isset($data['plan'])) {
			// load plans
			$this->load->model('plan_model');
			
			$plans = $this->plan_model->GetPlans(array());
			
			$data['plans'] = $plans;
		}
		
		$this->load->view(branded_view('cp/recurring'), $data);
	}
	
	/**
	* Change Recurring Plan
	*/
	function change_plan ($id) {
		$this->load->model('recurring_model');
		if ($this->recurring_model->ChangeRecurringPlan($id,$this->input->post('plan'))) {
			$this->notices->SetNotice('Recurring charge #' . $id . ' updated to a new plan successfully.');
		}
		else {
			$this->notices->SetError('Recurring charge #' . $id . ' could not be updated.');
		}
		
		redirect(site_url('transactions/recurring/' . $id));
	}
	
	/**
	* Cancel Recurring
	*/
	function cancel_recurring ($id) {
		$this->load->model('recurring_model');
		$this->recurring_model->CancelRecurring($id);
		
		$this->notices->SetNotice('Recurring charge #' . $id . ' cancelled');
		
		redirect(site_url('transactions/recurring/' . $id));
	}
	
	/**
	* Refund a Charge
	*/
	function refund ($charge_id) {
		$this->load->model('gateway_model');
		if ($this->gateway_model->Refund($charge_id)) {
			$this->notices->SetNotice('Charge #' . $charge_id . ' refunded.');
		}
		else {
			$this->notices->SetError('Charge #' . $charge_id . ' could not be refunded.');
		}
		
		redirect(site_url('transactions/charge/' . $charge_id));
	}
}