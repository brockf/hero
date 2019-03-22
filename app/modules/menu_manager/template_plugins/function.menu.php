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
    $menu_grandchildren = array();

    // parse parent links, which will in turn call child links if necessary
	parse_links($menu_items, $menu_children, $menu_grandchildren, $links, $menu, $smarty, $params);

	$params['class'] = (isset($params['class'])) ? $params['class'] : 'mm_c';
	$params['id'] = (isset($params['id'])) ? $params['id'] : 'mm_id';
	
	$return = '<ul class="' . $params['class'] . '" id="' . $params['id'] . '">';
    $grandchild_flag = FALSE;
    $childflag = FALSE;

	// $menu_items contains the menu items including the grandchildren
    foreach ($menu_items as $id => $item) {
		// do we have children?
		$children_items = array();

		if ($item['is_child'] == FALSE) {
            if ($childflag == TRUE) {
                // terminate the last child list
                $return .= '</ul></li>';
                $childflag = FALSE;
            }
            if ($grandchild_flag == TRUE) {
                // terminate grandchild list
                $return .= '</ul></li>';
                $grandchild_flag = FALSE;
            }

            if (isset($menu_children[$id])) {
				$grandchild_flag = FALSE;
                foreach ($menu_children[$id] as $key) {
					$item_child = $menu_items[$key];
					
					if (!empty($menu_items[$key])) {
						// set parent active if it's child is active
						if ($item_child['active'] == TRUE) {
							$item['active'] = TRUE;
						}

                        $children_items[] = li_html_child($item_child, $grandchild_flag);
					}
				}
			}

			$classes = (!empty($children_items)) ? array('parent') : array();
            $ret_li = li_html($item, $children_items, $classes, $childflag, $grandchild_flag);
            $return .= $ret_li;
		}
	}
	
	$return .= '</ul>';

    $return = correct_li_tag($return);

	return $return;
}

// this function corrects the html list code for child that have grandchildren
function correct_li_tag($input_src) {

    // check for presence of error
    // the string has the ">" missing at the end on purpose
    $key = strpos($input_src, '</li><ul');
    if ($key === false) {
        // not found
        $exput_src = $input_src;
        $output_src = $exput_src;
    }
    else {
        // found !
        $input_parts = explode('</li><ul', $input_src);
        $cnt = count($input_parts);

        // add the parts back together (without the </li> tag)
        $exput_src = $input_parts[0];
        for($i=1;$i<$cnt;$i++) {
            $exput_src .= '<ul';
            $exput_src .= $input_parts[$i];
        }

        $output_src = $exput_src;
    }

    $key = strpos($exput_src, '</li></li></ul>');
    if ($key === false) {
        // not found
        $output_src = $exput_src;
    }
    else {
        // found !
        $input_parts = explode('</li></li></ul>', $exput_src);
        $cnt = count($input_parts);

        // add the parts back together (without the </li> tag)
        $exput_src = $input_parts[0];
        for($i=1;$i<$cnt;$i++) {
            $exput_src .= '</li></ul>';
            $exput_src .= $input_parts[$i];
        }

        $output_src = $exput_src;
    }

    return $output_src;
}

// This function specifically added for supporting the grandchildren
function li_html_child ($item, &$grandchild_flag) {
    $classes = array();
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

    $return = '';

    if (!$item['is_grandchild']) {
        if ($grandchild_flag == TRUE) {
            // terminate the grandchild list and the previous child list
            $return .= '</ul></li>';
            $grandchild_flag = FALSE;
        }
    }
    else {
        // item is a grandchild
        if ($grandchild_flag == FALSE) {
            $return .= '<ul class="grandchildren">';
            $grandchild_flag = TRUE;
        }
    }

    $return .= '<li' . $class . '>';
    $return .= '<a href="' . $item['url'] . '">' . $item['text'] . '</a>';

    $return .= '</li>';

    return $return;
}

function li_html ($item, $children_items = FALSE, $classes = array(), &$childflag, &$grandchild_flag) {
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

	if (!empty($children_items)) {
        $childflag = TRUE;
        //if ($grandchild_flag == TRUE) {
            //terminate the grandchild list if not already
        //    $return .= '<b></b></ul></li>';
        //    $grandchild_flag = FALSE;
        //}
        $return .= '<ul class="children">';
		
		foreach ($children_items as $child) {

            $return .= $child;
		}
	}

	$return .= '</li>';
	
	return $return;
}

/**
 * Menu utility function
 *
 * Local function used to sort links into menus
 *
 * @param reference array $menu_items
 * @param reference array $menu_children
 * @param reference array $menu_grandchildren
 * @param array $links
 * @param array $menu
 * @param reference array $smarty
 * @param array $params
 *
 */
function parse_links (&$menu_items, &$menu_children, &$menu_grandchildren, $links, $menu, &$smarty, $params) {
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
												'is_child' => ($link['parent_menu_link_id'] != '0') ? TRUE : FALSE,
                                                'is_grandchild' => ($link['child_menu_link_id'] != '0') ? TRUE : FALSE
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
                        $links_grandchildren = $smarty->CI->menu_model->get_links(array('menu' => $menu['id'], 'parent' => $link_child['id']));
                        if (is_array($links_grandchildren)) {
                            $menu_items[$link_child['id']] = array(
                                                        'text' => $link_child['text'],
                                                        'url' => $link_child['link_url_path'],
                                                        'active' => TRUE,
                                                        'class' => $link_child['class'],
                                                        'is_child' => TRUE,
                                                        'is_grandchild' => FALSE
                                                        );

                            foreach ($links_grandchildren as $link_grandchild) {
                                $menu_children[$link['id']][] = $link_grandchild['id'];
                            }

                            parse_links($menu_items, $menu_children, $menu_grandchildren, $links_grandchildren, $menu, $smarty, $params);
                        }
                    }
					parse_links($menu_items, $menu_children, $menu_grandchildren, $links_children, $menu, $smarty, $params);
				}
			}
		}
	}
}