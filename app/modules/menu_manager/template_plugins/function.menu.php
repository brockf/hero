<?php

/**
* Menu Template Function
*
* Displays a menu in CSS-ready HTML
*
* @param string $name The menu name
* @param string $class CSS class(es) for the menu <ul> element
* @param string $id Element ID for the menu <ul> element
* @param string $show_sub_menus Either "no|yes|active".  "active" only displays the sub menu if it's parent menu
*								item is active, or a sibling is active
*
* @return string $menu
*/
function smarty_function_menu ($params, $smarty) {
	// check params
	if (!isset($params['name'])) {
		return 'The {menu} template plugin requires a "name" parameter to identify the menu to load.';
	}

	$smarty->CI->load->model('menu_manager/menu_model');
	$menu = $smarty->CI->menu_model->get_menu_by_name($params['name']);
	
	if (empty($menu)) {
		return 'The "name" parameter you passed to {menu} (' . $params['name'] . ') is not associated with a menu.  Menu load failed.';
	}
	
	// set default parameter for show_sub_menus
	if (!isset($params['show_sub_menus'])) {
		$params['show_sub_menus'] = 'yes';
	}
	
	// we have the menu
	// get root level menu items
	$links = $smarty->CI->menu_model->get_links(array('menu' => $menu['id'], 'parent' => '0'));
	
	if (empty($links)) {
		return 'There are no links in this menu (' . $menu['name'] . ').  Go to your Menu Manager in the control panel and add more links.';
	}
	
	// we'll store menu_items here with main key "id" and keys "text", "url", "active", "class"
	$menu_items = array();
	$menu_children = array();
	
	// parse parent links, which will in turn call child links if necessary
	parse_links($menu_items, $menu_children, $links, $menu, $smarty, $params);
	
	// sort through menu items and display the menu
	$params['class'] = (isset($params['class'])) ? $params['class'] : '';
	$params['id'] = (isset($params['id'])) ? $params['id'] : '';
	
	$return = '<ul class="' . $params['class'] . '" id="' . $params['id'] . '">';
	
	foreach ($menu_items as $id => $item) {
		// do we have children?
		$children_items = array();
		
		if ($item['is_child'] == FALSE) {
			if (isset($menu_children[$id])) {
				foreach ($menu_children[$id] as $key) {
					$item_child = $menu_items[$key];
					
					if (!empty($menu_items[$key])) {
						// set parent active if it's child is active
						if ($item_child['active'] == TRUE) {
							$item['active'] = TRUE;
						}
						
						$children_items[] = li_html($item_child);
					}
				}
			}
		}
		
		if ($item['is_child'] == FALSE) {
			$classes = (!empty($children_items)) ? array('parent') : array();
			
			$return .= li_html($item, $children_items, $classes);
		}
	}
	
	$return .= '</ul>';
	
	return $return;
}

function li_html ($item, $children_items = FALSE, $classes = array()) {
	if (!empty($item['class'])) {
		if (strpos($item['class'], ' ')) {
			// they gave multiple classes separated by a space
			$item['class'] = explode(' ', $item['class']);
			$classes = array_merge($classes, $item['class']);
		}
		else {
			$classes[] = $item['class'];
		}
	}
	
	if ($item['active'] == TRUE) {
		$classes[] = 'active';
	}
	
	$class = (!empty($classes)) ? ' class="' . implode(' ' ,$classes) . '"' : '';

	$return = '<li' . $class . '>';
	$return .= '<a href="' . $item['url'] . '">' . $item['text'] . '</a>';
	
	// insert children?
	if (!empty($children_items)) {
		$return .= '<ul class="children">';
		
		foreach ($children_items as $child) {
			$return .= $child;
		}
		
		$return .= '</ul>';
	}
	
	$return .= '</li>';
	
	return $return;
}
	
