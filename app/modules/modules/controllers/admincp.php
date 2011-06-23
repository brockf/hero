<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Module Control Panel
*
* Manage modules
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function index () {
		$this->load->library('dataset');
			
			$columns = array(
						array(
							'name' => 'Module Name',
							'type' => 'id',
							'width' => '30%',
							'filter' => 'text'
							),
						array(
							'name' => 'Status',
							'width' => '15%'
							),
						array(
							'name' => 'Version',
							'width' => '20%'
							),
						array(
							'name' => '',
							'width' => '35%'
							)
					);
						
		$this->dataset->columns($columns);
		$this->dataset->datasource('modules/module_model','get_modules');
		$this->dataset->base_url(site_url('admincp/modules'));
		
		// initialize the dataset
		$this->dataset->initialize();
				
		return $this->load->view('modules');
	}
}

	