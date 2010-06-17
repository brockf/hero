<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->navigation->parent_active('configuration');
		
		$this->navigation->module_link('Setup New Gateway',site_url('admincp/gateways/new_gateway'));
	}
	
	/**
	* Manage gateways
	*
	* Lists active gateways for managing
	*/
	function index()
	{	
		$this->load->model('admincp/dataset','dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'sort_column' => 'id',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'id'),
						array(
							'name' => 'Gateway',
							'type' => 'text',
							'width' => '40%'),
						array(
							'name' => 'Date Created',
							'width' => '25%',
							'type' => 'date'),
						array(
							'name' => '',
							'width' => '25%'
							)
					);
		
		$this->dataset->columns($columns);
		$this->dataset->datasource('billing/gateway_model','GetGateways');
		$this->dataset->base_url(site_url('admincp/gateways'));
		$this->dataset->rows_per_page(1000);

		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/gateways/delete_gateways');
		
		$this->load->view('gateways.php');
	}
	
	/**
	* Delete Gateways
	*
	* Delete gateways as passed from the dataset
	*
	* @param string Hex'd, base64_encoded, serialized array of gateway ID's
	* @param string Return URL for Dataset
	*
	* @return bool Redirects to dataset
	*/
	function delete_gateways ($gateways, $return_url) {
		$this->load->model('billing/gateway_model');
		$this->load->library('asciihex');
		
		$gateways = unserialize(base64_decode($this->asciihex->HexToAscii($gateways)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($gateways as $gateway) {
			$this->gateway_model->DeleteGateway($gateway);
		}
		
		$this->notices->SetNotice($this->lang->line('gateways_deleted'));
		
		redirect($return_url);
		return true;
	}
	
	/**
	* New Gateway
	*
	* Create a new gateway
	*
	* @return true Passes to view
	*/
	function new_gateway ()
	{
		$this->load->model('billing/gateway_model');
		$gateways = $this->gateway_model->GetExternalAPIs();
		
		$data = array(
					'gateways' => $gateways
					);
		
		$this->load->view('new_gateway_type.php',$data);
	}
	
	/**
	* New Gateway Step 2
	*
	* Create a new gateway
	*
	* @return true Passes to view
	*/
	function new_gateway_details ()
	{
		if ($this->input->post('external_api') == '') {
			redirect('settings/new_gateway');
			return false;
		}
		else {
			$this->load->library('payment/' . $this->input->post('external_api'), $this->input->post('external_api'));
			$class = $this->input->post('external_api');
			$settings = $this->$class->Settings();
		}
		$this->navigation->PageTitle($settings['name'] . ': Details');
		
		$data = array(
					'form_title' => $settings['name'] . ': Details',
					'form_action' => site_url('settings/post_gateway/new'),
					'external_api' => $this->input->post('external_api'),
					'name' => $settings['name'],
					'fields' => $settings['field_details']
					);
		
		$this->load->view(branded_view('cp/gateway_details.php'),$data);
	}
	
	/**
	* Handle New/Edit Gateway Post
	*/
	function post_gateway ($action, $id = false) {		
		if ($this->input->post('external_api') == '') {
			$this->notices->SetError('No external API ID in form posting.');
			$error = true;
		}
		else {
			$this->load->library('payment/' . $this->input->post('external_api'), $this->input->post('external_api'));
			$class = $this->input->post('external_api');
			$settings = $this->$class->Settings();
		}
		
		$gateway = array();
		
		foreach ($settings['field_details'] as $name => $details) {
			$gateway[$name] = $this->input->post($name);
			
			if ($this->input->post($name) == '') {
				$this->notices->SetError('Required field missing: ' . $details['text']);
				$error = true;
			}
		}
		reset($settings['field_details']);
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('settings/new_gateway');
				return false;
			}
			else {
				redirect('settings/edit_gateway/' . $id);
			}	
		}
		
		$params = array(
						'gateway_type' => $this->input->post('external_api'),
						'alias' => $this->input->post('alias')
					);
					
		foreach ($settings['field_details'] as $name => $details) {
			$params[$name] = $this->input->post($name);
		}
		
		$this->load->model('billing/gateway_model');
		
		if ($action == 'new') {
			$gateway_id = $this->gateway_model->NewGateway($params);
			
			$gateway = $this->gateway_model->GetGatewayDetails($gateway_id);
			
			// test gateway
			$test = $this->$class->TestConnection($gateway);
			
			if (!$test) {
				$this->gateway_model->DeleteGateway($gateway_id,TRUE);
				
				$this->notices->SetError('Unable to establish a test connection.  Your details may be incorrect.');
				
				if ($action == 'new') {
					redirect('settings/new_gateway');
					return false;
				}
				else {
					redirect('settings/edit_gateway/' . $id);
				}	
			}
			
			$this->notices->SetNotice($this->lang->line('gateway_added'));
		}
		else {
			$params['gateway_id'] = $id;
			
			$this->gateway_model->UpdateGateway($params);
			$this->notices->SetNotice($this->lang->line('gateway_updated'));
		}
		
		redirect('settings/gateways');
		
		return true;
	}
	
	/**
	* Edit Gateway
	*
	* Show the gateway form, preloaded with variables
	*
	* @param int $id the ID of the gateway
	*
	* @return string The email form view
	*/
	function edit_gateway($id) {
		$this->load->model('billing/gateway_model');
		$gateway = $this->gateway_model->GetGatewayDetails($id);
	
		$this->load->library('payment/' . $gateway['name'], $gateway['name']);
		$settings = $this->$gateway['name']->Settings();

		$this->navigation->PageTitle($settings['name'] . ': Details');
		
		$data = array(
					'form_title' => $settings['name'] . ': Details',
					'form_action' => site_url('settings/post_gateway/edit/' . $id),
					'external_api' => $gateway['name'],
					'name' => $gateway['alias'],
					'fields' => $settings['field_details'],
					'values' => $gateway
					);
		
		$this->load->view(branded_view('cp/gateway_details.php'),$data);
	}
	
	/**
	* Make Default Gateway
	*/
	function make_default_gateway ($id) {
		$this->load->model('billing/gateway_model');
		$this->gateway_model->MakeDefaultGateway($id);
		
		$this->notices->SetNotice($this->lang->line('default_gateway_changed'));
		
		redirect(site_url('settings/gateways'));
	}
}