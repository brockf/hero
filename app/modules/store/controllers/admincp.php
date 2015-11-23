<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Store Control Panel
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

		$this->admin_navigation->parent_active('storefront');
	}

	function post_product_option () {
		$this->load->helper('array_to_json');

		$error = array();

		if ($this->input->post('name') == '') {
			$error[] = 'Name must not be empty.';
		}

		// build array, then check for errors
		$options = array();
		for ($i = 1; $i <= 50; $i++) {
			$value = $this->input->post('value' . $i);
			$price = $this->input->post('price' . $i);

			if (!empty($value)) {
				$options[] = array(
								'label' => $value,
								'price' => (!empty($price)) ? (float)$price : 0
							);
			}
		}

		if (empty($options)) {
			$error[] = 'No values have been given.  This option must have at least value before it can be used.';
		}

		// do we have errors?
		if (!empty($error)) {
			return print(array_to_json(array('error' => implode(' ',$error))));
		}

		// do the insert
		$this->load->model('store/product_option_model');

		$save = ($this->input->post('save') == '1') ? TRUE : FALSE;

		$option_id = $this->product_option_model->new_option($this->input->post('name'),
												$options,
												$save);

		return print(array_to_json(array('option_id' => $option_id, 'option_name' => $this->input->post('name'))));
	}

	/*
	* List Shipping Rates
	*/
	function shipping () {
		$this->admin_navigation->module_link('Add Shipping Rate',site_url('admincp/store/shipping_add'));
		$this->admin_navigation->module_link('Shipping Configuration',site_url('admincp/store/shipping_config'));

		$this->load->library('dataset');

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'id'
							),
						array(
							'name' => 'Name',
							'type' => 'text',
							'width' => '20%',
							'filter' => 'name'
							),
						array(
							'name' => 'State/Province',
							'type' => 'text',
							'width' => '15%',
							'filter' => 'state'
							),
						array(
							'name' => 'Country',
							'type' => 'text',
							'width' => '15%',
							'filter' => 'country'
							),
						array(
							'name' => 'Rate',
							'type' => 'text',
							'width' => '20%',
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('shipping_model','get_rates');
		$this->dataset->base_url(site_url('admincp/store/shipping'));

		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/store/shipping_delete');

		$this->load->view('shipping_rates');
	}

	function shipping_config () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('General Configuration');

		$form->value_row('','<b>Ship Worldwide?</b><br />If you select specific countries below, only customers from these
					countries will be able to purchase your products.  However, if you select "SHIP WORLDWIDE" or leave the options blank,
					anyone can buy your products.  If they don\'t match a specific shipping rate, they will be charged your default shipping
					rate (configured below).');

		$this->load->model('states_model');
		$countries = $this->states_model->GetCountries();

		$options = array();
		$options[0] = 'SHIP WORLDWIDE';
		foreach ($countries as $country) {
			$options[$country['iso2']] = $country['name'];
		}

		$selected = (setting('shipping_available_countries') == '') ? array('0') : unserialize(setting('shipping_available_countries'));

		$form->dropdown('Limit to Countries','countries',$options,$selected,TRUE);

		$form->value_row('','<b>Default Shipping Rate</b><br />In the absence of a specific shipping rate matching the customer\'s address, this default shipping rate will be used.');

		$options = array(
						'weight' => 'Calculate the shipping rate as a multiple of each ' . setting('weight_unit') . ' in the shopping cart',
						'product' => 'Flat rate per product',
						'flat' => 'Flat rate per cart'
						);

		/*
			Shipping Modules

			If a shipping module is installed it will have set a setting named 'shipping_module'
			that holds the name of the module that handles shipping. If it's set,
			then load their library file, and add our options to the options array.
		*/
		if ($this->config->item('shipping_module'))
		{
			$module = $this->config->item('shipping_module');

			$this->load->library($module .'/'. $module);

			$mod_options = $this->$module->get_types();

			if (is_array($mod_options))
			{
				$options = array_merge($options, $mod_options);
			}
		}
		/*
			End shipping modules integration
		*/

		$form->radio('Rate Type','type',$options, setting('shipping_default_type'));

		$form->text('Rate (' . setting('currency_symbol') . ')','rate',setting('shipping_default_rate'),FALSE,TRUE,'e.g., &quot;5.00&quot;',FALSE,'70px','rate',array('number'));

		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/store/post_shipping_config'),
					'form_title' => 'Shipping Configuration'
					);

		$this->load->view('shipping_config',$data);
	}

	function post_shipping_config () {
		// are we shipping worldwide?
		if (!$this->input->post('countries') or in_array('0', $this->input->post('countries'))) {
			// we are shipping globally
			$this->settings_model->update_setting('shipping_available_countries','');
		}
		else {
			$countries = serialize($this->input->post('countries'));

			$this->settings_model->update_setting('shipping_available_countries', $countries);
		}

		// set default rate
		$this->settings_model->update_setting('shipping_default_type', $this->input->post('type'));
		$this->settings_model->update_setting('shipping_default_rate', $this->input->post('rate'));

		$this->notices->SetNotice('Shipping configuration updated successfully.');

		return redirect('admincp/store/shipping');
	}

	function shipping_delete ($rates, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('shipping_model');

		$rates = unserialize(base64_decode($this->asciihex->HexToAscii($rates)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));

		foreach ($rates as $rate) {
			$this->shipping_model->delete_rate($rate);
		}

		$this->notices->SetNotice('Shipping rate(s) deleted successfully.');

		redirect($return_url);

		return TRUE;
	}

	function shipping_add () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Shipping Rate');

		$this->load->model('states_model');
		$countries = $this->states_model->GetCountries();
		$states = $this->states_model->GetStates();

		$options = array();
		$options[0] = '';
		foreach ($countries as $country) {
			$options[$country['id']] = $country['name'];
		}

		$form->dropdown('Available to Country','country',$options,'0',FALSE,'If selected, the rate will only be available to purchasers from this country.');

		$options = array();
		$options[0] = '';
		foreach ($states as $state) {
			$options[$state['id']] = $state['name'];
		}

		$form->dropdown('Available to State','state',$options,'0',FALSE,'If selected, the rate will only be available to purchasers from this state/province.');

		$options = array(
						'weight' => 'Calculate the shipping rate as a multiple of each ' . setting('weight_unit') . ' in the shopping cart',
						'product' => 'Flat rate per product',
						'flat' => 'Flat rate per cart'
						);

		// Get rates from any shipping
		if ($this->config->item('shipping_module'))
		{
			$module = $this->config->item('shipping_module');

			$this->load->library($module .'/'. $module);

			$mod_options = $this->$module->get_types();

			if (is_array($mod_options))
			{
				$options = array_merge($options, $mod_options);
			}
		}

		$form->radio('Rate Type','type',$options, 'flat');

		$form->text('Rate (' . setting('currency_symbol') . ')','rate','',FALSE,TRUE,'e.g., &quot;5.00&quot;',FALSE,'70px','rate',array('number'));

		$form->checkbox('<a href="' . site_url('admincp/store/taxes') . '">Tax rules</a> apply','taxable','1',FALSE);

		$form->text('Method Name','name','','This will be seen by the customer when they select their shipping method.',TRUE,'e.g., &quot;FedEx Overnight&quot;');

		$form->text('Max Cart Weight (' . setting('weight_unit') . ')','max_weight','','(Optional) Shipping option will only be available if their cart weight is less than or equal to this amount.',FALSE,FALSE, FALSE, '70px');

		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/store/post_shipping/new'),
					'form_title' => 'Add New Shipping Method'
					);

		$this->load->view('shipping_form',$data);
	}

	function shipping_edit ($rate_id) {
		// get rate
		$this->load->model('shipping_model');
		$rate = $this->shipping_model->get_rate($rate_id);

		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Shipping Rate');

		$this->load->model('states_model');
		$countries = $this->states_model->GetCountries();
		$states = $this->states_model->GetStates();

		$options = array();
		$options[0] = '';
		foreach ($countries as $country) {
			$options[$country['id']] = $country['name'];
		}

		$form->dropdown('Available to Country','country',$options,$rate['country_id'],FALSE,'If selected, the rate will only be available to purchasers from this country.');

		$options = array();
		$options[0] = '';
		foreach ($states as $state) {
			$options[$state['id']] = $state['name'];
		}

		$form->dropdown('Available to State','state',$options,$rate['state_id'],FALSE,'If selected, the rate will only be available to purchasers from this state/province.');

		$options = array(
						'weight' => 'Calculate the shipping rate as a multiple of each ' . setting('weight_unit') . ' in the shopping cart',
						'product' => 'Flat rate per product',
						'flat' => 'Flat rate per cart'
						);

		if ($this->config->item('shipping_module'))
		{
			$module = $this->config->item('shipping_module');

			$this->load->library($module .'/'. $module);

			$mod_options = $this->$module->get_types();

			if (is_array($mod_options))
			{
				$options = array_merge($options, $mod_options);
			}
		}

		$form->radio('Rate Type','type',$options, $rate['type']);

		$form->text('Rate (' . setting('currency_symbol') . ')','rate',$rate['rate'],FALSE,TRUE,'e.g., &quot;5.00&quot;',FALSE,'70px','rate',array('number'));

		$form->checkbox('<a href="' . site_url('admincp/store/taxes') . '">Tax rules</a> apply','taxable','1',$rate['taxable']);

		$form->text('Method Name','name',$rate['name'],'This will be seen by the customer when they select their shipping method.',TRUE,'e.g., &quot;FedEx Overnight&quot;');

		$form->text('Max Cart Weight (' . setting('weight_unit') . ')','max_weight',$rate['max_weight'],'(Optional) Shipping option will only be available if their cart weight is less than or equal to this amount.',FALSE,FALSE, FALSE, '70px');

		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/store/post_shipping/edit/' . $rate['id']),
					'form_title' => 'Edit Shipping Method'
					);

		$this->load->view('shipping_form',$data);
	}

	function post_shipping ($action, $id = FALSE) {
		$this->load->library('form_validation');
		$this->form_validation->set_rules('rate','Rate','trim|numeric|required');
		$this->form_validation->set_rules('name','Method Name','trim|required');
		$this->form_validation->set_rules('type','Rate Type','required');

		if ($this->form_validation->run() === FALSE) {
			$errors = rtrim(validation_errors('','||'),'|');
			$errors = explode('||',str_replace('<p>','',$errors));

			$this->notices->SetError($errors);
			$error = TRUE;
		}

		// have they set any rule?
		if ($this->input->post('country') == '0' and $this->input->post('state') == '0') {
			$this->notices->SetError('You must specify either (or both) a state/province or a country in your shipping rule.');
			$error = TRUE;
		}

		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/store/shipping_add');
				return false;
			}
			else {
				redirect('admincp/store/shipping_add/' . $id);
				return false;
			}
		}

		$this->load->model('shipping_model');
		if ($action == 'new') {
			$rate_id = $this->shipping_model->new_rate($this->input->post('name'),
										$this->input->post('type'),
										$this->input->post('rate'),
										$this->input->post('state'),
										$this->input->post('country'),
										($this->input->post('taxable') == '1') ? TRUE : FALSE,
										$this->input->post('max_weight')
									);

			$this->notices->SetNotice('Shipping rate added successfully.');
		}
		elseif ($action == 'edit') {
			$this->shipping_model->update_rate($id,
										$this->input->post('name'),
										$this->input->post('type'),
										$this->input->post('rate'),
										$this->input->post('state'),
										$this->input->post('country'),
										($this->input->post('taxable') == '1') ? TRUE : FALSE,
										$this->input->post('max_weight')
									);

			$this->notices->SetNotice('Shipping rate edited successfully.');
		}

		return redirect('admincp/store/shipping');
	}

	/*
	* List Taxes
	*/
	function taxes () {
		$this->admin_navigation->module_link('Add Tax Rule',site_url('admincp/store/tax_add'));

		$this->load->library('dataset');

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'id'
							),
						array(
							'name' => 'State/Province',
							'type' => 'text',
							'width' => '20%',
							'filter' => 'state'
							),
						array(
							'name' => 'Country',
							'type' => 'text',
							'width' => '20%',
							'filter' => 'country'
							),
						array(
							'name' => 'Tax Rate',
							'type' => 'text',
							'width' => '10%',
							'filter' => 'percentage'
							),
						array(
							'name' => 'Name',
							'type' => 'text',
							'width' => '20%',
							'filter' => 'name'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('taxes_model','get_taxes');
		$this->dataset->base_url(site_url('admincp/store/taxes'));

		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/store/tax_delete');

		$this->load->view('tax_rules.php');
	}

	function tax_delete ($taxes, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('taxes_model');

		$taxes = unserialize(base64_decode($this->asciihex->HexToAscii($taxes)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));

		foreach ($taxes as $tax) {
			$this->taxes_model->delete_tax($tax);
		}

		$this->notices->SetNotice('Tax rule(s) deleted successfully.');

		redirect($return_url);

		return TRUE;
	}

	function tax_add () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Tax Rule');

		$this->load->model('states_model');
		$countries = $this->states_model->GetCountries();
		$states = $this->states_model->GetStates();

		$options = array();
		$options[0] = 'Any';
		foreach ($countries as $country) {
			$options[$country['id']] = $country['name'];
		}

		$form->dropdown('Applies to Country','country',$options,'0',FALSE,'If selected, the tax will only apply to purchasers from this country.');

		$options = array();
		$options[0] = 'Any';
		foreach ($states as $state) {
			$options[$state['id']] = $state['name'];
		}

		$form->dropdown('OR Applies to State','state',$options,'0',FALSE,'If selected, the tax will only apply to purchasers from this state/province.');

		$form->text('Tax Rate (%)','percentage','',FALSE,TRUE,'e.g., &quot;7&quot;',FALSE,'60px','percentage',array('number'));

		$form->text('Identifying Name','name','',FALSE,TRUE,'e.g., &quot;GST&quot; or &quot;VAT&quot;');

		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/store/post_tax/new'),
					'form_title' => 'Add New Tax Rule'
					);

		$this->load->view('tax_form',$data);
	}

	function tax_edit ($id) {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Tax Rule');

		$this->load->model('taxes_model');
		$tax = $this->taxes_model->get_tax($id);

		$this->load->model('states_model');
		$countries = $this->states_model->GetCountries();
		$states = $this->states_model->GetStates();

		$options = array();
		$options[0] = 'Any';
		foreach ($countries as $country) {
			$options[$country['id']] = $country['name'];
		}

		$form->dropdown('Applies to Country','country',$options,$tax['country_id'],FALSE,'If selected, the tax will only apply to purchasers from this country.');

		$options = array();
		$options[0] = 'Any';
		foreach ($states as $state) {
			$options[$state['id']] = $state['name'];
		}

		$form->dropdown('OR Applies to State','state',$options,$tax['state_id'],FALSE,'If selected, the tax will only apply to purchasers from this state/province.');

		$form->text('Tax Rate (%)','percentage',$tax['percentage'],FALSE,TRUE,'e.g., &quot;7&quot;',FALSE,'60px','percentage',array('number'));

		$form->text('Identifying Name','name',$tax['name'],FALSE,TRUE,'e.g., &quot;GST&quot; or &quot;VAT&quot;');

		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/store/post_tax/edit/' . $tax['id']),
					'form_title' => 'Edit Tax Rule'
					);

		$this->load->view('tax_form',$data);
	}

	function post_tax ($action, $id = FALSE) {
		$this->load->library('form_validation');
		$this->form_validation->set_rules('percentage','Tax Rate','trim|numeric|required');
		$this->form_validation->set_rules('name','Tax Name','trim|required');

		if ($this->form_validation->run() === FALSE) {
			$errors = rtrim(validation_errors('','||'),'|');
			$errors = explode('||',str_replace('<p>','',$errors));

			$this->notices->SetError($errors);
			$error = TRUE;
		}

		// have they set any rule?
		if ($this->input->post('country') == '0' and $this->input->post('state') == '0') {
			$this->notices->SetError('You must specify either (or both) a state/province or a country in your tax rule.');
			$error = TRUE;
		}

		// don't let them set a country if they chose a state
		if ($this->input->post('state') != '0') {
			$country = '0';
		}
		else {
			$country = $this->input->post('country');
		}

		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/store/tax_add');
				return false;
			}
			else {
				redirect('admincp/store/tax_add/' . $id);
				return false;
			}
		}

		$this->load->model('taxes_model');
		if ($action == 'new') {
			$tax_id = $this->taxes_model->new_tax($this->input->post('name'),
										$this->input->post('percentage'),
										$this->input->post('state'),
										$country
									);

			$this->notices->SetNotice('Tax rule added successfully.');
		}
		elseif ($action == 'edit') {
			$this->taxes_model->update_tax($id,
										$this->input->post('name'),
										$this->input->post('percentage'),
										$this->input->post('state'),
										$country
									);

			$this->notices->SetNotice('Tax rule edited successfully.');
		}

		redirect('admincp/store/taxes');
	}

	/*
	* List Products
	*/
	function index () {
		$this->admin_navigation->module_link('Add Product',site_url('admincp/store/add'));

		// Make collections available in the view
		$this->load->model('collections_model');
		$collections = $this->collections_model->get_collections();

		$collection_options = array();
		if (is_array($collections))
		{
			foreach ($collections as $collection)
			{
				$collection_options[ $collection['id'] ] = $collection['name'];
			}
		}
		unset($collections);

		$this->load->library('dataset');

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'id'
							),
						array(
							'name' => 'Link',
							'type' => 'link',
							'width' => '5%'
							),
						array(
							'name' => 'Type',
							'type' => 'select',
							'width' => '10%',
							'filter' => 'type',
							'options' => array('download' => 'Download', 'shippable' => 'Shippable')
							),
						array(
							'name' => 'Name',
							'width' => '20%',
							'type' => 'text',
							'filter' => 'name',
							'sort_column' => 'product_name'
							),
						array(
							'name' => 'Price',
							'width' => '10%',
							'type' => 'text',
							'sort_column' => 'product_price',
							'filter' => 'price'
							),
						array(
							'name' => 'Inventory',
							'width' => '10%',
							'sort_column' => 'product_inventory'
							),
						array(
							'name' => 'Collections',
							'width' => '15%',
							'type' => 'select',
							'options' => $collection_options,
							'filter' => 'collection'
						),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('products_model','get_products');
		$this->dataset->base_url(site_url('admincp/store'));

		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/store/delete');

		$this->load->view('products.php', array('collections' => $collection_options));
	}

	/**
	* Delete Products
	*
	* Delete products as passed from the dataset
	*
	* @param string Hex'd, base64_encoded, serialized array of products ID's
	* @param string Return URL for Dataset
	*
	* @return bool Redirects to dataset
	*/
	function delete ($products, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('products_model');

		$products = unserialize(base64_decode($this->asciihex->HexToAscii($products)));

		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));

		foreach ($products as $product) {
			$this->products_model->delete_product($product);
		}

		$this->notices->SetNotice('Product(s) deleted successfully.');

		redirect($return_url);

		return TRUE;
	}

	function product_actions ($action = FALSE, $product_id = FALSE) {
		// take input from POST if it wasn't passed
		if ($product_id == FALSE) {
			$product_id = $this->input->post('product_id');
		}

		if ($action == FALSE) {
			$action = $this->input->post('action');
		}

		// get product
		$this->load->model('products_model');
		$product = $this->products_model->get_product($product_id);

		if ($action == 'details') {
			header('Location: ' . $product['admin_link']);
		}
		elseif ($action == 'edit') {
			redirect('admincp/store/edit/' . $product['id']);
		}
		elseif ($action == 'images') {
			redirect('admincp/store/product/' . $product['id']);
		}
		elseif ($action == 'view_orders') {
			$this->load->helper('admincp/dataset_link');
			$url = dataset_link('admincp/reports/products', array('product_name' => $product['name']));
			redirect($url);
		}

		return TRUE;
	}

	function product ($product_id) {
		$this->admin_navigation->module_link('Add a Product',site_url('admincp/store/add'));
		$this->admin_navigation->module_link('Edit this Product',site_url('admincp/store/edit/' . $product_id));

		$this->load->model('products_model');

		$product = $this->products_model->get_product($product_id);

		if (!$product) {
			die(show_error('Unable to retrieve product.'));
		}

		// get usergroups
		$this->load->model('users/usergroup_model');
	    $usergroups = $this->usergroup_model->get_usergroups();

	    $options = array();
	    foreach ($usergroups as $group) {
	    	$options[$group['id']] = $group['name'];
	    }
	    $usergroups = $options;

	    // get custom fields
	    $this->load->model('custom_fields_model');
	    $custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => setting('products_custom_field_group')));

	    // gallery
	    // image gallery
		$this->load->library('image_gallery_form');
		$gallery = new Image_gallery_form;
		$gallery->label('Upload New Images');
		$gallery->name('product_images');
		$gallery->show_upload_button(TRUE);

		$this->load->helper('format_size');
		$this->load->helper('image_thumb');

		$data = array(
						'product' => $product,
						'usergroups' => $usergroups,
						'custom_fields' => $custom_fields,
						'gallery' => $gallery->display()
					);

		$this->load->view('product', $data);
	}

	function save_image_order ($product_id) {
		$this->load->model('products_model');

		// reset
		$this->products_model->images_reset_order($product_id);

		$count = 1;
		foreach ($_POST['image'] as $image_id) {
			$this->products_model->image_update_order($image_id, $count);
			$count++;
		}

		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
	}

	function product_image_feature ($product_id, $image_id) {
		$this->load->model('products_model');
		$this->products_model->make_feature_image($image_id);

		redirect('admincp/store/product/' . $product_id);
	}

	function product_image_delete ($product_id, $image_id) {
		$this->load->model('products_model');
		$this->products_model->remove_image($image_id);

		redirect('admincp/store/product/' . $product_id);
	}

	function product_images ($product_id) {
		$this->load->model('products_model');

		// deal with image uploads
		if (is_array($_FILES['product_images_image'])) {
		    $config = array();
		    $config['upload_path'] = setting('path_product_images');
		    $config['allowed_types'] = 'jpg|gif|png';

		    // upload class may already be loaded
		    if (isset($this->upload)) {
		    	$this->upload->initialize($config);
		    }
		    else {
		    	$this->load->library('upload', $config);
		    }

		    // do we already have images
		    $images = $this->products_model->get_images($product_id);

		    if (isset($images[0]['featured']) and $images[0]['featured'] == '1') {
		    	$has_feature = TRUE;
		    }
		    else {
		    	$has_feature = FALSE;
		    }

		    for ($i = 0; $i <= 14; $i++) {
		    	if (!empty($_FILES['product_images_image']['tmp_name'][$i]) and is_uploaded_file($_FILES['product_images_image']['tmp_name'][$i])) {
		    		// this is a legit file
		    		if (!$this->upload->do_upload('product_images_image',$i)) {
		    			die(show_error($this->upload->display_errors()));
		    		}

		    		$image_id = $this->products_model->add_image($product_id, $this->upload->file_name);
		    		$this->upload->file_name = ''; // reset

		    		if ($has_feature == FALSE) {
		    			$this->products_model->make_feature_image($image_id);
		    			$has_feature = TRUE;
		    		}
		    	}
		    }
		}

		redirect('admincp/store/product/' . $product_id);
	}

	function add () {
		$this->load->helper('form');

		// first part of form is generated by Admin_form
		$this->load->library('admin_form');
		$form = new Admin_form;

		$form->fieldset('Basic Information');
		$form->text('Product Name','name','','e.g, "Adidas Cross Trainers", or "CRBO2010 Conference Tickets"',TRUE,FALSE,TRUE);
		$form->textarea('Description','description','','This description will appear on the product page.',TRUE,'basic',TRUE);

		$this->load->model('collections_model');
		$collections = $this->collections_model->get_tiered_collections();

		$options = array();
		$options[0] = 'No collections';
		foreach ($collections as $data) {
			$options[$data['id']] = $data['name'];
		}

		$form->dropdown('Collection(s)','collections[]',$options, array(), TRUE, FALSE, 'Select multiple collections by holding the CTRL or CMD button and selecting multiple options.',TRUE);

		$form->fieldset('Product Properties');
		$form->text('Price (' . setting('currency_symbol') . ')','price','',FALSE,TRUE,'9.95',FALSE,'60px','price');
		$form->text('Weight (' . setting('weight_unit') . ')','weight','0',FALSE,TRUE,'0',FALSE,'60px','weight');
		$tax = form_checkbox('taxable','1',TRUE);
		$form->value_row('&nbsp;',$tax . ' <b><a target="_blank" href="' . site_url('admincp/store/taxes') . '">Tax rules</a> apply to this product</b>');
		$require_shipping = form_checkbox('requires_shipping','1',TRUE);
		$form->value_row('&nbsp;',$require_shipping . ' <b>Require a shipping address for this product</b> (not necessary for digital products or services)');

		$form->fieldset('Inventory Management');
		$form->text('SKU Number','sku','','(Optional) Enter a unique identifier of the product for inventory management.',FALSE,FALSE,FALSE,'200px','sku');

		$inventory = form_checkbox(array('name' => 'track_inventory', 'value' => '1', 'checked' => FALSE, 'id' => 'track_inventory'));
		$form->value_row('&nbsp;',$inventory . ' <b>Track inventory for this product?</b>');
		$form->text('Quantity in Stock','inventory','',FALSE,FALSE,FALSE,FALSE,'60px','inventory');
		$options = array(
						'0' => 'Don\'t sell at zero inventory',
						'1' => 'Sell at zero inventory'
					);
		$form->radio('&nbsp;','inventory_allow_oversell',$options,'1',FALSE,FALSE,FALSE,'inventory_allow_at_zero');

		// image gallery
		$this->load->library('image_gallery_form');
		$gallery = new Image_gallery_form;
		$gallery->label('Product Images');
		$gallery->name('product_images');

		// custom fields
		if (setting('products_custom_field_group') != '') {
			$this->load->library('custom_fields/form_builder');
			$this->form_builder->build_form_from_group(setting('products_custom_field_group'));

			$custom_fields = $this->form_builder->output_admin();
		}
		else {
			$custom_fields = FALSE;
		}

		// load usergroups for membership tiers
		$this->load->model('users/usergroup_model');
	    $usergroups = $this->usergroup_model->get_usergroups();

	    $options = array();
	    $options[0] = '';
	    foreach ($usergroups as $group) {
	    	$options[$group['id']] = $group['name'];
	    }
	    $usergroups = $options;

		// get files from product_files
		$files = $this->map_product_files();

		// get product options that might be re-used
		$this->load->model('store/product_option_model');
		$shared_product_options = $this->product_option_model->get_options(array('shared' => '1'));

		$data = array(
					'form' => $form->display(),
					'gallery' => $gallery->display(),
					'form_action' => site_url('admincp/store/post_product/new'),
					'form_title' => 'Add New Product',
					'usergroups' => $usergroups,
					'shared_product_options' => $shared_product_options,
					'files' => $files,
					'custom_fields' => $custom_fields
					);

		$this->load->view('product_form.php',$data);
	}

	function edit ($product_id) {
		$this->load->helper('form');

		// get product
		$this->load->model('products_model');
		$product = $this->products_model->get_product($product_id);

		// first part of form is generated by Admin_form
		$this->load->library('admin_form');
		$form = new Admin_form;

		$form->fieldset('Basic Information');
		$form->text('Product Name','name',$product['name'],'e.g, "Adidas Cross Trainers", or "CRBO2010 Conference Tickets"',TRUE,FALSE,TRUE);
		$form->textarea('Description','description',$product['description'],'This description will appear on the product page.',TRUE,'basic',TRUE);

		$this->load->model('collections_model');
		$collections = $this->collections_model->get_tiered_collections();

		$options = array();
		$options[0] = 'No collections';
		foreach ($collections as $data) {
			$options[$data['id']] = $data['name'];
		}

		$form->dropdown('Collection(s)','collections[]',$options, (is_array($product['collections'])) ? $product['collections'] : array(), TRUE, FALSE, 'Select multiple collections by holding the CTRL or CMD button and selecting multiple options.',TRUE);

		$form->fieldset('Product Properties');
		$form->text('Price (' . setting('currency_symbol') . ')','price',$product['price'],FALSE,TRUE,'9.95',FALSE,'60px','price');
		$form->text('Weight (' . setting('weight_unit') . ')','weight',$product['weight'],FALSE,TRUE,'0',FALSE,'60px','weight');
		$tax = form_checkbox('taxable','1',($product['is_taxable'] == TRUE) ? TRUE : FALSE);
		$form->value_row('&nbsp;',$tax . ' <b><a target="_blank" href="' . site_url('admincp/store/taxes') . '">Tax rules</a> apply to this product</b>');
		$require_shipping = form_checkbox('requires_shipping','1',($product['requires_shipping'] == TRUE) ? TRUE : FALSE);
		$form->value_row('&nbsp;',$require_shipping . ' <b>Require a shipping address for this product</b> (not necessary for digital products or services)');

		$form->fieldset('Inventory Management');
		$form->text('SKU Number','sku',$product['sku'],'(Optional) Enter a unique identifier of the product for inventory management.',FALSE,FALSE,FALSE,'200px','sku');

		$inventory = form_checkbox(array('name' => 'track_inventory', 'value' => '1', 'checked' => ($product['track_inventory'] == FALSE) ? FALSE : TRUE, 'id' => 'track_inventory'));
		$form->value_row('&nbsp;',$inventory . ' <b>Track inventory for this product?</b>');
		$form->text('Quantity in Stock','inventory',$product['inventory'],FALSE,FALSE,FALSE,FALSE,'60px','inventory');
		$options = array(
						'0' => 'Don\'t sell at zero inventory',
						'1' => 'Sell at zero inventory'
					);
		$form->radio('&nbsp;','inventory_allow_oversell',$options,($product['track_inventory'] == TRUE) ? $product['inventory_allow_oversell'] : '1',FALSE,FALSE,FALSE,'inventory_allow_at_zero');

		// custom fields
		if (setting('products_custom_field_group') != '') {
			$this->load->library('custom_fields/form_builder');
			$this->form_builder->build_form_from_group(setting('products_custom_field_group'));

			$this->form_builder->set_values($product);
			$this->form_builder->clear_defaults();
			$custom_fields = $this->form_builder->output_admin();
		}
		else {
			$custom_fields = FALSE;
		}

		// load usergroups for membership tiers
		$this->load->model('users/usergroup_model');
	    $usergroups = $this->usergroup_model->get_usergroups();

	    $options = array();
	    $options[0] = '';
	    foreach ($usergroups as $group) {
	    	$options[$group['id']] = $group['name'];
	    }
	    $usergroups = $options;

		// get files from product_files
		$files = $this->map_product_files();

		// get product options that might be re-used
		$this->load->model('store/product_option_model');
		$shared_product_options = $this->product_option_model->get_options(array('shared' => '1'));

		// also, get all product options
		$product_options = $this->product_option_model->get_options();

		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/store/post_product/edit/' . $product['id']),
					'form_title' => 'Edit Product',
					'usergroups' => $usergroups,
					'files' => $files,
					'shared_product_options' => $shared_product_options,
					'product_options' => $product_options,
					'custom_fields' => $custom_fields,
					'product' => $product
					);

		$this->load->view('product_edit.php',$data);
	}

	function post_product ($action, $id = FALSE) {
		$this->load->model('products_model');

		$validated = $this->products_model->validation();
		if ($validated !== TRUE) {
			$this->notices->SetError(implode('<br />',$validated));
			$error = TRUE;
		}

		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/store/add');
				return FALSE;
			}
			else {
				redirect('admincp/store/edit/' . $id);
				return FALSE;
			}
		}

		$this->load->library('custom_fields/form_builder');
		$this->form_builder->build_form_from_group(setting('products_custom_field_group'));
		$custom_fields = $this->form_builder->post_to_array();

		// get member tiers
		if ($this->input->post('membership_tiers') == '1') {
		    // we'll cap at 25 membership tiers - come on!
		    for ($i = 0; $i <= 24; $i++) {
		    	if (!empty($_POST['membership_tier'][$i])) {
		    		$member_tiers[$_POST['membership_tier'][$i]] = $_POST['membership_tier_price'][$i];
		    	}
		    }
		}
		else {
		    $member_tiers = array();
		}

		// is this a download?  i hope not..
		if ($this->input->post('download') == '1') {
		    $is_download = TRUE;

		    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
		    	// they are uploading
		    	$config = array();
		    	$config['upload_path'] = setting('path_product_files');
		    	$config['allowed_types'] = '*';
		    	$this->load->library('upload', $config);

		    	if (!$this->upload->do_upload('file')) {
		    		die(show_error($this->upload->display_errors()));
		    	}
		    	else {
		    		$download_name = $this->upload->file_name;
		    		$this->upload->file_name = ''; // reset
		    		$download_size = filesize(setting('path_product_files') . $download_name);
		    	}
		    }
		    else {
		    	// they have selected an existing file
		    	$download_name = $this->input->post('file_uploaded');
		    	$download_size = filesize(setting('path_product_files') . $download_name);
		    }
		}
		else {
		    $is_download = FALSE;
		    $download_name = '';
		    $download_size = '0';
		}

		// build product options
		$product_options = array();

		if (isset($_POST['options'])) {
			foreach ($_POST['options'] as $option) {
				if (!in_array($option,$product_options) and is_numeric($option)) {
					$product_options[] = $option;
				}
			}
		}

		if ($action == 'new') {
			$product_id = $this->products_model->new_product(
													$this->input->post('name'),
													$this->input->post('description'),
													isset($_POST['collections']) ? $_POST['collections'] : array(),
													$this->input->post('price'),
													$this->input->post('weight'),
													($this->input->post('requires_shipping') == '1') ? TRUE : FALSE,
													($this->input->post('track_inventory') == '1') ? TRUE : FALSE,
													$this->input->post('inventory'),
													($this->input->post('inventory_allow_oversell') == '1') ? TRUE : FALSE,
													$this->input->post('sku'),
													($this->input->post('taxable') == '1') ? TRUE : FALSE,
													$member_tiers,
													$is_download,
													$download_name,
													$download_size,
													$this->input->post('promotion'),
													$custom_fields,
													$product_options
												);

			// deal with image uploads
			if (is_array($_FILES['product_images_image'])) {
				$config = array();
				$config['upload_path'] = setting('path_product_images');
				$config['allowed_types'] = 'jpg|gif|png';

				// upload class may already be loaded
				if (isset($this->upload)) {
					$this->upload->initialize($config);
				}
				else {
					$this->load->library('upload', $config);
				}

				$has_feature = FALSE;
				for ($i = 0; $i <= 14; $i++) {
					if (!empty($_FILES['product_images_image']['tmp_name'][$i]) and is_uploaded_file($_FILES['product_images_image']['tmp_name'][$i])) {
						// this is a legit file
						if (!$this->upload->do_upload('product_images_image',$i)) {
							die(show_error($this->upload->display_errors()));
						}

						$image_id = $this->products_model->add_image($product_id, $this->upload->file_name);
						$this->upload->file_name = ''; // reset

						if ($has_feature == FALSE) {
							$this->products_model->make_feature_image($image_id);
							$has_feature = TRUE;
						}
					}
				}
			}

			$this->notices->SetNotice('Product added successfully.');
		}
		else {
			$this->products_model->update_product(
											$id,
											$this->input->post('name'),
											$this->input->post('description'),
											isset($_POST['collections']) ? $_POST['collections'] : array(),
											$this->input->post('price'),
											$this->input->post('weight'),
											($this->input->post('requires_shipping') == '1') ? TRUE : FALSE,
											($this->input->post('track_inventory') == '1') ? TRUE : FALSE,
											$this->input->post('inventory'),
											($this->input->post('inventory_allow_oversell') == '1') ? TRUE : FALSE,
											$this->input->post('sku'),
											($this->input->post('taxable') == '1') ? TRUE : FALSE,
											$member_tiers,
											$is_download,
											$download_name,
											$download_size,
											$this->input->post('promotion'),
											$custom_fields,
											$product_options
										);


			$this->notices->SetNotice('Product edited successfully.');

			$product_id = $id;
		}

		redirect('admincp/store/product/' . $product_id);

		return TRUE;
	}

	function get_product_files () {
		$this->load->helper('array_to_json');

		$files = $this->map_product_files();

		echo array_to_json($files);
	}

	private function map_product_files () {
		$this->load->helper('file');
		$this->load->helper('filter_directory');

		$files = filter_directory(directory_map(setting('path_product_files')),array('index.html'));

		sort($files);

		return $files;
	}

	function collection_add () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Collection Information');

		$this->load->model('collections_model');
		$collections = $this->collections_model->get_tiered_collections();

		$options = array();
		$options[0] = 'No parent';
		foreach ($collections as $data) {
			$options[$data['id']] = $data['name'];
		}

		$form->dropdown('Parent','parent',$options,'0',FALSE,FALSE,'If a parent is selected, this collection will act as a sub-collection of its parent.',TRUE);
		$form->text('Collection Name','name','',FALSE,TRUE,'e.g., Men\'s Shoes',TRUE);
		$form->textarea('Description','description','',FALSE,FALSE,'complete',TRUE);

		// custom fields
		if (setting('collections_custom_field_group') != '') {
			$this->load->model('custom_fields_model');
			$collection_data = $this->custom_fields_model->get_custom_fields(array('group' => setting('collections_custom_field_group')));
			if (!empty($collection_data)) {
				$form->fieldset('Custom Collection Data');
				$form->custom_fields($collection_data);
			}
		}

		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/store/post_collection/new'),
					'form_title' => 'New Collection'
					);

		$this->load->view('collection_form',$data);
	}

	function collection_edit ($id) {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Collection Information');

		$this->load->model('collections_model');
		$collections = $this->collections_model->get_tiered_collections();

		$collection = $this->collections_model->get_collection($id);

		$options = array();
		$options[0] = 'No parent';
		foreach ($collections as $data) {
			$options[$data['id']] = $data['name'];
		}

		$form->dropdown('Parent','parent',$options,$collection['parent'],FALSE,FALSE,'If a parent is selected, this collection will act as a sub-collection of its parent.',TRUE);
		$form->text('Collection Name','name',$collection['name'],FALSE,TRUE,'e.g., Men\'s Shoes',TRUE);
		$form->textarea('Description','description',$collection['description'],FALSE,FALSE,'complete',TRUE);

		// custom fields
		if (setting('collections_custom_field_group') != '') {
			$collection_data = $this->custom_fields_model->get_custom_fields(array('group' => setting('collections_custom_field_group')));
			if (!empty($collection_data)) {
				$form->fieldset('Custom Product Data');
				$form->custom_fields($collection_data, $collection);
			}
		}

		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/store/post_collection/edit/' . $collection['id']),
					'form_title' => 'Edit Collection'
					);

		$this->load->view('collection_form',$data);
	}

	function post_collection($action, $id = FALSE) {
		$this->load->library('form_validation');

		$this->form_validation->set_rules('name','Name','trim|required');
		$this->form_validation->set_rules('parent','Parent','is_natural');

		if ($this->form_validation->run() === FALSE) {
			$errors = rtrim(validation_errors('','||'),'|');
			$errors = explode('||',str_replace('<p>','',$errors));

			$this->notices->SetError($errors);
			$error = TRUE;
		}

		$this->load->library('custom_fields/form_builder');

		// get custom field rules for custom field group, if there are custom fields
		if (setting('collections_custom_field_group') != '') {
			$this->form_builder->build_form_from_group(setting('collections_custom_field_group'));

			if ($this->form_builder->validate_post() === FALSE) {
				$this->notices->SetError(strip_tags($this->form_builder->validation_errors()));
				$error = TRUE;
			}
		}
		else {
			$custom_fields = array();
		}

		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/store/collection_add');
				return false;
			}
			else {
				redirect('admincp/store/collection_edit/' . $id);
			}
		}

		$this->form_builder->build_form_from_group(setting('collections_custom_field_group'));
		$custom_fields = $this->form_builder->post_to_array();

		if ($action == 'new') {
			$this->load->model('collections_model');
			$collection_id = $this->collections_model->new_collection(
													$this->input->post('name'),
													$this->input->post('description'),
													$this->input->post('parent'),
													$custom_fields
													);

			$this->notices->SetNotice('Collection added successfully.');
		}
		elseif ($action == 'edit') {
			$this->load->model('collections_model');
			$collection_id = $this->collections_model->update_collection(
													$id,
													$this->input->post('name'),
													$this->input->post('description'),
													$this->input->post('parent'),
													$custom_fields
													);

			$this->notices->SetNotice('Collection updated successfully.');
		}

		redirect('admincp/store/collections');
	}

	function collections () {
		$this->admin_navigation->module_link('Add Collection',site_url('admincp/store/collection_add'));

		$this->load->library('dataset');

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%'
							),
						array(
							'name' => 'Name',
							'width' => '70%',
							'filter' => 'name',
							'type' => 'text'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('collections_model','get_tiered_collections');
		$this->dataset->base_url(site_url('admincp/store/collections'));

		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/store/collections_delete');

		$this->load->view('collections.php');
	}

	function collections_delete ($collections, $return_url) {
		$this->load->library('asciihex');
		$this->load->model('collections_model');

		$collections = unserialize(base64_decode($this->asciihex->HexToAscii($collections)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));

		foreach ($collections as $collection) {
			$this->collections_model->delete_collection($collection);
		}

		$this->notices->SetNotice('Collection(s) deleted successfully.');

		redirect($return_url);

		return TRUE;
	}

	function collection_data () {
		$this->admin_navigation->parent_active('configuration');

		if (setting('collections_custom_field_group') == '') {
			// we don't have custom fields enabled
			$this->load->view('enable_collections_custom_fields');

			return true;
		}

		$this->admin_navigation->module_link('Add Custom Field',site_url('admincp/store/collection_data_add'));
		$this->admin_navigation->module_link('Preview &amp; Arrange Fields',site_url('admincp/custom_fields/order/' . setting('collections_custom_field_group') . '/' . urlencode(base64_encode(site_url('admincp/store/collection_data')))));

		$this->load->library('dataset');

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%'
							),
						array(
							'name' => 'Human Name',
							'width' => '25%'
							),
						array(
							'name' => 'System Name',
							'width' => '25%'
							),
						array(
							'name' => 'Type',
							'type' => 'text',
							'width' => '20%'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('collections_model','get_custom_fields');
		$this->dataset->base_url(site_url('admincp/store/collection_data'));
		$this->dataset->rows_per_page(1000);

		// total rows
		$total_rows = $this->db->where('custom_field_group',setting('collections_custom_field_group'))->get('custom_fields')->num_rows();
		$this->dataset->total_rows($total_rows);

		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/store/collection_data_delete');

		$this->load->view('collection_data.php');
	}

	function enable_collection_data () {
		$this->admin_navigation->parent_active('configuration');

		$this->load->model('custom_fields_model');
		$group_id = $this->custom_fields_model->new_group('Collections');

		$this->settings_model->new_setting(2, 'collections_custom_field_group', $group_id, 'The custom field group ID for collection data.', 'text', '');

		redirect('admincp/store/collection_data');
	}

	function collection_data_add () {
		return redirect('admincp/custom_fields/add/' . setting('collections_custom_field_group') . '/collections/collections');
	}

	function collection_data_edit ($id) {
		return redirect('admincp/custom_fields/edit/' . $id . '/collections/collections');
	}

	/**
	* Delete Custom Fields
	*
	* Delete custom fields as passed from the dataset
	*
	* @param string Hex'd, base64_encoded, serialized array of custom field ID's
	* @param string Return URL for Dataset
	*
	* @return bool Redirects to dataset
	*/
	function collection_data_delete ($fields, $return_url) {
		$this->admin_navigation->parent_active('configuration');

		$this->load->library('asciihex');
		$this->load->model('custom_fields_model');

		$fields = unserialize(base64_decode($this->asciihex->HexToAscii($fields)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));

		foreach ($fields as $field) {
			$this->custom_fields_model->delete_custom_field($field, 'collections');
		}

		$this->notices->SetNotice('Field(s) deleted successfully.');

		redirect($return_url);

		return TRUE;
	}

	function data () {
		$this->admin_navigation->parent_active('configuration');

		if (setting('products_custom_field_group') == '') {
			// we don't have custom fields enabled
			$this->load->view('enable_custom_fields');

			return true;
		}

		$this->admin_navigation->module_link('Add Custom Field',site_url('admincp/store/data_add'));
		$this->admin_navigation->module_link('Preview &amp; Arrange Fields',site_url('admincp/custom_fields/order/' . setting('products_custom_field_group') . '/' . urlencode(base64_encode(site_url('admincp/store/data')))));

		$this->load->library('dataset');

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%'
							),
						array(
							'name' => 'Human Name',
							'width' => '25%'
							),
						array(
							'name' => 'System Name',
							'width' => '25%'
							),
						array(
							'name' => 'Type',
							'type' => 'text',
							'width' => '20%'
							),
						array(
							'name' => '',
							'width' => '25%'
							)
					);

		$this->dataset->columns($columns);
		$this->dataset->datasource('products_model','get_custom_fields');
		$this->dataset->base_url(site_url('admincp/store/data'));
		$this->dataset->rows_per_page(1000);

		// total rows
		$total_rows = $this->db->where('custom_field_group',setting('products_custom_field_group'))->get('custom_fields')->num_rows();
		$this->dataset->total_rows($total_rows);

		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/store/data_delete');

		$this->load->view('data.php');
	}

	function enable_data () {
		$this->admin_navigation->parent_active('configuration');

		$this->load->model('custom_fields_model');
		$group_id = $this->custom_fields_model->new_group('Products');

		$this->settings_model->new_setting(2, 'products_custom_field_group', $group_id, 'The custom field group ID for product data.', 'text', '');

		redirect('admincp/store/data');
	}

	function data_add () {
		return redirect('admincp/custom_fields/add/' . setting('products_custom_field_group') . '/products/products');
	}

	function data_edit ($id) {
		return redirect('admincp/custom_fields/edit/' . $id . '/products/products');
	}

	/**
	* Delete Custom Fields
	*
	* Delete custom fields as passed from the dataset
	*
	* @param string Hex'd, base64_encoded, serialized array of custom field ID's
	* @param string Return URL for Dataset
	*
	* @return bool Redirects to dataset
	*/
	function data_delete ($fields, $return_url) {
		$this->admin_navigation->parent_active('configuration');

		$this->load->library('asciihex');
		$this->load->model('custom_fields_model');

		$fields = unserialize(base64_decode($this->asciihex->HexToAscii($fields)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));

		foreach ($fields as $field) {
			$this->custom_fields_model->delete_custom_field($field, 'products');
		}

		$this->notices->SetNotice('Field(s) deleted successfully.');

		redirect($return_url);

		return TRUE;
	}

	//--------------------------------------------------------------------
	// !Product Options
	//--------------------------------------------------------------------

	/**
	 * Product Options
	 *
	 **/
	function product_options () {
		$this->admin_navigation->module_link('New Product Option',site_url('admincp/store/product_option_add'));

		$this->load->library('dataset');

		$columns = array(
					array(
						'name' => 'ID #',
						'type' => 'id',
						'width' => '5%',
						'filter' => 'id'
					),
					array(
						'name' => 'Name',
						'type' => 'text',
						'width' => '45%',
						'filter' => 'name'
					),
					array(
						'name' => 'Options',
						'width' => '45%'
					),
					array(
						'name' => '',
						'width' => '10%'
					)
				);

		$this->dataset->columns($columns);
		$this->dataset->datasource('product_option_model','get_options');
		$this->dataset->base_url(site_url('admincp/store/product_options'));

		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/store/options_delete');

		$this->load->view('product_options');
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a single product option for editing.
	 *
	 * @param INT	- The product option ID.
	 */
	function product_option($product_option_id) {
		$this->load->model('product_option_model');

		$product_option = $this->product_option_model->get_option($product_option_id);

		if (!$product_option) {
			die(show_error('Unable to retrieve product option.'));
		}
		$this->load->library('admin_form');
		$form = new Admin_form;

		$form->fieldset('Production Option Information');
		$form->text('Name', 'name', $product_option['name'], FALSE, TRUE);

		$data = array(
			'option_name'	=> $product_option['name'],
			'form'			=> $form->display(),
			'form_action'	=> site_url('admincp/store/save_product_option'),
			'product_option'	=> $product_option,
		);

		$this->load->view('product_option_edit', $data);
	}

	//--------------------------------------------------------------------

	/**
	 * Saves a single product option.
	 */
	function save_product_option() {

		if ($this->input->post('go_product') === FALSE && $this->input->post('go_new_product') === FALSE)
		{
			redirect('admincp/store/product_options');
		}

		$this->load->model('product_option_model');

		$product_options = array();

		if (!isset($_POST['option']))
		{
			return;
		}

		$options = is_array($_POST['option']) ? $_POST['option'] : array($_POST['option']);
		$prices = is_array($_POST['price']) ? $_POST['price'] : array($_POST['price']);

		for ($i=0; $i < count($options); $i++)
		{
			if (!empty($options[$i]))
			{
				$product_options[] = array(
					'label'	=> $options[$i],
					'price'	=> !empty($prices[$i]) && is_numeric($prices[$i]) ? $prices[$i] : 0,
				);
			}
		}

		$share = $this->input->post('share_it');

		// Is this an insert or an update?
		if ($this->input->post('go_new_product'))
		{
			$result = $this->product_option_model->new_option($_POST['name'], $product_options, $share);
		}
		else
		{
			$result = $this->product_option_model->update_option($_POST['product_option_id'], $_POST['name'], $product_options, $share);
		}

		if ($result)
		{
			$this->notices->SetNotice('Product Option saved successfully.');

			redirect('admincp/store/product_options');
		}

		$this->notices->SetError('Error saving Product Option.');

		redirect(current_url());
	}

	//--------------------------------------------------------------------

	function product_option_add() {

		$data = array(
			'form_action'	=> site_url('admincp/store/save_product_option'),
		);

		$this->load->library('admin_form');
		$form = new Admin_form;

		$form->fieldset('Production Option Information');
		$form->text('Name', 'name', '', FALSE, TRUE);

		$data = array(
			'form'			=> $form->display(),
			'form_action'	=> site_url('admincp/store/save_product_option'),
		);

		$this->load->view('product_option_form', $data);
	}

	//--------------------------------------------------------------------

	public function options_delete($options)
	{
		$this->load->model('product_option_model');
		$this->load->library('asciihex');


		$options = unserialize(base64_decode($this->asciihex->HexToAscii($options)));

		foreach ($options as $option)
		{
			$this->product_option_model->delete_option($option);
		}

		$this->notices->SetNotice('Product Options deleted successfully.');
		redirect('admincp/store/product_options');
	}

	//--------------------------------------------------------------------

}