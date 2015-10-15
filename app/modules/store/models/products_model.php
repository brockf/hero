<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Products Model 
*
* Contains all the methods used to create, update, and delete products.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Products_model extends CI_Model
{
	private $CI;
	
	function __construct()
	{
		parent::__construct();
		
		$this->CI =& get_instance();
	}
	
	/**
	* Get Price
	*
	* If the user is logged in and in a group, we'll get the proper price for them (deals with membership tiers and options)
	*
	* @param int $product_id
	* @param array $selected_options Array of selected options which may adjust the pricing (default: array())
	*
	* @return float $new_price
	*/
	function get_price ($product_id, $selected_options = array()) {
		$product = $this->get_product($product_id);
		$membership_tiers = $product['member_tiers'];
		$price = $product['price'];
		
		// membership tiers
		$price = $this->get_member_price($membership_tiers, $price);
		
		// options
		if (!empty($selected_options)) {
			$this->load->model('store/product_option_model');
			
			foreach ($product['options'] as $option) {
				$option = $this->product_option_model->get_option($option);
				
				if (!empty($option)) {
					if (isset($selected_options[$option['name']])) {
						foreach ($option['options'] as $value) {
							if ($selected_options[$option['name']] == $value['label']) {
								if (!empty($value['price'])) {
									$price += $value['price'];
								}
							}
						}
					}
				}
			}
		}
		
		return $price;
	}
	
	/**
	* Membership Pricing
	*
	* Get the proper price for a product considering member groups
	*
	* @param array $membership_tiers
	* @param float $price
	*
	* @return float $price
	*/
	function get_member_price ($membership_tiers, $price) {
		$CI =& get_instance();
		
		// membership tiers
		if (!empty($membership_tiers) and $CI->user_model->logged_in()) {
			
			$usergroups = $CI->user_model->get('usergroups');
			
			foreach ($membership_tiers as $tier => $tier_price) {
				if (in_array($tier, $usergroups) and $price > $tier_price) {
					$price = money_format("%!^i",$tier_price);
				}
			}
		}
		
		return $price;
	}
	
	/**
	* Get Product Custom Fields
	*
	* Gets all product data custom fields
	*
	* @return array Array of custom field data, else FALSE
	*/
	function get_custom_fields () {
		$CI =& get_instance();
		$CI->load->model('custom_fields_model');
		
		return $CI->custom_fields_model->get_custom_fields(array('group' => setting('products_custom_field_group')));
	}
	
	/**
	* Add Image
	* 
	* Adds an image to a product's photo gallery
	*
	* @param int $product_id Product ID
	* @param string $filename The filename of the file in /writeable/product_images/
	*
	* @return int $image_id
	*/
	function add_image ($product_id, $filename) {
		// get next order
		$this->db->where('product_id',$product_id);
		$this->db->order_by('product_image_order','DESC');
		$result = $this->db->get('product_images');
		
		if ($result->num_rows() > 0) {
			$last_field = $result->row_array();
			$order = $last_field['product_image_order'] + 1;
		}
		else {
			$order = '1';
		}
		
		$insert_fields = array(
							'product_id' => $product_id,
							'product_image_filename' => $filename,
							'product_image_order' => $order,
							'product_image_uploaded' => date('Y-m-d H:i:s')
							);
		
		$this->db->insert('product_images',$insert_fields);
		
		$image_id = $this->db->insert_id();
		
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return $image_id;
	}
	
	/**
	* Remove Image
	*
	* Removes an image from the gallery
	*
	* @param int $image_id
	*
	* @return void
	*/
	function remove_image ($image_id) {
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		$this->db->select('product_id');
		$this->db->where('product_image_id',$image_id);
		$result = $this->db->get('product_images');
		
		$image = $result->row_array();
		if (!isset($image['product_id'])) {
			return FALSE;
		}
		$product_id = $image['product_id'];
	
		$this->db->delete('product_images',array('product_image_id' => $image_id));
		
		// replace feature image?
		$this->db->select('product_image_id');
		$this->db->where('product_id',$product_id);
		$this->db->where('product_image_featured','1');
		if ($this->db->get('product_images')->num_rows() == 0) {
			// there's no feature image
			$this->db->select('product_image_id');
			$this->db->where('product_id',$product_id);
			$this->db->order_by('product_image_order','ASC');
			$this->db->limit(1);
			$result = $this->db->get('product_images');
			if ($result->num_rows() == 0) {
				// no images to be a feature
				return;
			}
			else {
				$image = $result->row_array();
				$image_id = $image['product_image_id'];
				
				$this->db->update('product_images',array('product_image_featured' => '1'),array('product_image_id' => $image_id));
			}
		}
	}
	
	/**
	* Make Featured Image
	*
	* Makes this image the featured image
	*
	* @param int $image_id The image ID
	* 
	* @return boolean TRUE
	*/
	function make_feature_image ($image_id) {
		$this->db->select('product_id');
		$this->db->where('product_image_id',$image_id);
		$result = $this->db->get('product_images');
		
		$image = $result->row_array();
		$product_id = $image['product_id'];
		
		// null all other features for this product
		$this->db->update('product_images',array('product_image_featured' => '0'),array('product_id' => $product_id));
		
		// make this the feature
		$this->db->update('product_images',array('product_image_featured' => '1'),array('product_image_id' => $image_id));
		
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return TRUE;
	}
	
	/**
	* Reset Order for Images
	*
	* @param int $product_id
	*
	*/
	function images_reset_order ($product_id) {
		$this->db->update('product_images',array('product_image_order' => '0'), array('product_id' => $product_id));
		
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
	}
	
	/**
	* Update Order
	*
	* @param int $field_id
	* @param int $new_order
	*
	* @return void
	*/
	function image_update_order ($image_id, $new_order) {
		$this->db->update('product_images',array('product_image_order' => $new_order), array('product_image_id' => $image_id));
		
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
	}
	
	/**
	* Product POST Validation
	*
	* Validates the $_POST and $_FILES input for a new product submission
	*
	* @return string|boolean String of errors if they exist, else TRUE if validated
	*/
	function validation () {
		$CI =& get_instance();
		
		$this->load->library('form_validation');
		$this->load->model('custom_fields_model');
		
		$this->form_validation->set_rules('name','Name','trim|required');
		$this->form_validation->set_rules('description','Description','trim|required');
		$this->form_validation->set_rules('price','Price','trim|numeric|required');
		$this->form_validation->set_rules('weight','Weight','trim|numeric|required');
		
		if ($this->form_validation->run() == FALSE) {
			$errors = rtrim(validation_errors('','||'),'|');
			$errors = explode('||',str_replace('<p>','',$errors));
			return $errors;
		}
		
		// custom field validation
		
		// validate custom fields
		if (setting('products_custom_field_group') != FALSE) {
			$CI->load->library('custom_fields/form_builder');
			$CI->form_builder->build_form_from_group(setting('products_custom_field_group'));
	
			if ($CI->form_builder->validate_post() === FALSE) {
				$errors = $CI->form_builder->validation_errors(TRUE);
				return $errors;
			}
		}
		
		return TRUE;
	}
	
	/**
	* New Product
	*
	* Adds a new product
	*
	* @param string $name Product name
	* @param string $description Product description
	* @param array $collections Array of collection ID's (default: array())
	* @param float $price Product price (e.g., "5.00") (default: 1)
	* @param int $weight Product weight in default units (default: 0)
	* @param boolean $requires_shipping Does it require a shipping address? (default: FALSE)
	* @param boolean $track_inventory Shall we track inventory? (default: FALSE)
	* @param float $starting_inventory How many are in stock right now? (default: 0)
	* @param boolean $allow_oversell Should we allow the product to sell at zero stock? (default: FALSE)
	* @param string $sku The SKU identifier (default: '')
	* @param boolean $is_taxable Is this product subject to taxes? (default: FALSE)
	* @param array $member_tiers A "[group]" => "[price]" array of member tiered pricing (default: array())
	* @param boolean $is_download Is this a downloadable product? (default: FALSE)
	* @param string $download_name Filename for the download in /writeable/product_files/ (default: '')
	* @param int $download_size Total size of the file, in KB (default: 0)
	* @param int $promotion Shall we put purchasers into a usergroup? (default: '')
	* @param array $custom_fields Any custom field data (default: array())
	* @param array $product_options Array of product_options ID's (default: array())
	*
	* @return int $product_id The new product ID
	*/
	function new_product ($name, $description, $collections = array(), $price = 1, $weight = 0, $requires_shipping = FALSE, $track_inventory = FALSE, $starting_inventory = 0, $allow_oversell = FALSE, $sku = '', $is_taxable = FALSE, $member_tiers = array(), $is_download = FALSE, $download_name = '', $download_size = 0, $promotion = '', $custom_fields = array(), $product_options = array()) {
		// generate $url_path
		$this->load->helper('admincp/url_string_helper');
	
		$insert_fields = array(
								'product_url_path' => url_string($name,'products','product_url_path'),
								'product_collections' => (empty($collections)) ? FALSE : serialize($collections),
								'product_name' => $name,
								'product_price' => $price,
								'product_description' => $description,
								'product_weight' => $weight,
								'product_track_inventory' => ($track_inventory == FALSE) ? '0' : '1',
								'product_inventory' => ($track_inventory == FALSE) ? '0' : $starting_inventory,
								'product_inventory_allow_oversell' => ($track_inventory == FALSE or $allow_oversell == FALSE) ? '0' : '1',
								'product_sku' => $sku,
								'product_taxable' => ($is_taxable == FALSE) ? '0' : '1',
								'product_requires_shipping' => ($requires_shipping == FALSE) ? '0' : '1',
								'product_member_tiers' => serialize($member_tiers),
								'product_download' => ($is_download == FALSE) ? '0' : '1',
								'product_download_name' => ($is_download == FALSE) ? '' : $download_name,
								'product_download_size' => ($is_download == FALSE) ? '0' : $download_size,
								'product_promotion' => $promotion,
								'product_options' => (empty($product_options) or !is_array($product_options)) ? '' : serialize($product_options),
								'product_deleted' => '0'
							);
							
		if (is_array($custom_fields)) {					
			foreach ($custom_fields as $name => $value) {
				$insert_fields[$name] = $value;
			}
		}
		
		$this->db->insert('products',$insert_fields);
		
		$product_id = $this->db->insert_id();
		
		// insert collection maps
		foreach ((array)$collections as $collection) {
			if ($collection != '0') {
				$this->db->insert('collection_maps',array('collection_id' => $collection, 'product_id' => $product_id));
			}
		}
		
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return $product_id;
	}
	
	/**
	* Updates Product
	*
	* Updates a product record
	*
	* @param int $product_id The product ID to update
	* @param string $name Product name
	* @param string $description Product description
	* @param array $collections Array of collection ID's (default: array())
	* @param float $price Product price (e.g., "5.00") (default: 1)
	* @param int $weight Product weight in default units (default: 0)
	* @param boolean $requires_shipping Does it require a shipping address? (default: FALSE)
	* @param boolean $track_inventory Shall we track inventory? (default: FALSE)
	* @param float $starting_inventory How many are in stock right now? (default: 0)
	* @param boolean $allow_oversell Should we allow the product to sell at zero stock? (default: FALSE)
	* @param string $sku The SKU identifier (default: '')
	* @param boolean $is_taxable Is this product subject to taxes? (default: FALSE)
	* @param array $member_tiers A "[group]" => "[price]" array of member tiered pricing (default: array())
	* @param boolean $is_download Is this a downloadable product? (default: FALSE)
	* @param string $download_name Filename for the download in /writeable/product_files/ (default: '')
	* @param int $download_size Total size of the file, in KB (default: 0)
	* @param int $promotion Shall we put purchasers into a usergroup? (default: '')
	* @param array $custom_fields Any custom field data (default: array())
	* @param array $product_options Array of product_options ID's (default: array())
	*
	* @return boolean TRUE
	*/
	function update_product ($product_id, $name, $description, $collections = array(), $price, $weight = 0, $requires_shipping = FALSE, $track_inventory = FALSE, $current_inventory = 0, $allow_oversell = FALSE, $sku = '', $is_taxable = FALSE, $member_tiers = array(), $is_download = FALSE, $download_name = '', $download_size = 0, $promotion = '', $custom_fields = array(), $product_options = array()) {
		// generate $url_path
		$this->load->helper('admincp/url_string_helper');

		$update_fields = array(
								
								'product_collections' => (empty($collections) ) ? FALSE : serialize($collections),
								'product_name' => $name,
								'product_price' => $price,
								'product_description' => $description,
								'product_weight' => $weight,
								'product_track_inventory' => ($track_inventory == FALSE) ? '0' : '1',
								'product_inventory' => ($track_inventory == FALSE) ? '0' : $current_inventory,
								'product_inventory_allow_oversell' => ($track_inventory == FALSE or $allow_oversell == FALSE) ? '0' : '1',
								'product_sku' => $sku,
								'product_taxable' => ($is_taxable == FALSE) ? '0' : '1',
								'product_requires_shipping' => ($requires_shipping == FALSE) ? '0' : '1',
								'product_member_tiers' => serialize($member_tiers),
								'product_download' => ($is_download == FALSE) ? '0' : '1',
								'product_download_name' => ($is_download == FALSE) ? '' : $download_name,
								'product_download_size' => ($is_download == FALSE) ? '0' : $download_size,
								'product_promotion' => $promotion,
								'product_options' => (empty($product_options) or !is_array($product_options)) ? '' : serialize($product_options),
								'product_deleted' => '0'
							);
							
		if (is_array($custom_fields)) {					
			foreach ($custom_fields as $name => $value) {
				$update_fields[$name] = $value;
			}
		}
		
		$this->db->update('products',$update_fields,array('product_id' => $product_id));
		
		// update collection maps
		$this->db->delete('collection_maps',array('product_id' => $product_id));
		
		foreach ($collections as $collection) {
			if ($collection != '0') {
				$this->db->insert('collection_maps',array('collection_id' => $collection, 'product_id' => $product_id));
			}
		}
		
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return TRUE;
	}
	
	/**
	* Get Product ID
	*
	* @param string $url_path
	*
	* @return int $product_id
	*/
	function get_product_id ($url_path) {
		$this->db->select('product_id');
		$this->db->where('product_url_path',$url_path);
		$this->db->where('product_deleted','0');
		$product = $this->db->get('products');
		
		if ($product->num_rows() == 0) {
			return FALSE;
		}
		
		$product = $product->row_array();
		
		return $product['product_id'];
	}
	
	/**
	* Get product
	*
	* @param int $product_id
	*
	* @return array Product array, else FALSE
	*/
	function get_product($product_id) {
		$cache_key = 'get_product' . $product_id;
		
		if ($return = $this->CI->cache->file->get($cache_key)) {
			return $return;
		}
			
		$result = $this->get_products(array('id' => $product_id));
		
		if (empty($result)) {
			return FALSE;
		}
		else {
			$product = $result[0];
			$product['images'] = $this->get_images($product['id']);
			
			$this->CI->cache->file->save($cache_key, $product, (5*60));
			
			return $product;
		}
	}
	
	/**
	* Get Images
	*
	* Retrieves product images into an array
	*
	* @param int $product_id
	*
	* @return array Of all product images, stored
	*/
	function get_images ($product_id) {
		$this->db->where('product_id',$product_id);
		$this->db->order_by('product_image_featured DESC, product_image_order ASC');
		$result = $this->db->get('product_images');
		
		$images = array();
		foreach ($result->result_array() as $image) {
			if (strpos($image['product_image_filename'], '/') === FALSE) {
				// we assume it's in the product images folder
				$image_path = setting('path_product_images') . $image['product_image_filename'];
			}
			else {
				// it's a path from the app root
				$image_path = FCPATH . $image['product_image_filename'];
			}
			
			$images[] = array(
								'id' => $image['product_image_id'],
								'path' => $image_path,
								'url' => site_url(str_replace(FCPATH,'',$image_path)),
								'featured' => ($image['product_image_featured'] == '1') ? TRUE : FALSE
							);
		}
		
		return $images;
	}
	
	/**
	* Get Products
	*
	* @param int $filters['id']
	* @param string $filters['type'] ("download" or "shippable")
	* @param string $filters['name']
	* @param int $filters['collection'] Collection ID #
	* @param float $filters['price']
	* @param string $filters['keyword']
	* @param string $filters['sort'] Field to sort by
	* @param string $filters['sort_dir'] ASC or DESC
	* @param int $filters['limit'] How many records to retrieve
	* @param int $filters['offset'] Start records retrieval at this record
	*
	* @return array Array of product data, else FALSE if non match
	*/
	function get_products($filters = array()) {
		// we can only cache if we aren't dynamically grabbing the price, or sorting
		if (isset($this->CI->cache) and $this->CI->user_model->logged_in() == FALSE and (!isset($filters['sort_dir']) or $filters['sort_dir'] != 'rand()')) {
			$caching = TRUE;
			$cache_key = 'get_products' . md5(serialize($filters));
			
			if ($return = $this->CI->cache->file->get($cache_key)) {
				return ($return == 'empty_cache') ? FALSE : $return;
			}
		}
		else {
			$caching = FALSE;
		}
	
		// filters
		if (isset($filters['id'])) {
			$this->db->where('products.product_id',$filters['id']);
		}
		
		if (isset($filters['type'])) {
			if ($filters['type'] == 'download') {
				$this->db->where('product_download','1');
			}
			if ($filters['type'] == 'shippable') {
				$this->db->where('product_requires_shipping','1');
			}
		}

		if (isset($filters['collection'])) {
			if ($filters['collection'] == '0') {
				$this->db->where('products.product_collections', serialize(array(0=>'0')));
			}
			else {
				$this->db->join('collection_maps','collection_maps.product_id = products.product_id','inner');
				$this->db->where('collection_maps.collection_id',$filters['collection']);
			}
		}
		
		if (isset($filters['name'])) {
			$this->db->like('product_name',$filters['name']);
		}
		
		if (isset($filters['price'])) {
			$this->db->where('product_price',$filters['price']);
		}
		
		if (isset($filters['keyword'])) {
			$search_fields = array('`product_name`', '`product_description`', '`product_price`');
			// todo: add custom fields to search fields
			$search_fields = implode(', ', $search_fields);
			
			$this->db->where('MATCH (' . $search_fields . ') AGAINST (\'' . mysql_real_escape_string($filters['keyword']) . '\')', NULL, FALSE);  
			$this->db->select('*');
			$this->db->select('MATCH (' . $search_fields . ') AGAINST (\'' . mysql_real_escape_string($filters['keyword']) . '\') AS `relevance`', FALSE);
		}
		else {
			$this->db->select('*');
		}
	
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'product_name';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		$this->db->join('product_images','product_images.product_id = products.product_id','LEFT');
		$this->db->where('(product_image_featured = \'1\' or product_image_featured IS NULL)');
		$this->db->where('product_deleted','0');
		
		$this->db->select('products.product_id AS true_product_id');
		
		$this->db->group_by('products.product_id');
		
		$result = $this->db->get('products');

		if ($result->num_rows() == 0) {
			if ($caching == TRUE) {
				$this->CI->cache->file->save($cache_key, 'empty_cache', (5*60));
			}
		
			return FALSE;
		}
		
		// get custom fields
		$custom_fields = $this->get_custom_fields();
		if (empty($custom_fields)) {
			$custom_fields = array();
		}
		
		$products = array();
		foreach ($result->result_array() as $product) {
			$product['product_inventory_allow_oversell'] = ($product['product_inventory_allow_oversell'] == '1') ? TRUE : FALSE;
			
			$member_tiers = unserialize($product['product_member_tiers']);
			
			if (empty($member_tiers)) {
				$member_tiers = FALSE;
			}
			
			// determine $feature_image path
			if (!empty($product['product_image_filename'])) {
				if (strpos($product['product_image_filename'], '/') === FALSE) {
					// we assume it's in the product images folder
					$feature_image = setting('path_product_images') . $product['product_image_filename'];
				}
				else {
					// it's a path from the app root
					$feature_image = FCPATH . $product['product_image_filename'];
				}
			}
			
			$this_product = array(
								'id' => $product['true_product_id'],
								'url' => site_url('store/p/' . $product['product_url_path']),
								'url_path' => $product['product_url_path'],
								'quick_add_to_cart_url' => site_url('store/add_to_cart?product_id=' . $product['true_product_id'] . '&quantity=1'),
								'admin_link' => site_url('admincp/store/product/' . $product['true_product_id']),
								'collections' => (empty($product['product_collections'])) ? FALSE : unserialize($product['product_collections']), 
								'name' => $product['product_name'],
								'description' => $product['product_description'],
								'price' => money_format("%!^i",$product['product_price']),
								'weight' => $product['product_weight'],
								'requires_shipping' => ($product['product_requires_shipping'] == '1') ? TRUE : FALSE,
								'track_inventory' => ($product['product_track_inventory'] == '1') ? TRUE : FALSE,
								'inventory' => ($product['product_track_inventory'] == '1') ? $product['product_inventory'] : FALSE,
								'inventory_allow_oversell' => ($product['product_track_inventory'] == '1') ? $product['product_inventory_allow_oversell'] : FALSE,
								'sku' => $product['product_sku'],
								'is_taxable' => ($product['product_taxable'] == '1') ? TRUE : FALSE,
								'member_tiers' => $member_tiers,
								'is_download' => ($product['product_download'] == '1') ? TRUE : FALSE,
								'download_name' => ($product['product_download'] == '1') ? $product['product_download_name'] : FALSE,
								'download_fullpath' => ($product['product_download'] == '1') ? setting('path_product_files') . $product['product_download_name'] : FALSE,
								'download_size' => ($product['product_download'] == '1') ? $product['product_download_size'] : FALSE,
								'promotion' => (!empty($product['product_promotion'])) ? $product['product_promotion'] : FALSE,
								'feature_image' => (!empty($product['product_image_filename'])) ?  $feature_image : FALSE,
								'feature_image_url' => (!empty($product['product_image_filename'])) ? site_url(str_replace(FCPATH,'',$feature_image)) : FALSE,
								'options' => (!empty($product['product_options'])) ? unserialize($product['product_options']) : FALSE,
								'relevance' => (isset($product['relevance'])) ? $product['relevance'] : FALSE
							);
							
			foreach ($custom_fields as $field) {
				$this_product[$field['name']] = $product[$field['name']];
			}
			reset($custom_fields);
			
			// modify price
			if (!defined('_CONTROLPANEL')) {
				$this_product['price'] = $this->get_member_price($member_tiers, $this_product['price']);
			}
			
			$products[] = $this_product;
		}
		
		if ($caching == TRUE) {
			$this->CI->cache->file->save($cache_key, $products, (5*60));
		}
		
		return $products;
	}
	
	/**
	* Knock Inventory
	*
	* Subtract 1 from inventory
	*
	* @param int $product_id
	* @param int $quantity
	*
	* @return int $new_inventory
	*/
	function knock_inventory ($product_id, $quantity = 1) {
		$product = $this->get_product($product_id);
		
		// new inventory
		$new_inventory = ($product['inventory'] - $quantity);
		
		$this->db->update('products', array('product_inventory' => $new_inventory), array('product_id' => $product['id']));
		
		return $new_inventory;
	}
	
	/**
	* Delete Product
	*
	* Marks a product database record as deleted
	*
	* @param int $product_id
	*
	* @return void 
	*/
	function delete_product ($product_id) {
		$this->db->update('products',array('product_deleted' => '1'),array('product_id' => $product_id));
		
		// Clean up the collection_map
		$this->db->delete('collection_maps', array('product_id' => $product_id));
	}
}