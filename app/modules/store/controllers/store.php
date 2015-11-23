<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Store Module
*
* Displays store products, store categories, shopping cart, add to cart, remove from cart
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Store extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function index ($product_url = FALSE) {
		// get all parent collections
		$this->load->model('collections_model');
		$collections = $this->collections_model->get_collections(array('parent' => '0'));
		
		// get all products without a parent
		$this->load->model('products_model');
		$products = $this->products_model->get_products(array('collection' => '0'));
		
		$this->smarty->assign('title','Store');
		$this->smarty->assign('collections',$collections);
		$this->smarty->assign('products', $products);
		
		return $this->smarty->display('store_listing.thtml');
	}
	
	function p ($url_path) {
		// we may have a URL with "/" in it, so let's combine all the arguments into a URL string
		$args = func_get_args();
		$url_path = implode('/',$args);
		// end URL from arguments code
		
		$this->load->model('products_model');
		$product_id = $this->products_model->get_product_id($url_path);
		
		if (empty($product_id)) {
			die(show_404('store/' . $url_path));
		}
		
		$product = $this->products_model->get_product($product_id);
		
		if (empty($product)) {
			die(show_404('store/' . $url_path));
		}
		
		$this->smarty->assign($product);
		
		// product options
		$this->load->model('store/product_option_model');
		$all_options = $this->product_option_model->get_options();
		
		$this->smarty->assign('product_options', $all_options);
		
		// if we only have one category, we'll pass that too
		if (is_array($product['collections']) and count($product['collections']) == 1) {
			$this->load->model('collections_model');
			$collection = $this->collections_model->get_collection($product['collections'][0]);
			
			$this->smarty->assign('collection', $collection);
		}
		
		return $this->smarty->display('store_product.thtml');
	}
	
	/**
	* View a Collection
	*/
	function c ($collection_id) {
		// get all parent collections
		$this->load->model('collections_model');
		$collections = $this->collections_model->get_collections(array('parent' => $collection_id));
		$collection = $this->collections_model->get_collection($collection_id);
		
		// get all products without a parent
		$this->load->model('products_model');
		
		$setting = $this->settings_model->get_setting('front_items_count');
		
		$filters = array(
			'collection' => $collection_id,
			'limit'		=> $setting['value'],
			'offset'	=> $this->input->get('page') ? $this->input->get('page') : 0
		);
		
		$products = $this->products_model->get_products($filters);
		
		$this->smarty->assign('total_products', $this->collections_model->count_products($collection_id));
		$this->smarty->assign('products_per_page', $setting['value']);
		$this->smarty->assign('collection',$collection);
		$this->smarty->assign('collections',$collections);
		$this->smarty->assign('products', $products);
		
		return $this->smarty->display('store_listing.thtml');
	}
	
	/**
	* Add to Cart
	*/
	function add_to_cart () {
		$product_id = ($this->input->get('product_id')) ? $this->input->get('product_id') : $this->input->post('product_id');
		
		// get quantity
		$quantity = ($this->input->get('quantity')) ? $this->input->get('quantity') : $this->input->post('quantity');
		if ((int)$quantity <= 0) {
			$quantity = 1;
		}
		
		if (empty($product_id)) {
			die(show_error('No product_id was passed to the add to cart method.'));
		}
		
		$this->load->model('store/products_model');
		$product = $this->products_model->get_product($product_id);
		
		if ($product['track_inventory'] == TRUE) {
			$stock_check = (int)$product['inventory'] - $quantity;
			
			if ($stock_check < 0 and $product['inventory_allow_oversell'] == FALSE) {
				die(show_error('We have run out of the product you selected.  Sorry for the inconvenience.  <a href="javascript:history.go(-1)">Go back</a>'));
			}
		}
		
		// build options
		$options = array();
		
		if (!empty($product['options'])) {
			$this->load->model('store/product_option_model');
			
			foreach ($product['options'] as $option) {
				$option = $this->product_option_model->get_option($option);
				
				if (!empty($option)) {
					if ($this->input->post('option_' . $option['id'])) {
						$value = $this->input->post('option_' . $option['id']);
						
						// clean it!
						$value = strip_tags($value);
					}
					else {
						$value = 'Unselected';
					}
					
					$options[$option['name']] = $value;
				}
			}
		}
		
		
		$this->load->model('cart_model');
		$this->cart_model->add_to_cart($product_id,
									   $quantity,
									   $options);
		
		redirect('store/cart');
	}
	
	/**
	* Remove from Cart
	*/
	function remove_from_cart ($rowid) {
		$this->load->model('cart_model');
		$this->cart_model->remove_from_cart($rowid);
		
		redirect('store/cart');
	}
	
	/**
	* Show Cart
	*/
	function cart () {
		$this->load->model('cart_model');
		
		// let's reset everything in our cart
		$this->cart_model->reset_to_precoupon();
		
		$this->smarty->assign('cart', $this->cart_model->get_cart());
		$this->smarty->assign('cart_total', $this->cart_model->get_total());
		return $this->smarty->display('store_cart.thtml');
	}
	
	/**
	* Update Cart
	*/
	function update_cart () {	
		// are they really trying to checkout?
		if ($this->input->post('checkout_button')) {
			redirect('checkout');
			die();
		}
	
		$this->load->model('cart_model');
		$cart = $this->cart_model->get_cart();
		
		$this->load->model('store/products_model');
		
		if (!empty($cart)) {
			foreach ($cart as $item) {
				if ($item['is_subscription'] == FALSE) {
					$quantity = $this->input->post('qty_' . $item['rowid']);
					
					$product = $this->products_model->get_product($item['id']);
		
					if ($product['track_inventory'] == TRUE) {
						$stock_check = (int)$product['inventory'] - $quantity;
						
						if ($stock_check < 0 and $product['inventory_allow_oversell'] == FALSE) {
							die(show_error('Unfortunately, the quantity increase of "' . $product['name'] . '" is not possible to fulfill at this time.  Sorry for the inconvenience.  <a href="javascript:history.go(-1)">Go back</a>'));
						}
						else {
							$this->cart_model->update_quantity($item['rowid'], $quantity);
						}
					}
					else
					{
						$this->cart_model->update_quantity($item['rowid'], $quantity);
					}
				}
			}
		}
		
		redirect('store/cart');
	}
	
	/**
	* Download Product
	*/
	function download ($hash) {
		if (strlen($hash) != 32) {
			die(show_error('Hash is invalid.'));
		}
		
		$this->db->where('download_link_hash', $hash);
		$result = $this->db->get('download_links');
		
		if ($result->num_rows() > 0) {
			$row = $result->row_array();
			
			if ($row['download_link_downloads'] >= $this->config->item('maximum_downloads_per_purchase')) {
				die(show_error('This download link has expired.'));
			}
			
			// we have an accessible file
			
			// don't limit to small files
			set_time_limit(0);
			
			// track download
			$this->db->update('download_links', array('download_link_downloads' => ($row['download_link_downloads'] + 1)), array('download_link_hash' => $row['download_link_hash']));
			
			// get extension
			$this->load->helper('file_extension');
			$extension = file_extension($row['download_link_path']);
			
			// get the mime type
			include(APPPATH . 'config/mimes.php');
			
			if (!isset($mimes[$extension])) {
				die(show_error('Failed to retrieve mime-type data for file extension "' . $extension . '".'));
			}
			
			$mime_type = $mimes[$extension];

			// some mime types are arrays...
			if (is_array($mime_type)) {
				$mime_type = $mime_type[0];
			}
			
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", FALSE); // required for certain browsers 
			header("Content-Type: " . $mime_type);
			header("Content-Disposition: attachment; filename=\"". $row['download_link_hash'] . '.' . file_extension($extension) . "\";");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: " . filesize(setting('path_product_files') . $row['download_link_path']));
			
			readfile(setting('path_product_files') . $row['download_link_path']);
		    die();
		}
		else {
			die(show_error('This download link is invalid.'));
		}
	}
}