<?php
/**
* Callback Controller 
*
* Works with external payment API's to complete payment
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework

*/
class Callback extends CI_Controller {

	function Callback()
	{
		parent::__construct();
	}

	function process() { 
		// get gateway
		$gateway = $this->uri->segment(2); 
		
		// get action
		$action = $this->uri->segment(3);
		
		// get (recurring or one-time) charge_id
		$charge_id = $this->uri->segment(4);
		
		// compile all GET and POST parameters
		$params = array();
		
		// fancy tricks to get at $_GET
		$query_string = explode('?',$_SERVER['REQUEST_URI']);
		if (isset($query_string[1])) {
			parse_str($query_string[1], $params);
		}
		foreach ($_POST as $key => $value) {
			$params[$key] = $value;
		}
		
		// load the gateway
		$gateway_name = $gateway;
		$this->load->library('payment/'.$gateway);
		$gateway_settings = $this->$gateway->Settings();
		
		if ($gateway_settings['external'] == FALSE) {
			die(show_error('This gateway is not an external gateway.  Callbacks are futile.'));
		}
		
		/*
			Since some gateway callbacks will not provide the order_id, 
			We need to call a function in the gateway to retrieve the order id.
		*/
		if (empty($charge_id) and method_exists($this->$gateway_name, 'GetChargeId'))
		{	
			$charge_id = $this->$gateway_name->GetChargeId($params);
			
			// It's very likely these same ones will be using the same callback URL
			// for both recurring and non-recurring, so check for that functionality.
			if (method_exists($this->$gateway_name, 'is_recurring'))
			$recurring = $this->$gateway_name->is_recurring($params);
		}
		
		
		/*
			Get the charge. 
			
			If $action contains 'recur' then we pull from the subscription_info,
			Otherwise we pull it from the order.
		*/
		if (stristr($action, 'recur') !== FALSE || (isset($recurring) && $recurring === TRUE) ) {
			$recurring = TRUE;
			
			$this->load->model('billing/recurring_model');
			$charge = $this->recurring_model->GetRecurring($charge_id);
			
			// log callback
			$this->load->library('billing/transaction_log');
			$this->transaction_log->log_event($charge_id, FALSE, 'callback', $_REQUEST, __FILE__, __LINE__);
		}
		else {
			$recurring = FALSE;
			
			$this->load->model('billing/charge_model');
			$charge = $this->charge_model->GetCharge($charge_id);
			
			// log callback
			$this->load->library('billing/transaction_log');
			$this->transaction_log->log_event(FALSE, $charge_id, 'callback', $_REQUEST, __FILE__, __LINE__);
		}
		
		$buffer = ob_start();
		ini_set('display_errors','On');
		error_reporting(E_ALL);
		
		// get gateway
		$this->load->model('billing/gateway_model');
		$gateway = $this->gateway_model->GetGatewayDetails($charge['gateway_id']);

		// is gateway enabled?
		if (!$gateway or $gateway['enabled'] == '0') {
			die($this->response->Error(5017));
		}
		
		// pass to gateway
		$function = 'Callback_' . $action;
		
		if (!method_exists($this->$gateway_name,$function)) {
			die('Method doesn\'t exist in gateway library.');
		}
		
		// e.g., $this->Paypal_standard->Callback_confirm(1000, 345, array(charge), array(params));
		$this->$gateway_name->$function($gateway, $charge, $params);
		
		// log all script output, to catch errors
		$return = ob_get_clean();
		if ($recurring === TRUE) {
			$this->transaction_log->log_event(FALSE, $charge_id, 'callback_output', $return, __FILE__, __LINE__);
		}
		else {
			$this->transaction_log->log_event($charge_id, FALSE, 'callback_output', $return, __FILE__, __LINE__);
		}
	}
}	