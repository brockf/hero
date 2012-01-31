<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Menu Model 
*
* Contains all the methods used to create, update, and delete menus.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Menu_model extends CI_Model
{
	private $CI;
	
	function __construct()
	{
		parent::__construct();
		
		$this->CI =& get_instance();
	}
	
	/**
	* Create New Menu
	*
	* @param string $name
	*
	* @return int $menu_id
	*/
	function new_menu ($name) {
		$this->load->helper('clean_string');
		$name = clean_string($name);
		
		// make sure it's unique
		$duplicates = $this->get_menus(array('name' => $name));
		if (!empty($duplicates)) {
			die(show_error('A menu with that name (' . $name . ') already exists.'));
		}
	
		$insert_fields = array(
							'menu_name' => $name
						);
						
		$this->db->insert('menus',$insert_fields);
		$link_id = $this->db->insert_id();
		
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return $link_id;
	}
	
	/**
	* Add Link to Menu
	*
	* @param int $menu_id Which menu does it belong to?
	* @param int $parent_link If it's a 2nd_tier link name the parent
	* @param string $type Either 'external', 'special', or 'link'
	* @param int $link_id If it's in the universal link database, what's the link_id?
	* @param string $text The display text
	* @param string $special_type If it's a "special" link, give it a name (e.g., "store", "account") (default: FALSE)
	* @param string $external_url The full URL for external links (default: FALSE)
	* @param array $privileges A serialized array of member groups who can see it (default: array())
	*
	* @return int $menu_link_id
	*/
	function add_link ($menu_id, $parent_link = FALSE, $type, $link_id = FALSE, $text, $special_type = FALSE, $external_url = FALSE, $privileges = array()) {
		// get next order
		$links = $this->get_links(array('menu' => $menu_id), TRUE);
		if (is_array($links)) {
			// get last item
			$last_link = end($links);
			$order = $last_link['order'] + 1;
		}
		else {
			$order = 1;
		}
	
		$insert_fields = array(
								'menu_id' => $menu_id,
								'parent_menu_link_id' => $parent_link,
								'menu_link_type' => $type,
								'link_id' => (!empty($link_id)) ? $link_id : '0',
								'menu_link_text' => $text,
								'menu_link_special_type' => (!empty($special_type)) ? $special_type : '',
								'menu_link_external_url' => (!empty($external_url)) ? $external_url : '',
								'menu_link_privileges' => (!empty($privileges)) ? serialize($privileges) : '',
								'menu_link_order' => $order,
								'menu_link_class' => ''
							);
							
		$this->db->insert('menus_links', $insert_fields);
		
		$link_id = $this->db->insert_id();
		
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return $link_id;
	}
	
	/**
	* Update Link
	*
	* @param int $menu_link_id The link ID to edit
	* @param string $text The display text
	* @param array $privileges A serialized array of member groups who can see it (default: array())
	* @param string $class The element CSS class (default: FALSE)
	*
	* @return int $menu_link_id
	*/
	function update_link ($menu_link_id, $text, $privileges = array(), $class = FALSE, $external_url=null) {
		$update_fields = array(
								'menu_link_text' => $text,
								'menu_link_privileges' => (!empty($privileges)) ? serialize($privileges) : '',
								'menu_link_class' => $class
							);
		
		if (!is_null($external_url))
		{
			$update_fields['menu_link_external_url'] = $external_url;
		}
		
		$this->db->update('menus_links', $update_fields, array('menu_link_id' => $menu_link_id));
		
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return TRUE;
	}
	
	/**
	* Remove a link
	*
	* @param int $menu_link_id
	*
	* @return boolean
	*/
	function remove_link ($menu_link_id) {
		$this->db->delete('menus_links', array('menu_link_id' => $menu_link_id));
		
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return TRUE;
	}
	
	/**
	* Get Menu
	*
	* @param int $id
	*
	* @return array
	*/
	function get_menu ($id) {
		$menu = $this->get_menus(array('id' => $id));
		
		if (empty($menu)) {
			return FALSE;
		}
		
		return $menu[0];
	}
	
	/**
	* Get Menu by Name
	*
	* @param string $name
	*
	* @return array
	*/
	function get_menu_by_name ($name) {
		$menu = $this->get_menus(array('name' => $name));
		
		if (empty($menu)) {
			return FALSE;
		}
		
		return $menu[0];
	}
		
	/**
	* Get Menus
	*
	* @param int $filters['id']
	* @param int $filters['name']
	*
	* @return array 
	*/
	function get_menus ($filters = array()) {
		if (isset($filters['id'])) {
			$this->db->where('menu_id',$filters['id']);
		}
		if (isset($filters['name'])) {
			$this->db->where('menu_name',$filters['name']);
		}
	
		$this->db->order_by('menu_name');
		$result = $this->db->get('menus');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$menus = array();
		foreach ($result->result_array() as $row) {
			$menus[] = array(
						'id' => $row['menu_id'],
						'name' => $row['menu_name']
					);
		}
		
		return $menus;
	}
	
	/**
	* Get Link
	*
	* @param int $id 
	*
	* @return array 
	*/
	function get_link ($id) {
		$link = $this->get_links(array('id' => $id));
		
		if (empty($link)) {
			return FALSE;
		}
		else {
			return $link[0];
		}
	}
	
	/**
	* Delete Menu
	*
	* @param int $menu_id
	*
	* @return void
	*/
	function delete_menu ($menu_id) {
		$this->db->delete('menus',array('menu_id' => $menu_id));
		$this->db->delete('menus_links',array('menu_id' => $menu_id));
		
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return;
	}
	
	/**
	* Get Links
	*
	* @param int $filters['menu'] Menu ID
	* @param int $filters['parent'] Parent link ID
	* @param boolean $no_cache Don't use cache (default: FALSE)
	*
	* @return array $links
	*/
	function get_links ($filters = array(), $no_cache = FALSE) {
		// caching
		// we'll only cache for calls with a filter menu as all frontend calls
		// have this parameter
		
		if (isset($this->CI->cache) and isset($filters['menu']) and $no_cache == FALSE) {
			$caching = TRUE;
			$cache_key = 'get_links' . md5(serialize($filters));
			
			if ($return = $this->CI->cache->file->get($cache_key)) {
				return ($return == 'empty_cache') ? FALSE : $return;
			}
		}
		else {
			$caching = FALSE;
		}
		
		// no cache, continue...
	
		if (isset($filters['menu'])) {
			$this->db->where('menu_id',$filters['menu']);
		}
		if (isset($filters['parent'])) {
			$this->db->where('parent_menu_link_id',$filters['parent']);
		}
		if (isset($filters['id'])) {
			$this->db->where('menu_link_id',$filters['id']);
		}
	
		$this->db->order_by('menu_link_order');
		
		$this->db->join('links','links.link_id = menus_links.link_id','left');
		
		$result = $this->db->get('menus_links');
		
		if ($result->num_rows() == 0) {
			// save cache?
			if ($caching == TRUE) {
				$this->CI->cache->file->save($cache_key, 'empty_cache', (24*60*60));
			}
			
			return FALSE;
		}
		
		$links = array();
		foreach ($result->result_array() as $row) {
			$this->db->where('parent_menu_link_id',$row['menu_link_id']);
			$result2 = $this->db->get('menus_links');
			$children = $result2->num_rows();
		
			$links[] = array(
						'id' => $row['menu_link_id'],
						'menu_id' => $row['menu_id'],
						'children' => $children,
						'parent_menu_link_id' => $row['parent_menu_link_id'],
						'text' => $row['menu_link_text'],
						'type' => $row['menu_link_type'],
						'class' => $row['menu_link_class'],
						'link_id' => $row['link_id'],
						'special_type' => (!empty($row['menu_link_special_type'])) ? $row['menu_link_special_type'] : FALSE,
						'external_url' => (!empty($row['menu_link_external_url'])) ? $row['menu_link_external_url'] : FALSE,
						'privileges' => (!empty($row['menu_link_privileges'])) ? unserialize($row['menu_link_privileges']) : FALSE,
						'order' => $row['menu_link_order'],
						'link_url_path' => $row['link_url_path']
					);
		}
		
		if ($caching == TRUE) {
			$this->CI->cache->file->save($cache_key, $links, (24*60*60));
		}
		
		return $links;
	}
}