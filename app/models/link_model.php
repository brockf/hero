<?php

/*
* Links Model
*
* Universally, each URL path maps to a module/controller/method through a global
* record in this table.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Link_model extends CI_Model {
	function __construct() {
		parent::CI_Model();
	}
	
	/*
	* Create New Link
	*
	* @param string $url_path The path to the content
	* @param string $title The title of the page/content
	* @param string $type_name The type name to refer to the content as (e.g., RSS Feed, Download, Article)
	* @param string $module The module name in the modules/ folder
	* @param string $controller The controller to initiate
	* @param string $method The method to instantiate and pass the $url_path string to via mod_rewrite
	*
	* @return $link_id
	*/
	function new_link ($url_path, $title, $type_name, $module, $controller, $method) {
		$url_path = $this->prep_url_path($url_path);
	
		$insert_fields = array(
								'link_url_path' => $url_path,
								'link_title' => $title,
								'link_type' => $type_name,
								'link_module' => $module,
								'link_controller' => $controller,
								'link_method' => $method
							);
							
		$this->db->insert('links',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	function update_title ($link_id, $title) {
		$update_fields = array('link_title' => $title);
		$this->db->update('links',$update_fields,array('link_id' => $link_id));
		
		return TRUE;
	}
	
	function update_url ($link_id, $url_path) {
		$url_path = $this->prep_url_path($url_path);
		
		$update_fields = array('link_url_path' => $url_path);
		$this->db->update('links',$update_fields,array('link_id' => $link_id));
		
		return TRUE;
	}
	
	/*
	* Prep URL Path
	*
	* @param string $url_path
	*
	* @return string $url_path
	*/
	function prep_url_path($url_path) {
		// strip leading slash
		if (substr($url_path, 0, 1) == '/') {
			$url_path = substr_replace($url_path, '', 0, 1);
		}
		
		// strip trailing slash
		if (substr($url_path, -1, 1) == '/') {
			$url_path = substr_replace($url_path, '', -1, 1);
		}
		
		return $url_path;
	}
	
	/*
	* Get Unique URL Path
	*
	* Checks a string to make sure it's a unique URL path in the system.
	* If not, it makes it url_path_2, url_path_3, etc.
	*
	* @param string $url_path
	*
	* @return string $url_path
	*/
	function get_unique_url_path ($url_path) {
		$url_path = $this->prep_url_path($url_path);
	
		$this->db->where('link_url_path',$url_path);
		$this->db->select('link_id');
		$result = $this->db->get('links');
		$count = 1;
		while ($result->num_rows() > 0) {
			// strip final numbers
			$url_path = preg_replace('/(.*?)\_([0-9]*)$/i','$1',$url_path);
			
			// try with a new appended number
			$url_path = $url_path . '_' . $count;
			
			$count++;
			
			$this->db->where('link_url_path',$url_path);
			$this->db->select('link_id');
			$result = $this->db->get('links');
		}
		
		return $url_path;
	}
}