<?php

class Navigation {
	var $navigation; // stores the relational tree
	var $items; // stores details for each item
	var $item_count; // stores the increasing unique ID for each item
	var $active_parent; // which parent nav item is active?
	
	function __construct () {
		$this->item_count = 0;
	}
	
	function parent_link ($system_name, $name) {
		$this->navigation[$system_name] = array();
		
		$this->items[$system_name] = array(
									'system_name' => $system_name,
									'name' => $name
								);
	}
	
	function child_link ($parent, $weight, $name, $link) {
		$item_id = $this->take_id();
		
		$this->navigation[$parent][$weight] = $item_id;
		
		$this->items[$item_id] = array(
										'name' => $name,
										'link' => $link
									);
	}
	
	function parent_active ($system_name) {
		$this->active_parent = $system_name;
	}
	
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
				
				$return .= '<ul class="children" style="display:none">';
				foreach ($this->navigation[$parent_id] as $child_weight => $child_id) {
					$return .= '<li><a rel="' . $child_weight . '" href="' . $this->items[$child_id]['link'] . '">' . $this->items[$child_id]['name'] . '</a></li>';
				}
				
				$return .= '</ul>';
			}
			
			$return .= '</li>';
		}
		
		return $return;
	}
	
	function take_id () {
		$this->item_count++;
		
		return $this->item_count;
	}
}