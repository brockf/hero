<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Topic Model 
*
* Contains all the methods used to create, update, and delete topics.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Topic_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* New Topic
	*
	* @param string $name
	* @param string $description (default: '')
	* @param int $parent (default: 0)
	*
	* @return int $topic_id
	*/
	function new_topic ($name, $description = '', $parent = 0) {
		$insert_fields = array(
								'topic_name' => $name,
								'topic_description' => $description,
								'topic_parent_id' => $parent,
								'topic_deleted' => '0'
							);
							
		$this->db->insert('topics',$insert_fields);
		
		$topic_id = $this->db->insert_id();
		
		$CI =& get_instance();
		$CI->app_hooks->trigger('new_topic', $topic_id);
		
		return $topic_id;
	}
	
	/**
	* Update Topic
	*
	* @param int $topic_id
	* @param string $name
	* @param string $description (default: '')
	* @param int $parent (default: 0)
	*
	* @return boolean TRUE
	*/
	function update_topic ($topic_id, $name, $description = '', $parent = 0) {
		$update_fields = array(
								'topic_name' => $name,
								'topic_description' => $description,
								'topic_parent_id' => $parent
							);
							
		$this->db->update('topics',$update_fields,array('topic_id' => $topic_id));
		
		$CI =& get_instance();
		$CI->app_hooks->trigger('update_topic', $topic_id);
		
		return TRUE;
	}
	
	/**
	* Delete Topic
	*
	* @param int $topic_id
	*/
	function delete_topic ($topic_id) {
		$this->db->update('topics',array('topic_deleted' => '1'), array('topic_id' => $topic_id));
		
		$CI =& get_instance();
		
		$CI->app_hooks->trigger('delete_topic', $topic_id);
		
		// delete all children, too
		$result = $this->get_topics(array());
		
		if (!$result) {
			// no topics
			return TRUE;
		}
		
		$topics = array();
		
		foreach ($result as $topic) {
			$topics[$topic['parent']][$topic['id']] = $topic['name'];
		}
		
		if (isset($topics[$topic_id])) {
			// has children
			foreach ($topics[$topic_id] as $child_id => $child) {
				$this->db->update('topics',array('topic_deleted' => '1'), array('topic_id' => $child_id));
				
				$CI->app_hooks->trigger('delete_topic', $child_id);
			}
		}
		
		return TRUE;
	}
	
	/**
	* Get Tiered Topics
	*
	* Gets an array of all topics, tiered nicely in a hierarchical structure
	* It's a big ugly function, though, so call sparingly.  Not a multidimensional array.
	*
	* returns: Shoes
	*		   Shoes > Adidas
	*          Shoes > Adidas > Crosstrainers
	*          	
	* @uses make_tiers()
	* @param array $filters equivalent to get_topics()
	*
	* @return array Topics
	*/
	function get_tiered_topics ($filters = array()) {
		// no limit or offset will be passed
		// it doesn't work when tiering
		if (isset($filters['limit'])) {
			unset($filters['limit']);
		}
		
		if (isset($filters['offset'])) {
			unset($filters['offset']);
		}
	
		$result = $this->get_topics($filters);
		
		if (!$result) {
			// no topics
			
			return array();
		}
		
		$topics = array();
		
		foreach ($result as $topic) {
			$topics[$topic['parent']][$topic['id']] = $topic['name'];
		}
		
		if (!isset($topics[0])) {
			// no topics at the parent node
			return array();
		}
		
		$tiers = array(); 
		
		// start at parent 0 and go from there
		foreach ($topics[0] as $id => $name) {
			$tiers[$id] = array('id' => $id, 'name' => $name);
			
			if (isset($topics[$id]) and is_array($topics[$id])) {
				foreach ($topics[$id] as $id_2 => $name_2) {
					$tiers[$id_2] = array('id' => $id_2, 'name' => $name . ' > ' . $name_2);
					
					if (isset($topics[$id_2]) and is_array($topics[$id_2])) {
						foreach ($topics[$id_2] as $id_3 => $name_3) {
							$tiers[$id_3] = array('id' => $id_3, 'name' => $name . ' > ' . $name_2 . ' > ' . $name_3);
							
							if (isset($topics[$id_3]) and is_array($topics[$id_3])) {
								foreach ($topics[$id_3] as $id_4 => $name_4) {
									$tiers[$id_4] = array('id' => $id_4, 'name' => $name . ' > ' . $name_2 . ' > ' . $name_3 . ' > ' . $name_4);
									
									if (isset($topics[$id_4]) and is_array($topics[$id_4])) {
										foreach ($topics[$id_4] as $id_5 => $name_5) {
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
 	 * NO LONGER USED.  HAS BUGS.
	 *
	 * make the actual tiers for above
	 * recursive method	 
	 * 
	 * @param array ($terms)
	 * @param array ($tiers)
	 * @param int
	 * @param string (running name)
	 * @return array (finished tiers)	 
	 */	 	 	  	 	 	
	function make_tiers($terms, &$tiers, $index=0, $names='')
	{
		if (is_array($terms[$index]))
		{
			while(list($id, $name) = each($terms[$index]))
			{	
				if ($index == 0)
				{
					$tiers[$id] = array('id'=>$id, 'name'=>$name);
					$names = $name . ' > ';
				}
				else
				{
					$tiers[$id] = array('id'=>$id, 'name'=>$names . $name);
					$names = $names . $name . ' > ';
				}			
								
				if (isset($terms[$id]))
				{
					$this->make_tiers($terms, $tiers, $id, $names);
				}
			}
		}
		
		return $tiers;
	}
	
	/**
	* Get Topic
	*
	* @param int $topic_id
	*
	* @return array Array of data, else FALSE
	*/
	function get_topic($topic_id) {
		$topic = $this->get_topics(array('id' => $topic_id), TRUE);
		
		if (!empty($topic)) {
			return $topic[0];
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Get Topics
	*
	* @param int $filters['parent']
	* @param int $filters['id']
	* @param string $filters['name']
	* @param string $filters['sort'] Sort column
	* @param string $filters['sort_dir'] Sort direction
	* @param int $filters['limit'] Result limit
	* @param int $filters['offset'] Result offset
	*
	* @return array $topics
	*/
	function get_topics ($filters = array(), $any_status = FALSE) {
		if (isset($filters['parent'])) {
			$this->db->where('topic_parent_id',$filters['parent']);
		}
		if (isset($filters['id'])) {
			$this->db->where('topic_id',$filters['id']);
		}
		if (isset($filters['name'])) {
			$this->db->like('topic_name',$filters['name']);
		}
		
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'topic_name';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		if ($any_status == FALSE) {
			$this->db->where('topic_deleted','0');
		}
		$result = $this->db->get('topics');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		else {
			$topics = array();
			foreach ($result->result_array() as $topic) {
				$this_topic = array(
									'id' => $topic['topic_id'],
									'name' => $topic['topic_name'],
									'description' => $topic['topic_description'],
									'parent' => $topic['topic_parent_id']
									);
									
				$topics[] = $this_topic;
			}
			
			return $topics;
		}
	}
}