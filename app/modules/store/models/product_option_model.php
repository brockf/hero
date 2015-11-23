<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Product Option Model 
*
* Contains all the methods used to create, update, and delete product options.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Product_option_model extends CI_Model
{
	var $cache_options;
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* New Product Option
	*
	* @param string $name
	* @param array $values with each value having keys "label" and "price" (default: array())
	* @param boolean $save Should this be saved for use in other products? (default: FALSE)
	*
	* @return int $option_id
	*/
	function new_option ($name, $values = array(), $save = FALSE) {
		$values = serialize($values);
	
		$this->db->insert('product_options',array(
											'product_option_name' => $name,
											'product_option_options' => $values,
											'product_option_share' => ($save == TRUE) ? '1' : '0'
										));
										
		return $this->db->insert_id();
	}
	
	/**
	* Update Product Option
	*
	* @param int 	$option_id
	* @param string	$name
	* @param array	$values with each value having keys "label" and "price" (default: array())
	*
	* @return boolean
	*/
	function update_option($option_id, $name, $values=array())
	{
		$values = serialize($values);
		
		$this->db->where('product_option_id', $option_id);
		return $this->db->update('product_options', array(
									'product_option_name'		=> $name,
									'product_option_options'	=> $values,
								));
	}
	
	/**
	* Get Option
	*
	* @param int $option_id
	* 
	* @return array 
	*/
	function get_option ($option_id) {
		if (isset($this->cache_options[$option_id])) {
			return $this->cache_options[$option_id];
		}
	
		$option = $this->get_options(array('id' => $option_id));
		
		if (empty($option)) {
			return FALSE;
		}
		
		$this->cache_options[$option_id] = $option[$option_id];
		
		return $option[$option_id];
	}
	
	/**
	* Delete Option
	*
	* @param int $option_id
	*
	* @return void
	*/
	function delete_option ($option_id) {
		$this->db->delete('product_options', array('product_option_id' => $option_id));
	}
	
	/**
	* Get Options
	*
	* @param int $filters['id']
	* @param int $filters['shared'] - Is it an option shared with other products?
	*
	* @return array 
	*/
	function get_options ($filters = array()) {
		if (isset($filters['shared'])) {
			$this->db->where('product_option_share',$filters['shared']);
		}
		
		if (isset($filters['id'])) {
			$this->db->where('product_option_id', $filters['id']);
		}
		
		if (isset($filters['name'])) {
			$this->db->where('product_option_name', $filters['name']);
		}
		
		$this->db->order_by('product_option_name','ASC');
		$this->db->from('product_options');
		
		$options = $this->db->get();
		
		if ($options->num_rows() == 0) {
			return FALSE;
		}
		
		$return = array();
		
		foreach ($options->result_array() as $option) {
			$return[$option['product_option_id']] = array(
							'id' => $option['product_option_id'],
							'name' => $option['product_option_name'],
							'options' => unserialize($option['product_option_options']),
							'shared' => ($option['product_option_share'] == '1') ? TRUE : FALSE,
							'admin_link' => site_url('admincp/store/product_option/' . $option['product_option_id']),
						);
		}
		
		return $return;
	}
}