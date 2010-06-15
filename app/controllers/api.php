<?php

class API extends Controller {

	function __construct()
	{
		parent::Controller();	
		
		define("_API","TRUE");
	}
	
	function index()
	{
		$this->load->model('response');
		
		// grab the request
		$request = trim(file_get_contents('php://input'));
		
		// find out if the request is valid XML
		$xml = @simplexml_load_string($request);
		
		// if it is not valid XML...
		if (!$xml) {
			die($this->response->Error(1000));
		}
		
		// Make an array out of the XML
		$this->load->library('arraytoxml');
		$params = $this->arraytoxml->toArray($xml);
		
		// get the api ID and secret key
		$api_id = $params['authentication']['api_id'];
		$secret_key = $params['authentication']['secret_key'];
		
		if ($api_id != $this->config->item('api_id') or $secret_key != $this->config->item('secret_key')) {
			die($this->response->Error(1001));
		}
		
		// Get the request type
		if(!isset($params['type'])) {
					
			die($this->response->Error(1002));
		}
		$request_type = $params['type'];
		
		// Make sure the first letter is capitalized
		$request_type = ucfirst($request_type);
		
		// Make sure a proper format was passed
		if(isset($params['format'])) {
			$format = $params['format'];
			if(!in_array($format, array('xml', 'json', 'php'))) {
				echo $this->response->Error(1006);
				die();
			}
		} else {
			$format = 'xml';
		}
		
		// is request type valid?
		if (!method_exists($this, $request_type)) {
			die($this->response->Error(1002));
		}
		
		// pass off to the method
		$response = $this->$request_type($params);
				
		// handle errors that didn't just kill the code
		if ($response == FALSE) {
			die($this->response->Error(1009));
		}
		
		// Echo the response
		echo $this->response->FormatResponse($response, $format);		
	}
	
	function Charge($params) {
		$this->load->model('gateway_model');
		
		// Make sure it came from a secure connection if SSL is active
		if (empty($_SERVER["HTTPS"]) and $this->config->item('ssl_active') == TRUE) {
			die($this->response->Error(1010));
		}
		
		// we don't check the gateway here, because the GetGatewayDetails function will attempt
		// to find the default gateway
		
		// take XML params and put them in variables
		$credit_card = isset($params['credit_card']) ? $params['credit_card'] : array();
		$customer_id = isset($params['customer_id']) ? $params['customer_id'] : FALSE;
		$customer = isset($params['customer']) ? $params['customer'] : FALSE;
		$amount = isset($params['amount']) ? $params['amount'] : FALSE;
		$gateway_id = isset($params['gateway_id']) ? $params['gateway_id'] : FALSE;
		$customer_ip = isset($params['customer_ip_address']) ? $params['customer_ip_address'] : FALSE;
		$return_url = isset($params['return_url']) ? $params['return_url'] : FALSE;
		$cancel_url = isset($params['cancel_url']) ? $params['cancel_url'] : FALSE;
		
		return $this->gateway_model->Charge($gateway_id, $amount, $credit_card, $customer_id, $customer, $customer_ip, $return_url, $cancel_url);
	}
	
	function Recur($params) {
		$this->load->model('gateway_model');
		
		// Make sure it came from a secure connection if SSL is active
		if (empty($_SERVER["HTTPS"]) and $this->config->item('ssl_active') == TRUE) {
			die($this->response->Error(1010));
		}
		
		// we don't check the gateway here, because the GetGatewayDetails function will attempt
		// to find the default gateway
		
		// take XML params and put them in variables
		$credit_card = isset($params['credit_card']) ? $params['credit_card'] : array();
		$customer_id = isset($params['customer_id']) ? $params['customer_id'] : FALSE;
		$customer = isset($params['customer']) ? $params['customer'] : FALSE;
		$amount = isset($params['amount']) ? $params['amount'] : FALSE;
		$gateway_id = isset($params['gateway_id']) ? $params['gateway_id'] : FALSE;
		$customer_ip = isset($params['customer_ip_address']) ? $params['customer_ip_address'] : FALSE;
		$recur = isset($params['recur']) ? $params['recur'] : FALSE;
		$return_url = isset($params['return_url']) ? $params['return_url'] : FALSE;
		$cancel_url = isset($params['cancel_url']) ? $params['cancel_url'] : FALSE;
		
		return $this->gateway_model->Recur($gateway_id, $amount, $credit_card, $customer_id, $customer, $customer_ip, $recur, $return_url, $cancel_url);
	}
	
