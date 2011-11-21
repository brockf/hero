<?php

/**
* Content Type Model
*
* Manages content types, including their custom fields
*
* @author Electric Function, Inc.
* @package Hero Framework

*/

class Content_type_model extends CI_Model
{
	private $CI;
	
	function __construct()
	{
		parent::__construct();
		
		$this->CI =& get_instance();
	}
	
	/**
	* Create New Content Type
	*
	* Creates a new content type, including the associated table and custom field group
	*
	* @param string $name
	* @param boolean $is_standard Include Title, URL Path, and Topic dropdown? (default: FALSE)
	* @param boolean $is_privileged Include Restrict Access to Member Group(s) Dropdown? (default: FALSE)
	* @param boolean $is_module Should this be treated as an automatic content type?  Or is there another admin module which will manage this content type? (default: FALSE)
	* @param string $template The filename of the template in the theme directory to use for output (default: content.thtml)
	* @param string $base_url If this will be managed with the standard content module, we can pre-populate the URL string with this base_url. (default: '')
	*
	* @return int $content_type_id
	*/
	function new_content_type ($name, $is_standard = TRUE, $is_privileged = FALSE, $is_module = FALSE, $template = 'content.thtml', $base_url = '') {
		// prep base url
		$base_url = rtrim($base_url, '/');
		$base_url = (!empty($base_url)) ? $base_url . '/' : '';
		
		// get system name
		$this->load->helper('clean_string');
		$system_name = clean_string($name);
		
		// make sure table doesn't already exist, after we clear the cache
		// we clear the cache because, when theme installs run, we don't want to run into errors about
		// non-existant table conflicts
		$this->db->data_cache = array();
		if ($this->db->table_exists($system_name)) {
			die(show_error('There is already a table in the database by the name of ' . $system_name . '.  You should rename your content type to avoid a conflict.'));
		}
		
		// create custom field group
		$this->load->model('custom_fields_model');
		$custom_field_group_id = $this->custom_fields_model->new_group('Content: ' . $name);
		
		$insert_fields = array(
							'content_type_is_module' => ($is_module == FALSE) ? '0' : '1',
							'content_type_friendly_name' => $name,
							'content_type_system_name' => $system_name,
							'content_type_is_standard' => ($is_standard == TRUE) ? '1' : '0',
							'content_type_is_privileged' => ($is_privileged == TRUE) ? '1' : '0',
							'content_type_template' => $template,
							'content_type_base_url' => $base_url,
							'custom_field_group_id' => $custom_field_group_id
						);
						
		$this->db->insert('content_types', $insert_fields);
		
		$content_type_id = $this->db->insert_id();
		
		// if this content type isn't another admin module, we'll create a table for it
		// otherwise, we expect the developer to create it's own table in the module install
		if ($is_module == FALSE) {
			/*
				OLD DBFORGE METHOD
			
			// database functions
			$this->load->dbforge();
			
			// add ID, date, edit_date, admin rows
			$this->dbforge->add_field('`' . $system_name . '_id` INT(11) auto_increment PRIMARY KEY');
			$this->dbforge->add_field('`content_id` INT(11) NOT NULL');
			
			// create table
			$this->dbforge->create_table($system_name);
			
			// add the content_id index to VASTLY speed up queries
			$this->db->query('ALTER TABLE `' . $system_name . '` ADD INDEX ( `content_id` )');
			*/
			
			/*
				We can't use dbforge anymore as it doesn't let us specify a table-type, 
				which we need to be able to support FULLTEXT queries.
			*/
			$prefix = $this->db->dbprefix;
			
			$sql ="CREATE TABLE `{$prefix}{$system_name}` (
				`{$system_name}_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
				`content_id` INT(11) NOT NULL,
				INDEX ( `content_id` )
			) ENGINE = MYISAM";
			
			$this->db->query($sql);
		}
		
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return $content_type_id;
	}
	
	/**
	* Update Content Type
	*
	* Updates a content type
	*
	* @param int $content_type_id
	* @param string $name
	* @param boolean $is_standard Include Title, URL Path, and Topic dropdown? (default: FALSE)
	* @param boolean $is_privileged Include Restrict Access to Member Group(s) Dropdown? (default: FALSE)
	* @param string $template The filename of the template in the theme directory to use for output (default: content.thtml)
	* @param string $base_url If this will be managed with the standard content module, we can pre-populate the URL string with this base_url. (default: '')
	*
	* @return void
	*/
	function update_content_type ($content_type_id, $name, $is_standard = TRUE, $is_privileged = FALSE, $template = 'content.thtml', $base_url) {
		// prep base url
		$base_url = rtrim($base_url, '/');
		$base_url = (!empty($base_url)) ? $base_url . '/' : '';
		
		$update_fields = array(
							'content_type_friendly_name' => $name,
							'content_type_is_standard' => ($is_standard == TRUE) ? '1' : '0',
							'content_type_is_privileged' => ($is_privileged == TRUE) ? '1' : '0',
							'content_type_template' => $template,
							'content_type_base_url' => $base_url
						);
						
		$this->db->update('content_types', $update_fields, array('content_type_id' => $content_type_id));
		
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return;
	}
	
	/**
	* Delete Content Type
	*
	* @param int $content_type_id
	*
	* @return boolean TRUE
	*/
	function delete_content_type ($content_type_id) {
		$type = $this->get_content_type($content_type_id);
		
		if (empty($type)) {
			return FALSE;
		}
		
		// delete custom field group
		$this->load->model('custom_fields_model');
		$this->custom_fields_model->delete_group($type['custom_field_group_id'], $type['system_name']);
		
		// delete content from content database
		$this->load->model('publish/content_model');
		$content = $this->content_model->get_contents(array('type' => $type['id']));
		foreach ((array)$content as $item) {
			$this->content_model->delete_content($item['id']);
		}
		
		// delete table
		$this->load->dbforge();
		$this->dbforge->drop_table($type['system_name']);
		
		// delete content type
		$this->db->delete('content_types',array('content_type_id' => $type['id']));
		
		// clear cache
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
		
		return TRUE;
	}
	
	/**
	* Build Search Index
	*
	* Builds the FULLTEXT search key for a content table, if it's standard content
	*
	* @param int $content_type_id
	*
	* @return boolean TRUE
	*/
	function build_search_index ($content_type_id) {
		$type = $this->get_content_type($content_type_id);
		
		if (empty($type)) {
			die(show_error('Error re-building search index for content type id #' . $content_type_id . '.  Content type does not exist.'));
		}
		elseif ($type['is_standard'] == FALSE) {
			// non-standard content types don't get automatic search indeces like this
			return FALSE;
		}
		
		$this->load->model('custom_fields_model');
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => $type['custom_field_group_id']));
		
		// load fieldtype library for below dbcolumn checks
		$this->CI->load->library('custom_fields/fieldtype');
		
		$search_fields = array();
		$fields = 1;
		foreach ($custom_fields as $field) {
			if ($fields < 16) {		
				// we will only index fields that are VARCHAR, or TEXT
				$this->CI->fieldtype->load_type($field['type']);
				$db_column = $this->CI->fieldtype->$field['type']->db_column;
			
				if (strpos($db_column,'TEXT') !== FALSE or strpos($db_column,'VARCHAR') !== FALSE) {
					$search_fields[] = '`' . $field['name'] . '`';
					$fields++;
				}
			}
		}
		
		$search_fields = implode(', ', $search_fields);
		
		// we'll only drop the key if it already exists
		$result = $this->db->query('SHOW INDEX FROM `' . $type['system_name'] . '`');
		
		$key_exists = FALSE;
		
		foreach ($result->result_array() as $key) {
			if ($key['Key_name'] == 'search') {
				$key_exists = TRUE;
			}
		}
		
		if ($key_exists == TRUE) {
			$this->db->query('ALTER TABLE `' . $type['system_name'] . '` DROP index `search`');
		}
		
		if (!empty($search_fields)) {
			$this->db->query('CREATE FULLTEXT INDEX `search` ON `' . $type['system_name'] . '` (' . $search_fields . ');');
		}
		
		return TRUE;
	}
	
	/**
	* Get Content Type
	*
	* @param int $content_type_id
	*
	* @return array $content_type
	*/
	function get_content_type ($id) {
		$cache_key = 'get_content_type' . $id;
		
		if (isset($this->CI->cache) and $return = $this->cache->file->get($cache_key)) {
			return $return;
		}
		
		$type = $this->get_content_types(array('id' => $id));
		
		if (empty($type)) {
			$return = FALSE;
		}
		else {
			$return = $type[0];
		}
		
		if (isset($this->CI->cache)) {
			$this->CI->cache->file->save($cache_key, $return, (30*60));
		}
		
		return $return;
	}
	
	/**
	* Get Content Types
	*
	* @param int $filters['id']
	* @param boolean $filters['is_standard']
	* @param boolean $filters['is_module']
	*
	* @return array
	*/
	function get_content_types ($filters = array()) {
		$cache_key = 'get_content_types' . md5(serialize($filters));
		
		if (isset($this->CI->cache) and $return = $this->cache->file->get($cache_key)) {
			return ($return == 'empty_cache') ? FALSE : $return;
		}
	
		if (isset($filters['id'])) {
			$this->db->where('content_type_id',$filters['id']);
		}
		if (isset($filters['is_standard'])) {
			$filters['is_standard'] = (!empty($filters['is_standard'])) ? TRUE : FALSE;
			$this->db->where('content_type_is_standard',$filters['is_standard']);
		}
		if (isset($filters['system_name'])) {
			$this->db->where('content_type_system_name',$filters['system_name']);
		}
		if (isset($filters['is_module'])) {
			$filters['is_module'] = (!empty($filters['is_module'])) ? TRUE : FALSE;
			$this->db->where('content_type_is_module',$filters['is_module']);
		}
	
		$this->db->order_by('content_type_friendly_name');
		$result = $this->db->get('content_types');
		
		if ($result->num_rows() == 0) {
			if (isset($this->CI->cache)) {
				$this->cache->file->save($cache_key, 'empty_cache', (30*60));
			}
			
			return FALSE;
		}
		
		// load inflection library for singular names
		$this->load->library('inflect');
		
		$types = array();
		foreach ($result->result_array() as $row) {
			$types[] = array(
						'id' => $row['content_type_id'],
						'name' => $row['content_type_friendly_name'],
						'singular_name' => $this->inflect->singularize($row['content_type_friendly_name']),
						'system_name' => $row['content_type_system_name'],
						'is_privileged' => ($row['content_type_is_privileged'] == '1') ? TRUE : FALSE,
						'is_standard' => ($row['content_type_is_standard'] == '1') ? TRUE : FALSE,
						'template' => $row['content_type_template'],
						'custom_field_group_id' => $row['custom_field_group_id'],
						'base_url' => $row['content_type_base_url']
					);
		}
		
		if (isset($this->CI->cache)) {
			$this->cache->file->save($cache_key, $types, (30*60));
		}
		
		return $types;
	}
}