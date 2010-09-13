<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Events Model 
*
* Contains all the methods used to create, update, and delete events.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Events_model extends CI_Model
{
	function __construct()
	{
		parent::CI_Model();
	}
	
	/*
	* Create New Event
 	* @param string $title Event title
 	* @param string $url_path
 	* @param string $description Event description
 	* @param string $location Event location
 	* @param string $max_attendees Event maximum attendees 
 	* @param string $price Event price 
 	* @param string $start_date Event start date
 	* @param string $end_date Event end date
	* @param string Standard privileges array of member group ID's
 	*
 	* @return $feed_id
 	*/
	function new_event ($title, $url_path, $description, $location, $max_attendees, $price, $start_date, $end_date, $privileges = array()) {
		$this->load->helper('clean_string');
		$url_path = (empty($url_path)) ? clean_string($title) : clean_string($url_path);
		
		$this->load->model('link_model');
		$url_path = $this->link_model->get_unique_url_path($url_path);
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


	

	function get_events () {

	}
	

}