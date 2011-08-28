<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* RSS Model 
*
* Contains all the methods used to create, update, and delete RSS feeds.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Rss_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Create New RSS Feed
	*
	* @param int $content_type_id
 	* @param string $title Feed title
 	* @param string $url_path
 	* @param string $description Feed description
 	* @param array $filter_author The user ID(s) to filter by (default: array())
 	* @param array $filter_topic The topic ID(s) to filter by (default: array())
 	* @param string $summary_field The column name to use for the summary (default: FALSE)
 	* @param string $sort_field The column name to sort by (default: '')
 	* @param string $sort_dir Sort direction (default: '')
 	* @param string $template The template file to use for output (default: rss_feed.txml)
 	*
 	* @return int $feed_id
 	*/
	function new_feed ($content_type_id, $title, $url_path, $description, $filter_author = array(), $filter_topic = array(), $summary_field = FALSE, $sort_field = '', $sort_dir = '', $template = 'rss_feed.txml') {
		$this->load->helper('clean_string');
		$url_path = (empty($url_path)) ? clean_string($title) : $url_path;
		
		$this->load->model('link_model');
		$link_id = $this->link_model->new_link($url_path, FALSE, $title, 'RSS Feed', 'rss', 'feed', 'view');
		
		$insert_fields = array(
							'link_id' => $link_id,
							'content_type_id' => $content_type_id,
							'rss_title' => $title,
							'rss_description' => $description,
							'rss_filter_author' => (is_array($filter_author) and !empty($filter_author)) ? serialize($filter_author) : '',
							'rss_filter_topic' => (is_array($filter_topic) and !empty($filter_topic)) ? serialize($filter_topic) : '',
							'rss_summary_field' => (!empty($summary_field)) ? $summary_field : '',
							'rss_sort_field' => (!empty($sort_field)) ? $sort_field : '',
							'rss_sort_dir' => (!empty($sort_dir)) ? $sort_dir : '',
							'rss_template' => $template
							);
							
		$this->db->insert('rss_feeds',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Update RSS Feed
	*
	* @param int $feed_id
	* @param int $content_type_id
 	* @param string $title Feed title
 	* @param string $url_path
 	* @param string $description Feed description
 	* @param array $filter_author The user ID(s) to filter by (default: array())
 	* @param array $filter_topic The topic ID(s) to filter by (default: array())
 	* @param string $summary_field The column name to use for the summary (default: FALSE)
 	* @param string $sort_field The column name to sort by (default: '')
 	* @param string $sort_dir Sort direction (default: '')
 	* @param string $template The template file to use for output (default: rss_feed.txml)
 	*
 	* @return void 
 	*/
	function update_feed ($feed_id, $content_type_id, $title, $url_path, $description, $filter_author = array(), $filter_topic = array(), $summary_field = FALSE, $sort_field = '', $sort_dir = '', $template = 'rss_feed.txml') {
		$feed = $this->get_feed($feed_id);
		
		$this->load->model('link_model');
		
		if (empty($url_path)) {
			$this->load->helper('clean_string');
			$url_path = clean_string($title);
		}
		
		if ($url_path != $feed['url_path']) {
			$this->load->helper('clean_string');
			$url_path = clean_string($url_path);
			
			$url_path = $this->link_model->prep_url_path($url_path);
			$url_path = $this->link_model->get_unique_url_path($url_path);
			$this->link_model->update_url($feed['link_id'], $url_path);
		}
		$this->link_model->update_title($feed['link_id'], $title);
	
		$update_fields = array(
							'content_type_id' => $content_type_id,
							'rss_title' => $title,
							'rss_description' => $description,
							'rss_filter_author' => (is_array($filter_author) and !empty($filter_author)) ? serialize($filter_author) : '',
							'rss_filter_topic' => (is_array($filter_topic) and !empty($filter_topic)) ? serialize($filter_topic) : '',
							'rss_summary_field' => (!empty($summary_field)) ? $summary_field : '',
							'rss_sort_field' => (!empty($sort_field)) ? $sort_field : '',
							'rss_sort_dir' => (!empty($sort_dir)) ? $sort_dir : '',
							'rss_template' => $template
							);
							
		$this->db->update('rss_feeds',$update_fields,array('rss_id' => $feed_id));
		
		return;
	}
	
	/**
	* Delete RSS Feed
	*
	* @param int $feed_id
	*
	* @return boolean TRUE
	*/
	function delete_feed ($feed_id) {
		$rss = $this->get_feed($feed_id);
	
		$this->db->delete('rss_feeds',array('rss_id' => $feed_id));
		
		$this->load->model('link_model');
		$this->link_model->delete_link($rss['link_id']);
		
		return TRUE;
	}
	
	/**
	* Get Feed ID
	*
	* Returns feed ID from a URL_path
	*
	* @param string $url_path
	* 
	* @return int The feed ID, or FALSE
	*/
	function get_feed_id($url_path) {
		$this->db->select('rss_id');
		$this->db->where('link_url_path',$url_path);
		$this->db->join('links','rss_feeds.link_id = links.link_id','inner');
		$result = $this->db->get('rss_feeds');
		
		if ($result->num_rows() == FALSE) {
			return FALSE;
		}
		
		$feed = $result->row_array();
		
		return $feed['rss_id'];
	}
	
	/**
	* Get RSS Feed
	*
	* @param int $feed_id
	*
	* @return array
	*/
	function get_feed ($feed_id) {
		$feed = $this->get_feeds(array('id' => $feed_id));
		
		if (empty($feed)) {
			return FALSE;
		}
		
		return $feed[0];
	}
	
	/**
	* Get Feed Content
	*
	* Retrieves content for a specific feed
	*
	* @param int $feed_id
	*
	* @return array Array of content else FALSE
	*/
	function get_feed_content ($feed_id) {
		$feed = $this->get_feed($feed_id);
		
		if (empty($feed)) {
			return FALSE;
		}
		
		// prepare filters for get_contents
		$filters = array();
		$filters['type'] = $feed['type'];
		
		// filter by author?
		if ($feed['filter_authors'] !== FALSE) {
			$filters['author'] = $feed['filter_authors'];
		}
		
		// filter by topic?
		if ($feed['filter_topics'] !== FALSE) {
			$filters['topic'] = $feed['filter_topics'];
		}
		
		if (!empty($feed['sort_field'])) {
			$filters['sort'] = $feed['sort_field'];
			$filters['sort_dir'] = $feed['sort_dir'];
		}
		
		$filters['limit'] = setting('feed_items_count');
		
		// get content
		$CI =& get_instance();
		$CI->load->model('publish/content_model');
		$CI->load->helper('shorten');
		$contents = $CI->content_model->get_contents($filters);
		
		if (empty($contents)) {
			return FALSE;
		}
		
		// we may need to prep a summary field
		if (!empty($feed['summary_field'])) {
			foreach ($contents as $key => $content) {
				// prep summary
				$contents[$key]['summary'] = shorten($content[$feed['summary_field']], 400, TRUE);
			}
		}
		
		return $contents;
	}
	
	/**
	* Get RSS Feeds
	*
	* @param int $filters['id']
	* @param int $filters['type']
	* @param string $filters['title']
	*
	* @return array
	*/
	function get_feeds ($filters = array()) {
		if (isset($filters['id'])) {
			$this->db->where('rss_id',$filters['id']);
		}
		if (isset($filters['type'])) {
			$this->db->where('content_types.content_type_id',$filters['type']);
		}
		if (isset($filters['title'])) {
			$this->db->like('rss_title',$filters['title']);
		}
	
		$this->db->order_by('rss_title');
		$this->db->join('content_types','content_types.content_type_id = rss_feeds.content_type_id','left');
		$this->db->join('links','links.link_id = rss_feeds.link_id','left');
		$result = $this->db->get('rss_feeds');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$feeds = array();
		foreach ($result->result_array() as $row) {
			$feeds[] = array(
						'id' => $row['rss_id'],
						'link_id' => $row['link_id'],
						'title' => $row['rss_title'],
						'description' => $row['rss_description'],
						'filter_authors' => (!empty($row['rss_filter_author'])) ? unserialize($row['rss_filter_author']) : FALSE,
						'filter_topics' => (!empty($row['rss_filter_topic'])) ? unserialize($row['rss_filter_topic']) : FALSE,
						'type' => $row['content_type_id'],
						'type_name' => $row['content_type_friendly_name'],
						'summary_field' => (!empty($row['rss_summary_field'])) ? $row['rss_summary_field'] : FALSE,
						'url' => site_url($row['link_url_path']),
						'url_path' => $row['link_url_path'],
						'template' => $row['rss_template'],
						'sort_field' => (!empty($row['rss_sort_field'])) ? $row['rss_sort_field'] : FALSE,
						'sort_dir' => (!empty($row['rss_sort_dir'])) ? $row['rss_sort_dir'] : FALSE
					);
		}
		
		return $feeds;
	}
}