	function Refund ($params) {
		$this->load->model('gateway_model');
		
		if ($this->gateway_model->Refund($params['charge_id'])) {
			return $this->response->TransactionResponse(50, array());
		}
		else {
			return $this->response->TransactionResponse(51, array());
		}
	}
	
	function DeletePlan($params)
	{
		$this->load->model('plan_model');
		
		if ($this->plan_model->DeletePlan($params['plan_id'])) {
			return $this->response->TransactionResponse(502, array());
		} else {
			return FALSE;
		}
	}
	
	function GetPlans($params)
	{
		$this->load->model('plan_model');
		
		if (!isset($params['limit']) or $params['limit'] > $this->config->item('query_result_default_limit')) {
			$params['limit'] = $this->config->item('query_result_default_limit');
		}
		
		$data = array();
		if ($plans = $this->plan_model->GetPlans($params)) {
			unset($params['limit']);
			$data['results'] = count($plans);
			$data['total_results'] = count($this->plan_model->GetPlans($params));
			
			while (list(,$plan) = each($plans)) {
				$data['plans']['plan'][] = $plan;
			}
		}
		else {
			$data['results'] = 0;
			$data['total_results'] = 0;
		}
		
		return $data;
	}
	
	function GetPlan($params)
	{
		$this->load->model('plan_model');
		
		if ($plan = $this->plan_model->GetPlan($params['plan_id'])) {
			$data = array();
			$data['plan'] = $plan;
			
			return $data;
		}
		else {
			return FALSE;
		}
	}
	
	function UpdatePlan($params)
	{
		$this->load->model('plan_model');
		
		if ($this->plan_model->UpdatePlan($params['plan_id'], $params)) {
			return $this->response->TransactionResponse(501, array());		
		}
		else {
			return FALSE;
		}
	}
	
	function NewPlan($params)
	{
		$this->load->model('plan_model');
		
		if ($insert_id = $this->plan_model->NewPlan($params)) {
			$response_array = array();
			$response_array['plan_id'] = $insert_id; 
			$response = $this->response->TransactionResponse(500, $response_array);
			
			return $response;
		}
		else {
			return FALSE;
		}
	}
	
	function ChangeRecurringPlan($params) {
		$this->load->model('recurring_model');
		
		if (!isset($params['plan_id'])) {
			die($this->response->Error(6006));
		}
		elseif (!isset($params['recurring_id'])) {
			die($this->response->Error(6002));
		}
		
		if ($this->recurring_model->ChangeRecurringPlan($params['recurring_id'],$params['plan_id'])) 
		{
			return $this->response->TransactionResponse(103, array());
		}
		else {
			return FALSE;
		}
	}
	
	function GetRecurring($params)
	{
		$this->load->model('recurring_model');
		if (!$recurring = $this->recurring_model->GetRecurring($params['recurring_id'])) {
			 die($this->response->Error(6002));
		} else {
			$data = array();
			$data['recurring'] = $recurring;
			return $data;
		}
	}
	
	function GetRecurrings($params)
	{
		$this->load->model('recurring_model');
		
		if (!isset($params['limit']) or $params['limit'] > $this->config->item('query_result_default_limit')) {
			$params['limit'] = $this->config->item('query_result_default_limit');
		}
		
		$data = array();
		if ($recurrings = $this->recurring_model->GetRecurrings($params)) {
			unset($params['limit']);
			$data['results'] = count($recurrings);
			$data['total_results'] = count($this->recurring_model->GetRecurrings($params));
			
			while (list(,$recurring) = each($recurrings)) {
				$data['recurrings']['recurring'][] = $recurring;
			}
		}
		else {
			$data['results'] = 0;
			$data['total_results'] = 0;
		}
		
		return $data;
	}
	
	function UpdateRecurring($params)
	{
		if (isset($params['plan_id'])) {
			 die($this->response->Error(6006));
		}
		
		if(!isset($params['recurring_id'])) {
			 die($this->response->Error(6002));
		}
	
		$this->load->model('recurring_model');
		if ($this->recurring_model->UpdateRecurring($params)) {
			$response = $this->response->TransactionResponse(102,array());
			
			return $response;
		}
		else {
			die($this->response->Error(6005));
		}
	}
	
