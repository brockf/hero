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
		$link_id = $this->link_model->new_link($url_path, FALSE, $title, 'Event', 'events', 'events', 'view');
		
		$insert_fields = array(
							'link_id' => $link_id,
							'event_title' => $title,
							'event_description' => $description,
							'event_location' => $location,
							'event_max_attendees' => $max_attendees,
							'event_price' => $price,
							'event_start_date' => $start_date,
							'event_end_date' => $end_date,
							'event_privileges' => (is_array($privileges) and !in_array(0, $privileges)) ? serialize($privileges) : ''
							);
							
		$this->db->insert('events',$insert_fields);
		
		return $this->db->insert_id();
	}

	function get_events () {

	}
	

}