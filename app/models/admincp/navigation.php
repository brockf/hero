<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Navigation Model 
*
* Dynamically generates the navigation, pagetitle, and sidebar elements of the page
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class Navigation extends CI_Model {
	var $elements;
	var $children;
	var $pagetitle;
	var $sidebar_buttons;
	var $sidebar_notes;

    function Navigation() {
        parent::CI_Model();
    }
    
    /**
    * Add Element
    *
    * Adds an element to the navigation menu
    *
    * @param string $link The link to the page.  If not relative to the site's base_url, set $external to FALSE
    * @param string $name The text to display for the link
    * @param string $parent The link (as passed previously) of the child's parent element.  Optional.
    * @param boolean $external Set to TRUE to keep the link from being relative to the base_url
    *
    * @return boolean True upon success
    */
    function Add ($link, $name, $parent = false, $external = false) {
    	if ($parent == false) {
	    	$this->elements[] = array(
	    						'link' => $link,
	    						'name' => $name,
	    						'external' => $external
	    				);
	    }
	    else {
	    	$this->children[$parent][] = array(
	    						'link' => $link,
	    						'name' => $name,
	    						'external' => $external
	    				);	
	    }
	    
	    return true;
    }
    
    /**
    * Output Navigation
    *
    * Outputs the navigation elements in a UL list
    *
    * @return string Formatted HTML list of navigation
    */
    function Output () {
    	$CI =& get_instance();
    	
    	$return = '';
    	
    	while (list(,$link) = each($this->elements)) {
    		$classes = array();
    		if (strstr($CI->router->fetch_class() . '/',$link['link'])) {
    			$classes[] = 'active';
    		}
    		
    		if (isset($this->children[$link['link']])) {
    			$classes[] = 'parent';
    		}
    		
    		$displaylink = ($link['external'] == false) ? site_url($link['link']) : $link['link'];
    		
    		$return .= '<li ' . $this->ArrayToClass($classes) . '><a href="' . $displaylink . '">' . $link['name'] . '</a>';
    		
    		if (isset($this->children[$link['link']])) {
    			$return .= '<ul class="children">';
    			
    			while (list(,$child) = each($this->children[$link['link']])) {
    				$displaylink = ($child['external'] == false) ? site_url($child['link']) : $child['link'];
    				$return .= '<li><a href="' . $displaylink . '">' . $child['name'] . '</a></li>';
    			}
    			
    			$return .= '<div style="clear:both"></div></ul>';
    		}
    		
    		$return .= '</li>';
    	}
    	
    	return $return;
    }
    
    /**
    * Page Title
    *
    * Set or retrieve the current PageTitle (<title> tag)
    *
    * @param string/boolean $set If set, this will set the page title.
    *
    * @return string The current pagetitle
    */
    function PageTitle ($set = false) {
    	if (!$set) {
    		return $this->pagetitle;
    	}
    	else {
    		$this->pagetitle = $set . ' | ' . $this->config->item('server_name');
    		return $this->pagetitle;
    	}
    }
    
    /**
    * Sidebar Button
    *
    * Creates a new sidebar button
    *
    * @param string $text The text of the button
    * @param string $link The link of the button.  If external, set $external to false
    * @param string $class The class of the anchor element.  Optional.  Defaults to 'button'.
    * @param boolean $external Set to TRUE to make link external.
    *
    * @return boolean TRUE upon success.
    */
    function SidebarButton ($text, $link, $class = 'button', $external = false) {
    	$this->sidebar_buttons[] = array(
    								'text' => $text,
    								'link' => ($external == false) ? site_url($link) : $link,
    								'class' => $class
    								);
    	return true;
    }
    
    /**
    * Sidebar Note
    *
    * Creates a new sidebar note
    *
    * @param string $text The text of the note
    * @param string $class The class of the paragraph element.  Optional.  Defaults to 'note'.
    *
    * @return boolean TRUE upon success.
    */
    function SidebarNote ($text, $class = 'note') {
    	$this->sidebar_notes[] = array(
    								'text' => $text,
    								'class' => $class
    								);
    								
    	return true;
    }
    
    /**
    * Get Sidebar
    *
    * Returns a formatted sidebar element
    *
    * @return string Formatted HTML of sidebar with added elements
    */
    function GetSidebar () {
    	$return = '';
    	
    	if (is_array($this->sidebar_buttons)) {
	    	foreach ($this->sidebar_buttons as $button) {
	    		$return .= '<p class="button"><a class="' . $button['class'] . '" href="' . $button['link'] . '">' . $button['text'] . '</a></p>';
	    	}
	    }
    	
    	if (is_array($this->sidebar_notes)) {
	    	foreach ($this->sidebar_notes as $note) {
	    		$return .= '<p class="' . $note['class'] . '">' . $note['text'] . '</p>';
	    	}
	    }	
    	
    	return $return;
    }
    
    /**
    * Array to Class
    *
    * Converts an array of values to a class string
    *
    * @param array $array The array of elements to convert to a class string
    *
    * @return string The string of class elements for use in an HTML element.
    */
    function ArrayToClass ($array) {
    	$classes = implode(' ',$array);
    	
    	if (!empty($classes)) {
    		return 'class="' . $classes . '"';
    	}
    }
}