	function CancelRecurring($params)
	{
		if (!isset($params['recurring_id'])) {
			die($this->response->Error(6002));
		}
		
		$this->load->model('recurring_model');
		
		if ($this->recurring_model->CancelRecurring($params['recurring_id'])) {
			return $this->response->TransactionResponse(101,array());
		}
		else {
			die($this->response->Error(5014));
		}
	}
	
	function NewCustomer($params)
	{
		$this->load->model('customer_model');
		
		if ($customer_id = $this->customer_model->NewCustomer($params)) {
			$response = array('customer_id' => $customer_id);
			
			return $this->response->TransactionResponse(200, $response);
		}
		else {
			return FALSE;
		}	
	}
	
	function UpdateCustomer($params)
	{
		if(!isset($params['customer_id'])) {
			die($this->response->Error(6001));
		}
		
		$this->load->model('customer_model');
		
		if ($this->customer_model->UpdateCustomer($params['customer_id'], $params)) {
			return $this->response->TransactionResponse(201);
		}
		else {
			return FALSE;
		}
	}
	
	function DeleteCustomer($params)
	{
		if(!isset($params['customer_id'])) {
			die($this->response->Error(6001));
		}
		
		$this->load->model('customer_model');
		
		if ($this->customer_model->DeleteCustomer($params['customer_id'])) {
			return $this->response->TransactionResponse(202);
		}
		else {
			return FALSE;
		}	
	}
	
	function GetCustomers($params)
	{
		$this->load->model('customer_model');
	
		if (!isset($params['limit']) or $params['limit'] > $this->config->item('query_result_default_limit')) {
			$params['limit'] = $this->config->item('query_result_default_limit');
		}
	
		
		$data = array();
		if ($customers = $this->customer_model->GetCustomers($params)) {
			unset($params['limit']);
			$data['results'] = count($customers);
			$data['total_results'] = count($this->customer_model->GetCustomers($params));
			
			while (list(,$customer) = each($customers)) {
				// sort through plans, first
				if (isset($customer['plans']) and is_array($customer['plans'])) {
					$customer_plans = $customer['plans'];
					unset($customer['plans']);
					while (list(,$plan) = each($customer_plans)) {
						$customer['plans']['plan'][] = $plan;
					}
				}
				else {
					unset($customer['plans']);
				}
				
				$data['customers']['customer'][] = $customer;
			}
		}
		else {
			$data['results'] = 0;
			$data['total_results'] = 0;
		}
		
		return $data;
	}
	
	function GetCustomer($params)
	{
		// Get the customer id
		if(!isset($params['customer_id'])) {
			die($this->response->Error(4000));
		}
		
		$this->load->model('customer_model');
		
		$data = array();
		if ($customer = $this->customer_model->GetCustomer($params['customer_id'])) {	
			// sort through plans, first
			$customer_plans = isset($customer['plans']) ? $customer['plans'] : '';
			unset($customer['plans']);
			if (is_array($customer_plans)) {
				while (list($plan) = each($customer_plans)) {
					$customer['plans']['plan'][] = $plan;
				}
			}
			
			$data['customer'] = $customer;
			
			return $data;
		}
		else {
			return FALSE;
		}
	}
	
	function GetCharges($params)
	{
		$this->load->model('charge_model');
		
		if (!isset($params['limit']) or $params['limit'] > $this->config->item('query_result_default_limit')) {
			$params['limit'] = $this->config->item('query_result_default_limit');
		}
		
		$data = array();
		if ($charges = $this->charge_model->GetCharges($params)) {
			unset($params['limit']);
			$data['results'] = count($charges);
			$data['total_results'] = count($this->charge_model->GetCharges($params));
			
			while (list(,$charge) = each($charges)) {
				$data['charges']['charge'][] = $charge;
			}
		}
		else {
			$data['results'] = 0;
			$data['total_results'] = 0;
		}
		
		return $data;
	}
	
