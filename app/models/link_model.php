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
	* @param array|boolean $topics Either an array of Topic ID's or FALSE
	* @param string $title The title of the page/content
	* @param string $type_name The type name to refer to the content as (e.g., RSS Feed, Download, Article)
	* @param string $module The module name in the modules/ folder
	* @param string $controller The controller to initiate
	* @param string $method The method to instantiate and pass the $url_path string to via mod_rewrite
	*
	* @return $link_id
	*/
	function new_link ($url_path, $topics, $title, $type_name, $module, $controller, $method) {
		$url_path = $this->prep_url_path($url_path);
	
		$insert_fields = array(
								'link_topics' => (is_array($topics) and !empty($topics)) ? serialize($topics) : '',
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
	
	function update_topics ($link_id, $topics) {
		$update_fields = array('link_topics' => (is_array($topics) and !empty($topics)) ? serialize($topics) : '');
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
	
	/*
	* Get Universal Content Links
	*
	* @param $filters['sort']
	* @param $filters['sort_dir']
	* @param $filters['offset']
	* @param $filters['limit']
	*
	* @return array|boolean
	*/
	function get_links ($filters = array()) {
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'links.link_title';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		$result = $this->db->get('links');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$links = array();
		foreach ($result->result_array() as $link) {
			$links[] = array(
								'id' => $link['link_id'],
								'title' => $link['link_title'],
								'type' => $link['link_type'],
								'module' => $link['link_module'],
								'controller' => $link['link_controller'],
								'method' => $link['link_method']
							);
		}
		
		return $links;
	}
}