function parse_links (&$menu_items, &$menu_children, $links, $menu, &$smarty, $params) {
	if (empty($links)) {
		return FALSE;
	}

	foreach ($links as $link) {
		$display_this_item = TRUE;
		if ($link['privileges']) {
			if (!$smarty->CI->user_model->in_group($link['privileges'])) {
				$display_this_item = FALSE;
			}
		}
	
		if ($display_this_item == TRUE) {
			if ($link['special_type'] == FALSE) {
				// calculate URL
				if ($link['external_url']) {
					if (strstr($link['external_url'], ':')) {
						// full http:// URL
						$url = $link['external_url'];
					}
					else {
						$url = site_url($link['external_url']);
					}
				}
				else {
					// it's in the universal links database, and we have link_url_path
					$url = site_url($link['link_url_path']);
				}
				
				// is it active?
				$active = FALSE;
				if ($link['external_url'] == TRUE and (current_url() == $url)) {
					$active = TRUE;
				}
				elseif ($link['external_url'] == FALSE and trim($smarty->CI->uri->uri_string,'/') == trim($link['link_url_path'],'/')) {
					$active = TRUE;
				}
				
				$menu_items[$link['id']] = array(
												'text' => $link['text'],
												'url' => $url,
												'active' => $active,
												'class' => $link['class'],
												'is_child' => ($link['parent_menu_link_id'] != '0') ? TRUE : FALSE
											);
			}
			else {
				// it's a special link
				if ($link['special_type'] == 'home') {
					$menu_items[$link['id']] = array(
												'text' => $link['text'],
												'url' => site_url(),
												'active' => ($smarty->CI->uri->segment(1) == FALSE) ? TRUE : FALSE,
												'class' => $link['class'],
												'is_child' => ($link['parent_menu_link_id'] != '0') ? TRUE : FALSE
											);
				}
				elseif ($link['special_type'] == 'control_panel') {
					$menu_items[$link['id']] = array(
												'text' => $link['text'],
												'url' => site_url('admincp'),
												'active' => ($smarty->CI->uri->segment(1) == 'admincp') ? TRUE : FALSE,
												'class' => $link['class'],
												'is_child' => ($link['parent_menu_link_id'] != '0') ? TRUE : FALSE
											);
				}
				elseif ($link['special_type'] == 'my_account') {
					$menu_items[$link['id']] = array(
												'text' => $link['text'],
												'url' => site_url('users'),
												'active' => ($smarty->CI->uri->segment(1) == 'users') ? TRUE : FALSE,
												'class' => $link['class'],
												'is_child' => ($link['parent_menu_link_id'] != '0') ? TRUE : FALSE
											);
					
					if ($smarty->CI->user_model->logged_in() and $link['parent_menu_link_id'] == '0' and ($params['show_sub_menus'] == 'yes' or ($params['show_sub_menus'] == 'active' and $menu_items[$link['id']] == TRUE))) {
						// add children						
						$menu_children[$link['id']][] = 'profile';
						$menu_children[$link['id']][] = 'password';
						if (module_installed('billing')) {
							$menu_children[$link['id']][] = 'invoices';
						}
						$menu_children[$link['id']][] = 'logout';
						
						$menu_items['profile'] = array(
													'text' => 'Update Profile',
													'url' => site_url('users/profile'),
													'active' => ($smarty->CI->uri->segment(1) == 'users' and $smarty->CI->uri->segment(2) == 'profile') ? TRUE : FALSE,
													'class' => 'account_profile',
													'is_child' => TRUE
												);
												
						$menu_items['password'] = array(
													'text' => 'Change Password',
													'url' => site_url('users/password'),
													'active' => ($smarty->CI->uri->segment(1) == 'users' and $smarty->CI->uri->segment(2) == 'password') ? TRUE : FALSE,
													'class' => 'account_password',
													'is_child' => TRUE
												);
						
						if (module_installed('billing')) {						
							$menu_items['invoices'] = array(
														'text' => 'View Invoices',
														'url' => site_url('users/invoices'),
														'active' => ($smarty->CI->uri->segment(1) == 'users' and $smarty->CI->uri->segment(2) == 'invoices') ? TRUE : FALSE,
														'class' => 'account_invoices',
														'is_child' => TRUE
													);											
						}
												
						$menu_items['logout'] = array(
													'text' => 'Logout',
													'url' => site_url('users/logout'),
													'active' => ($smarty->CI->uri->segment(1) == 'users' and $smarty->CI->uri->segment(2) == 'logout') ? TRUE : FALSE,
													'class' => 'account_logout',
													'is_child' => TRUE
												);
					}
				}
				elseif ($link['special_type'] == 'store' and module_installed('store')) {
					$menu_items[$link['id']] = array(
												'text' => $link['text'],
												'url' => site_url('store'),
												'active' => ($smarty->CI->uri->segment(1) == 'store' or ($smarty->CI->uri->segment(1) == 'checkout')) ? TRUE : FALSE,
												'class' => $link['class'],
												'is_child' => ($link['parent_menu_link_id'] != '0') ? TRUE : FALSE
											);
				}
				elseif ($link['special_type'] == 'search') {
					$menu_items[$link['id']] = array(
												'text' => $link['text'],
												'url' => site_url('search'),
												'active' => ($smarty->CI->uri->segment(1) == 'search') ? TRUE : FALSE,
												'class' => $link['class'],
												'is_child' => ($link['parent_menu_link_id'] != '0') ? TRUE : FALSE
											);
				}
				elseif ($link['special_type'] == 'subscriptions' and module_installed('billing')) {
					$menu_items[$link['id']] = array(
												'text' => $link['text'],
												'url' => site_url('subscriptions'),
												'active' => ($smarty->CI->uri->segment(1) == 'subscriptions') ? TRUE : FALSE,
												'class' => $link['class'],
												'is_child' => ($link['parent_menu_link_id'] != '0') ? TRUE : FALSE
											);
				}
				elseif ($link['special_type'] == 'cart') {
					$menu_items[$link['id']] = array(
												'text' => $link['text'],
												'url' => site_url('users'),
												'active' => ($smarty->CI->uri->segment(1) == 'cart') ? TRUE : FALSE,
												'class' => $link['class'],
												'is_child' => ($link['parent_menu_link_id'] != '0') ? TRUE : FALSE
											);
				}
			}
										
			// should we load children?
			// only if show_sub_menus parameter says so, and this isn't already a child link
			if ($link['parent_menu_link_id'] == '0' and ($params['show_sub_menus'] == 'yes' or ($params['show_sub_menus'] == 'active' and $menu_items[$link['id']] == TRUE))) {
				// load children
				$links_children = $smarty->CI->menu_model->get_links(array('menu' => $menu['id'], 'parent' => $link['id']));
				
				if (is_array($links_children)) {
					// track children
					foreach ($links_children as $link_child) {
						$menu_children[$link['id']][] = $link_child['id'];
					}
				
					parse_links($menu_items, $menu_children, $links_children, $menu, $smarty, $params);
				}
			}
		}
	}
}