	function GetCharge($params)
	{
		// Get the charge ID
		if(!isset($params['charge_id'])) {
			die($this->response->Error(6000));
		}
		
		$this->load->model('charge_model');
		
		$data = array();
		if ($charge = $this->charge_model->GetCharge($params['charge_id'])) {	
			$data['charge'] = $charge;
			
			return $data;
		}
		else {
			return FALSE;
		}
	}
	
	function GetLatestCharge($params)
	{
		if(!isset($params['customer_id'])) {
			die($this->response->Error(6001));
		}
		
		$this->load->model('charge_model');
		
		$data = array();
		if ($charge = $this->charge_model->GetLatestCharge($params['customer_id'])) {	
			$data['charge'] = $charge;
			
			return $data;
		}
		else {
			return FALSE;
		}
	}
	
	function NewEmail($params)
	{	
		// Get the email trigger id
		$this->load->model('email_model');
		$trigger_id = $this->email_model->GetTriggerId($params['trigger']);
		
		if(!$trigger_id) {
			die($this->response->Error(8000));
		}
		
		// throw an error if the email body had HTML and caused weird XML parsing into an array
		if (is_array($params['email_body'])) {
			die($this->response->Error(8002));
		}
		
		$this->load->model('email_model');
		$email_id = $this->email_model->SaveEmail($trigger_id, $params);
		
		$response_array = array('email_id' => $email_id);
		return $this->response->TransactionResponse(600, $response_array);
	}
	
	function UpdateEmail($params)
	{
		// Get the email id
		if(!isset($params['email_id'])) {
			die($this->response->Error(8001));
		}
		
		// Get the email trigger id
		if(isset($params['trigger'])) {
			$this->load->model('email_model');
			$trigger_id = $this->email_model->GetTriggerId($params['trigger']);
			
			if(!$trigger_id) {
				die($this->response->Error(8000));
			}
		} else {
			$trigger_id = FALSE;
		}
		
		// throw an error if the email body had HTML and caused weird XML parsing into an array
		if(is_array($params['email_body'])) {
			die($this->response->Error(8002));
		}
		
		$this->load->model('email_model');
		$email_id = $this->email_model->UpdateEmail($params['email_id'], $params, $trigger_id);
		
		return $this->response->TransactionResponse(601, array());
	}
	
	function DeleteEmail($params)
	{
		// Get the email id
		if(!isset($params['email_id'])) {
			die($this->response->Error(8001));
		}
		
		$this->load->model('email_model');
		$this->email_model->DeleteEmail($params['email_id']);
		
		return $this->response->TransactionResponse(602, array());
	}
	
	function GetEmail($params)
	{
		if(!$params['email_id']) {
			die($this->response->Error(8000));
		}
		
		$this->load->model('email_model');
		
		if ($response = $this->email_model->GetEmail($params['email_id'])) {
			$data['email'] = $response;
			return $data;
		}
		else {
			return FALSE;
		}	
	}
	
	function GetEmails($params)
	{
		$this->load->model('email_model');
		
		if (!isset($params['limit']) or $params['limit'] > $this->config->item('query_result_default_limit')) {
			$params['limit'] = $this->config->item('query_result_default_limit');
		}
		
		$data = array();
		if ($emails = $this->email_model->GetEmails($params)) {
			unset($params['limit']);
			$data['results'] = count($emails);
			$data['total_results'] = count($this->email_model->GetEmails($params));
			
			while (list(,$email) = each($emails)) {
				$data['emails']['email'][] = $email;
			}
		}
		else {
			$data['results'] = 0;
			$data['total_results'] = 0;
		}
		
		return $data;
	}
	
	function GetEmailVariables($params)
	{
		// Get the email trigger id
		if(isset($params['trigger'])) {
			$this->load->model('email_model');
			$trigger_id = $this->email_model->GetTriggerId($params['trigger']);
		} else {
			$trigger_id = FALSE;
		}
		
		if(!$trigger_id) {
			die($this->response->Error(8000));
		}
		
		$this->load->model('email_model');
		
		if ($response = $this->email_model->GetEmailVariables($trigger_id)) {
			foreach ($response as $array) {
				$return['variables']['variable'] = $array;
			}
			return $return;
		}
		else {
			return FALSE;
		}
	}
}



/* End of file gateway.php */
/* Location: ./system/opengateway/controllers/gateway.php */