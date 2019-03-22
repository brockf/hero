<?php

class Search_results {
	var $results;
	var $content_results;
	var $product_results;
	var $relevance_keys;
	var $query;
	var $results_per_page = 25;
	var $total_results;
	
	function __construct () {
		$this->total_results = 0;
		$this->results = array();
		$this->relevance_keys = array();
		
		$this->CI =& get_instance();
	}
	
	/**
	* Search
	*
	* Performs the search and stores results
	*
	* @param string $query
	*/
	function search ($query = '', $page = 0) {
		$this->CI->load->helper('shorten');
	
		// content search
		$content_types = unserialize(setting('search_content_types'));
		
		if (!empty($content_types)) {
			$this->CI->load->model('publish/content_model');
			$this->CI->load->model('publish/content_type_model');
			$this->CI->load->model('custom_fields_model');
		
			foreach ($content_types as $type => $summary_field) {
				$content = $this->CI->content_model->get_contents(array('keyword' => $query, 'type' => $type, 'sort' => 'relevance', 'sort_dir' => 'DESC', 'limit' => '50'));
				
				if (!empty($content)) {
					foreach ($content as $item) {
						// prep summary field
						if (!empty($summary_field)) {
							$item['summary'] = shorten(strip_tags($item[$summary_field]), setting('search_trim'), TRUE);
						}
						$item['result_type'] = 'content';
						
						$this->content_results[$item['id']] = $item;
						$this->relevance_keys['content|' . $item['id']] = $item['relevance'];
					}
				}
			}
		}
		
		// product search
		if (setting('search_products') == '1' and module_installed('store')) {
			$this->CI->load->model('store/products_model');
			
			$products = $this->CI->products_model->get_products(array('keyword' => $query, 'sort' => 'relevance', 'sort_dir' => 'DESC', 'limit' => '50'));
			
			if (!empty($products)) {
				foreach ($products as $product) {
					// prep summary field
					$product['summary'] = shorten(strip_tags($product['description']), setting('search_trim'), TRUE);
					$product['result_type'] = 'product';
				
					$this->product_results[$product['id']] = $product;
					$this->relevance_keys['product|' . $product['id']] = $product['relevance'];
				}
			}
		}
		
		// sort results
		arsort($this->relevance_keys);
		
		// put together final results array
		foreach ($this->relevance_keys as $item => $relevance) {
			list($type,$id) = explode('|', $item);
			
			if ($type == 'content') {
				$this->results[] = $this->content_results[$id];
			}
			elseif ($type == 'product') {
				$this->results[] = $this->product_results[$id];
			}
		}
		
		// how many total results?
		$this->total_results = count($this->results);
		
		if ($this->total_results == 0) {
			return array();
		}
		
		// grab the segment of the array corresponding to our page
		return array_slice($this->results, $page * $this->results_per_page, $this->results_per_page);
	}
	
	/**
	* Generates Pagination for a Search Request
	* 
	* @param string $base_url
	*
	* @return string $links
	*/
	function get_pagination ($base_url) {
		$this->CI->load->library('pagination');
		
		$config['base_url'] = $base_url;
		$config['total_rows'] = $this->total_results;
		$config['per_page'] = $this->results_per_page;
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'page';
		
		$this->CI->pagination->initialize($config);
		
		$links = $this->CI->pagination->create_links();
		
		// we may have cases of ?& because of CodeIgniter thinking we have universally enabled query strings
		$links = str_replace('?&amp;','?', $links);
		
		return $links;
	}
	
	/**
	* Get Total Results
	*
	* @return int How many results are there?
	*/
	function get_total_results () {
		return $this->total_results;
	}
}