<?php

/*
* Links Model
*
* Universally, each URL path maps to a module/controller/method through a global
* record in this table.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Link_model extends CI_Model {
	// initialized in __construct()
	// these routes can't be used as URL paths
	var $protected_routes;

	function __construct() {
		parent::__construct();

		$this->protected_routes = array(
										'checkout',
										'subscriptions'
									);
	}

	/**
	* Create New Link
	*
	* @param string $url_path The path to the content
	* @param array|boolean $topics Either an array of Topic ID's or FALSE
	* @param string $title The title of the page/content
	* @param string $type_name The type name to refer to the content as (e.g., RSS Feed, Download, Article)
	* @param string $module The module name in the modules/ folder
	* @param string $controller The controller to initiate
	* @param string $method The method to instantiate and pass the $url_path string to via mod_rewrite
	* @param string $parameter Some functions - like URLs mapped to templates - require a parameter to identify what to load at this URL (default: '')
	*
	* @return int $link_id
	*/
	function new_link ($url_path, $topics, $title, $type_name, $module, $controller, $method, $parameter = '') {
		$url_path = $this->prep_url_path($url_path);
		$url_path = $this->get_unique_url_path($url_path);

		$insert_fields = array(
								'link_topics' => (is_array($topics) and !empty($topics)) ? serialize($topics) : '',
								'link_url_path' => $url_path,
								'link_title' => $title,
								'link_type' => $type_name,
								'link_module' => $module,
								'link_controller' => $controller,
								'link_method' => $method,
								'link_parameter' => $parameter
							);

		$this->db->insert('links',$insert_fields);

		$link_id = $this->db->insert_id();

		// update routes file
		$this->gen_routes_file();

		return $link_id;
	}

	/**
	* Delete Inactive Link
	*
	* @param int $link_id
	*
	* @return boolean TRUE
	*/
	function delete_link ($link_id) {
		$this->db->delete('links',array('link_id' => $link_id));

		// update routes file
		$this->gen_routes_file();

		return TRUE;
	}

	/**
	* Update Link Title
	*
	* @param int $link_id
	* @param string $title
	*
	* @return boolean TRUE
	*/
	function update_title ($link_id, $title) {
		$update_fields = array('link_title' => $title);
		$this->db->update('links',$update_fields,array('link_id' => $link_id));

		return TRUE;
	}

	/**
	* Update Link URL
	*
	* @param int $link_id
	* @param string $url_path
	*
	* @return boolean TRUE
	*/
	function update_url ($link_id, $url_path) {
		$url_path = $this->prep_url_path($url_path);

		$update_fields = array('link_url_path' => $url_path);
		$this->db->update('links',$update_fields,array('link_id' => $link_id));

		// update routes file
		$this->gen_routes_file();

		return TRUE;
	}

	/**
	* Update Link Topics
	*
	* @param int $link_id
	* @param array $topics
	*
	* @return boolean TRUE
	*/
	function update_topics ($link_id, $topics) {
		$update_fields = array('link_topics' => (is_array($topics) and !empty($topics)) ? serialize($topics) : '');
		$this->db->update('links',$update_fields,array('link_id' => $link_id));

		return TRUE;
	}

	/**
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

		// regex the bad stuff out
		$url_path = preg_replace('/\s+/','_',$url_path);
		$url_path = preg_replace('/<(.*?)>/','',$url_path);
		$url_path = preg_replace('/\/{2,10}/','',$url_path);
		$url_path = preg_replace('/[^a-z0-9\/\-\._]/i','',$url_path);
		$url_path = preg_replace('/_+/i','_',$url_path);

		return $url_path;
	}

	/**
	* Check Unique
	*
	* @param string $url_path
	*
	* @return boolean TRUE if unique
	*/
	function is_unique ($url_path) {
		$this->db->where('link_url_path',$url_path);
		$this->db->select('link_id');
		$result = $this->db->get('links');

		if ($result->num_rows() > 0) {
			return FALSE;
		}
		else {
			return TRUE;
		}
	}

	/**
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

		// verify it doesn't conflict with protected routes
		if (in_array($url_path, $this->protected_routes)) {
			$url_path .= '_1';
		}

		$this->db->where('link_url_path',$url_path);
		$this->db->select('link_id');
		$result = $this->db->get('links');
		$count = 1;
		while ($result->num_rows() > 0) {
			// strip final numbers
			$url_path = preg_replace('/(.*?)\_\-([0-9]*)$/i','$1',$url_path);

			// try with a new appended number
			$url_path = $url_path . '_' . $count;

			$count++;

			$this->db->where('link_url_path',$url_path);
			$this->db->select('link_id');
			$result = $this->db->get('links');
		}

		return $url_path;
	}

	/**
	* Get Universal Content Links
	*
	* @param string $filters['url_path']
	* @param string $filters['parameter']
	* @param $filters['sort']
	* @param $filters['sort_dir']
	* @param $filters['offset']
	* @param $filters['limit']
	*
	* @return array|boolean Links
	*/
	function get_links ($filters = array()) {
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'links.link_title';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
		$this->db->order_by($order_by, $order_dir);

		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}

		if (isset($filters['url_path'])) {
			$this->db->where('link_url_path',$filters['url_path']);
		}

		if (isset($filters['parameter'])) {
			$this->db->where('link_parameter',$filters['parameter']);
		}

		$result = $this->db->get('links');

		if ($result->num_rows() == 0) {
			return FALSE;
		}

		$links = array();
		foreach ($result->result_array() as $link) {
			$links[] = array(
								'id' => $link['link_id'],
								'url_path' => $link['link_url_path'],
								'title' => $link['link_title'],
								'type' => $link['link_type'],
								'module' => $link['link_module'],
								'controller' => $link['link_controller'],
								'method' => $link['link_method'],
								'parameter' => $link['link_parameter']
							);
		}

		return $links;
	}

	/**
	* Create Routes File
	*
	* Generates a CodeIgniter routes declaration file included in /app/config/routes.php from
	* the universal links database
	*
	* @return boolean TRUE
	*/
	function gen_routes_file () {
		$this->load->helper('file');

		$links = $this->get_links();

		$routes = array();

		if (!empty($links)) {
			foreach ($links as $link) {
				$routes[$link['url_path']] = $link['module'] . '/' . $link['controller'] . '/' . $link['method'] . '/' . $link['url_path'];
			}
		}

		// generate PHP file
		$file = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');\n\n";

		if (!empty($routes)) {
			foreach ($routes as $route => $path) {
				$file .= '$route[\'' . $route . '\'] = \'' . $path . '\';' . "\n";
			}
		}

		write_file(FCPATH . 'writeable/routes.php',$file,'w');
	}
}