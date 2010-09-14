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
	*
 	* @param string $title Event title
 	* @param string $url_path
 	* @param string $description Event description
 	* @param string $location Event location
 	* @param string $max_attendees Event maximum attendees 
 	* @param string $price Event price 
 	* @param string $start_date Event start date
 	* @param string $end_date Event end date
	* @param string Standard privileges array of member group ID's
	* @param int $user The ID of the submitting user
 	*
 	* @return $feed_id
 	*/
	function new_event ($title, $url_path, $description, $location, $max_attendees, $price, $start_date, $end_date, $privileges = array(), $user) {
		$this->load->helper('clean_string');
		$url_path = (empty($url_path)) ? clean_string($title) : clean_string($url_path);

		if (!empty($title)) {
			$this->load->helper('clean_string');
			$url_path = (empty($url_path)) ? clean_string($title) : clean_string($url_path);
			
			// get a global link ID
			// make sure URL is unique
			$this->load->model('link_model');
			$url_path = $this->link_model->get_unique_url_path($url_path);
			
			$link_id = $this->link_model->new_link($url_path, FALSE, $title, 'Event', 'events', 'events', 'view');
		}
		
		
		$insert_fields = array(
							'link_id' => $link_id,
							'event_title' => $title,
							'event_url_path' => $url_path,
							'event_description' => $description,
							'event_location' => $location,
							'event_max_attendees' => $max_attendees,
							'event_price' => $price,
							'event_start_date' => $start_date,
							'event_end_date' => $end_date,
							'user_id' => $user,
							'event_privileges' => (is_array($privileges) and !in_array(0, $privileges)) ? serialize($privileges) : ''
							);
							
		$this->db->insert('events',$insert_fields);
		
		return $this->db->insert_id();
	}

	/*
	* Update Event
	*
	* @param string $event_id Event ID
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
 	* @return TRUE
 	*/
	function update_event ($event_id, $title, $url_path, $description, $location, $max_attendees, $price, $start_date, $end_date, $privileges = array()) {
		$event = $this->get_event($event_id);
		
		$this->load->model('link_model');
		if ($url_path != $event['url_path']) {
			$this->load->helper('clean_string');
			$url_path = clean_string($url_path);
			
			$url_path = $this->link_model->get_unique_url_path($url_path);
			$this->link_model->update_url($event['link_id'], $url_path);
		}
		$this->link_model->update_title($event['link_id'], $title);
	
		$update_fields = array(
							'event_title' => $title,
							'event_url_path' => $url_path,
							'event_description' => $description,
							'event_location' => $location,
							'event_max_attendees' => $max_attendees,
							'event_price' => $price,
							'event_start_date' => $start_date,
							'event_end_date' => $end_date,
							'event_privileges' => (is_array($privileges) and !in_array(0, $privileges)) ? serialize($privileges) : ''
							);
							
		$this->db->update('events',$update_fields,array('event_id' => $event_id));
		
		return TRUE;
	}
	
	/*
	* Delete Event
	*
	* @param int $event_id
	*
	* @return boolean TRUE
	*/
	function delete_event ($event_id) {
		$event = $this->get_event($event_id);
	
		$this->db->delete('events',array('event_id' => $event_id));
		
		$this->load->model('link_model');
		$this->link_model->delete_link($event['link_id']);
		
		return TRUE;
	}
			
	/*
	* Get Event
	*
	* @param int $event_id
	*
	* @return array
	*/
	function get_event ($event_id) {
		$event = $this->get_events(array('id' => $event_id));
		
		if (empty($event)) {
			return FALSE;
		}

		return $event[0];
	}
	
	/*
	* Get Events
	* @param int $filters['id']
	* @param string $filters['title']
	*
	*/
	function get_events ($filters = array()) {
		if (isset($filters['id'])) {
			$this->db->where('event_id',$filters['id']);
		}
		if (isset($filters['title'])) {
			$this->db->like('event_title',$filters['title']);
		}
		
		$this->db->order_by('event_title');
		$this->db->join('links','links.link_id = events.link_id','left');
		$result = $this->db->get('events');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$events = array();
		foreach ($result->result_array() as $row) {
			$events[] = array(
						'id' => $row['event_id'],
						'link_id' => $row['link_id'],
						'title' => $row['event_title'],
						'url_path' => $row['event_url_path'],
						'description' => $row['event_description'],
						'location' => $row['event_location'],
						'max_attendees' => $row['event_max_attendees'],
						'price' => $row['event_price'],
						'start_date' => $row['event_start_date'],
						'end_date' => $row['event_end_date'],
						'privileges' => (!empty($content['event_privileges'])) ? unserialize($content['event_privileges']) : FALSE
					);
		}
		
		return $events;
	}
	

}