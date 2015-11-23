<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Collections Model 
*
* Contains all the methods used to create, update, and delete collections.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Collections_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Get Collection Custom Fields
	*
	* Gets all collection data custom fields
	*
	* @return array Array of custom field data, else FALSE
	*/
	function get_custom_fields () {
		$CI =& get_instance();
		$CI->load->model('custom_fields_model');
		
		return $CI->custom_fields_model->get_custom_fields(array('group' => setting('collections_custom_field_group')));
	}

	/**
	* New Collection
	*
	* @param string $name
	* @param string $description (default: '')
	* @param int $parent (default: 0)
	* @param array $custom_fields Custom field data from form_builder::post_to_array() (default: array())
	*
	* @return int $collection_id
	*/
	function new_collection ($name, $description = '', $parent = 0, $custom_fields = array()) {
		$insert_fields = array(
								'collection_name' => $name,
								'collection_description' => $description,
								'collection_parent_id' => $parent,
								'collection_deleted' => '0'
							);
							
		if (is_array($custom_fields)) {					
			foreach ($custom_fields as $name => $value) {
				$insert_fields[$name] = $value;
			}
		}
							
		$this->db->insert('collections',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Update Collection
	*
	* @param int $collection_id
	* @param string $name
	* @param string $description (default: '')
	* @param int $parent (default: 0)
	* @param array $custom_fields Custom field data from form_builder::post_to_array() (default: array())
	*
	* @return void
	*/
	function update_collection ($collection_id, $name, $description = '', $parent = 0, $custom_fields = array()) {
		$update_fields = array(
								'collection_name' => $name,
								'collection_description' => $description,
								'collection_parent_id' => $parent
							);
							
		if (is_array($custom_fields)) {					
			foreach ($custom_fields as $name => $value) {
				$update_fields[$name] = $value;
			}
		}
							
		$this->db->update('collections',$update_fields,array('collection_id' => $collection_id));
		
		return;
	}
	
	/**
	* Delete Collection
	*
	* @param int $collection_id
	*
	* @return boolean
	*/
	function delete_collection ($collection_id) {
		$this->db->update('collections',array('collection_deleted' => '1'), array('collection_id' => $collection_id));
		
		// delete all children, too
		$result = $this->get_collections(array());
		
		if (!$result) {
			// no collections
			return TRUE;
		}
		
		$collections = array();
		
		foreach ($result as $collection) {
			$collections[$collection['parent']][$collection['id']] = $collection['name'];
		}
		
		if (isset($collections[$collection_id])) {
			// has children
			foreach ($collections[$collection_id] as $child_id => $child) {
				$this->db->update('collections',array('collection_deleted' => '1'), array('collection_id' => $child_id));
			}
		}
		
		return TRUE;
	}
	
	/**
	* Get Tiered Collections
	*
	* Gets an array of all collections, tiered nicely in a hierarchical structure
	* It's a big ugly function, though, so call sparingly.  Not a multidimensional array.
	*
	* returns: Shoes
	*		   Shoes > Adidas
	*          Shoes > Adidas > Crosstrainers
	*
	* @param array $filters identical to get_collections()
	*
	* @return array Collections
	*/
	function get_tiered_collections ($filters = array()) {
		$result = $this->get_collections($filters);
		
		if (!$result) {
			// no collections
			
			return array();
		}
		
		$collections = array();
		
		foreach ($result as $collection) {
			$collections[$collection['parent']][$collection['id']] = $collection['name'];
		}
		
		if (!isset($collections[0])) {
			// no collections at the parent node
			return array();
		}
		
		$tiers = array();
		// start at parent 0 and go from there
		foreach ($collections[0] as $id => $name) {
			$tiers[$id] = array('id' => $id, 'name' => $name);
			
			if (isset($collections[$id]) and is_array($collections[$id])) {
				foreach ($collections[$id] as $id_2 => $name_2) {
					$tiers[$id_2] = array('id' => $id_2, 'name' => $name . ' > ' . $name_2);
					
					if (isset($collections[$id_2]) and is_array($collections[$id_2])) {
						foreach ($collections[$id_2] as $id_3 => $name_3) {
							$tiers[$id_3] = array('id' => $id_3, 'name' => $name . ' > ' . $name_2 . ' > ' . $name_3);
							
							if (isset($collections[$id_3]) and is_array($collections[$id_3])) {
								foreach ($collections[$id_3] as $id_4 => $name_4) {
									$tiers[$id_4] = array('id' => $id_4, 'name' => $name . ' > ' . $name_2 . ' > ' . $name_3 . ' > ' . $name_4);
									
									if (isset($collections[$id_4]) and is_array($collections[$id_4])) {
										foreach ($collections[$id_4] as $id_5 => $name_5) {
											$tiers[$id_5] = array('id' => $id_5, 'name' => $name . ' > ' . $name_2 . ' > ' . $name_3 . ' > ' . $name_4 . ' > ' . $name_5);
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		return $tiers;
	}
	
	/**
	* Get Collection
	*
	* @param int $collection_id
	*
	* @return array Array of data, else FALSE
	*/
	function get_collection ($collection_id) {
		$collection = $this->get_collections(array('id' => $collection_id), TRUE);
		
		if (!empty($collection)) {
			return $collection[0];
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Get Collections
	*
	* @param int $filters['parent']
	* @param int $filters['id']
	* @param string $filters['name'] 
	* @param boolean $any_status Should we retrieve even deleted collections? (default: FALSE)
	*
	* @return array $collections
	*/
	function get_collections ($filters = array(), $any_status = FALSE) {
		if (isset($filters['parent'])) {
			$this->db->where('collection_parent_id',$filters['parent']);
		}
		if (isset($filters['id'])) {
			$this->db->where('collection_id',$filters['id']);
		}
		if (isset($filters['name'])) {
			$this->db->like('collection_name',$filters['name']);
		}
		
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'collection_name';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		if ($any_status == FALSE) {
			$this->db->where('collection_deleted','0');
		}
		$result = $this->db->get('collections');
		
		// get custom fields
		$custom_fields = $this->get_custom_fields();
		if (empty($custom_fields)) {
			$custom_fields = array();
		}
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		else {
			$collections = array();
			foreach ($result->result_array() as $collection) {
				$this_collection = array(
									'id' => $collection['collection_id'],
									'url' => site_url('store/c/' . $collection['collection_id']),
									'name' => $collection['collection_name'],
									'description' => $collection['collection_description'],
									'parent' => $collection['collection_parent_id']
									);
									
				foreach ($custom_fields as $field) {
					$this_collection[$field['name']] = $collection[$field['name']];
				}
				reset($custom_fields);
				
				$collections[] = $this_collection;
			}
			
			return $collections;
		}
	}
	
	/**
	 * Count Products
	 *
	 * Returns the number of total products in this collection.
	 *
	 * @param	int	$collection_id
	 *
	 * @return 	int Total number of products in collection.
	 */
	function count_products($collection_id)
	{
		$this->db->where('collection_id', $collection_id);
		return $this->db->count_all_results('collection_maps');
	}
}