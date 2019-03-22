<?php

/**
* Admin Navigation Library
*
* This class generates the navigation throughout the control panel.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/
class Admin_navigation {
	var $navigation; // stores the relational tree
	var $items; // stores details for each item
	var $item_count; // stores the increasing unique ID for each item
	var $active_parent; // which parent nav item is active?
	var $module_links;
	
	function __construct () {
		$this->item_count = 0;
	}
	
	/**
	* Parent Link
	*
	* @param string $system_name
	* @param string $name
	*
	* @return void 
	*/
	function parent_link ($system_name, $name) {
		$this->navigation[$system_name] = array();
		
		$this->items[$system_name] = array(
									'system_name' => $system_name,
									'name' => $name
								);
	}
	
	/**
	* Child Link
	*
	* @param string $parent
	* @param int $weight
	* @param string $name
	* @param string $link
	*
	* @return void 
	*/
	function child_link ($parent, $weight, $name, $link) {
		$item_id = $this->take_id();
		
		$this->navigation[$parent][$weight] = $item_id;
		
		$this->items[$item_id] = array(
										'name' => $name,
										'link' => $link
									);
	}
	
	/**
	* Module Link
	*
	* @param string $name
	* @param string $link
	*
	* @return void 
	*/
	function module_link ($name, $link) {
		$this->module_links[] = array(
									'name' => $name,
									'link' => $link
								);
	}
	
	/**
	* Delete Child
	*
	* Delete a child link from the navigation.  Useful to override an existing navigation link.
	*
	* @param string $name
	*
	* @return boolean
	*/
	function delete_child ($name) {
		foreach ($this->items as $key => $item) {
			if ($item['name'] == $name) {
				unset($this->items[$key]);
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	* Get Module Links
	*
	* @return string HTML 
	*/
	function get_module_links () {
		if (empty($this->module_links)) {
			return '';
		}
	
		$return = '<ul class="module_links">';
		
		foreach ($this->module_links as $link) {
			$return .= '<li><a href="' . $link['link'] . '">' . $link['name'] . '</a></li>';
		}
		
		$return .= '</ul>';
		
		return $return;
	}
	
	/**
	* Set Parent Active
	*
	* @param string $system_name
	*
	* @return void 
	*/
	function parent_active ($system_name) {
		$this->active_parent = $system_name;
	}
	
	/**
	* Display Nav
	*
	* @return string HTML
	*/
	function display () {
		// sort by weight
		foreach ($this->navigation as $parent_id => $children) {
			ksort($this->navigation[$parent_id]);
		}
		reset($this->navigation);

		// display
		
		$return = '';
		foreach ($this->navigation as $parent_id => $children) {
			// if there's only one child item, it becomes the parent link, else we show children
			if (count($this->navigation[$parent_id]) == 1) {
				foreach ($this->navigation[$parent_id] as $only_child) {
					$parent_link = $this->items[$only_child]['link'];
				}
			}
			else {
				$parent_link = '#';
			}
			
			$active_class = ($this->active_parent == $this->items[$parent_id]['system_name']) ? ' active': '';
		
			$return .= '<li class="' . $this->items[$parent_id]['system_name'] . '"><a class="parent' . $active_class .'" href="' . $parent_link . '">' . $this->items[$parent_id]['name'] . '</a>';
			
			// are their children?
			// i.e., are there more than 1 child item?
			if (!empty($this->navigation[$parent_id]) and count($this->navigation[$parent_id]) > 1) {
				// yes
				
				$display = ($this->active_parent == $this->items[$parent_id]['system_name']) ? '' : ' style="display:none"';
								
				$return .= '<ul class="children"' . $display . '>';
				foreach ($this->navigation[$parent_id] as $child_weight => $child_id) {
					// this conditional protects us against child links which were post-humously deleted
					if (isset($this->items[$child_id]['link'])) {
						$return .= '<li><a rel="' . $child_weight . '" href="' . $this->items[$child_id]['link'] . '">' . $this->items[$child_id]['name'] . '</a></li>';
					}
				}
				
				$return .= '</ul>';
			}
			
			$return .= '</li>';
		}
		
		return $return;
	}
	
	/**
	* Clear Module Links
	*
	* @return void 
	*/
	function clear_module_links () {
		$this->module_links = array();
	}
	
	/**
	* Increment the ID
	*
	* @return void 
	*/
	function take_id () {
		$this->item_count++;
		
		return $this->item_count;
	}
}