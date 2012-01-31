<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Menu Manager Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
				
		$this->admin_navigation->parent_active('design');
	}
	
	function index () {	
		$this->load->model('menu_model');
		$menus = $this->menu_model->get_menus();
		if (empty($menus)) {
			redirect('admincp/menu_manager/create');
			return TRUE;
		}
		
		if (!$this->session->userdata('manage_menu_id')) {
			$this->switch_active($menus[0]['id']);
			$this->session->set_userdata('manage_menu_parent_link_id','0');
			
			return FALSE;
		}
		
		if (!$this->session->userdata('manage_menu_parent_link_id')) {
			$this->session->set_userdata('manage_menu_parent_link_id','0');
		}
		
		$active_menu_id = $this->session->userdata('manage_menu_id');
		$active_menu = $this->menu_model->get_menu($active_menu_id);
		
		// set title from menu and possibly child menu
		$title = '<a href="' . site_url('admincp/menu_manager/switch_parent/0') . '">' . $active_menu['name'] . '</a>';
		
		if ($this->session->userdata('manage_menu_parent_link_id') and $this->session->userdata('manage_menu_parent_link_id') != 0) {
			$link = $this->menu_model->get_link($this->session->userdata('manage_menu_parent_link_id'));
			$title .= ' > <a href="' . site_url('admincp/menu_manager/switch_parent/' . $link['id']) . '">' . $link['text'] . '</a>';
		}
		
		// get possible links
		$possible_links = $this->get_possible_links($active_menu['id']);	
		$possible_links = $this->load->view('possible_links', array('possible_links' => $possible_links), TRUE);
		
		$data = array(
						'menus' => $menus,
						'active_menu' => $active_menu_id,
						'title' => $title,
						'active_id'	=> $active_menu_id,
						'possible_links' => $possible_links
					);
	
		$this->load->view('menu_manager', $data);
	}
	
	function switch_active ($id) {
		$this->session->set_userdata('manage_menu_id',$id);
		
		redirect('admincp/menu_manager');
	}
	
	function switch_parent ($id) {
		$this->session->set_userdata('manage_menu_parent_link_id', $id);
		
		redirect('admincp/menu_manager');
	}
	
	function get_possible_links ($menu_id) {
		// Each "possible link" must have the following 3 attributes:
		// - Text (display text)
		// - Type (content type, not used technically but just to show what type of content it is)
		// - Code (a base64_encoded, serialized array of data including:
		//		- link_type (either "link" or "special),
		//		- link_id (if in universal `links` table and link_type == "link"),
		//		- special_type (if link_type == "special")
		
		$possible_links = array();
		
		// get current links so we can prevent duplicates
		$this->load->model('menu_model');
		$current_links = $this->menu_model->get_links(array('menu' => $this->session->userdata('manage_menu_id'), 'parent' => $this->session->userdata('manage_menu_parent_link_id')));
		
		// add special links
		$special_links = array(
								'home' => 'Home',
								'control_panel' => 'Control Panel',
								'my_account' => 'My Account',
								'search' => 'Search'
							);
								
		if (module_installed('store')) {
			$special_links['store'] = 'Store';
			$special_links['cart'] = 'Shopping Cart';
		}								
		
		if (module_installed('billing')) {
			$special_links['subscriptions'] = 'Subscription Plans';				
		}								
							
		foreach ($special_links as $special_link_code => $special_link_name) {
			if (!$this->special_link_in_array($current_links, $special_link_code)) {
				$possible_links[] = array(
										'text' => $special_link_name,
										'type' => 'Special',
										'code' => base64_encode(serialize(array(
																			'special_type' => $special_link_code,
																			'link_type' => 'special',
																			'link_text' => $special_link_name
																		)))
									);
			}
		}
		
		// get all content links from the universal link database
		$this->load->model('link_model');
		$links = $this->link_model->get_links();
		
		foreach ((array)$links as $link) {
			if (!$this->universal_link_in_array($current_links, $link['id'])) {
				$possible_links[] = array(
										'text' => $link['title'],
										'type' => $link['type'],
										'code' => base64_encode(serialize(array(
																		'link_id' => $link['id'],
																		'link_type' => 'link',
																		'link_text' => $link['title']
																	)))
									);
			}
		}
		
		return $possible_links;
	}
	
	/*
	* Helper Method to browse current links in array and make sure that this special link type isn't in it
	*
	* @param array $links Array of current links
	* @param string $special_type The special type to browse for (e.g., my_account, store)
	*
	* @return boolean TRUE if it exists in the links array
	*/
	function special_link_in_array($links = array(), $special_type = '') {
		if (!is_array($links)) {
			return FALSE;
		}
	
		foreach ($links as $link) {
			if ($link['special_type'] == $special_type) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function universal_link_in_array($links = array(), $link_id = '') {
		if (!is_array($links)) {
			return FALSE;
		}
		
		foreach ($links as $link) {
			if ($link['link_id'] == $link_id) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function add_link () {
		// parse code for type, ID, and module
		$code = $this->input->post('code');
		
		$this->load->model('menu_model');
		
		if (!$this->input->post('external')) {
			$link = unserialize(base64_decode($code));
			
			if ($link['link_type'] == 'link') {
				$this->menu_model->add_link($this->session->userdata('manage_menu_id'), $this->session->userdata('manage_menu_parent_link_id'), 'link', $link['link_id'], $link['link_text']);
			}
			elseif ($link['link_type'] == 'special') {
				$this->menu_model->add_link($this->session->userdata('manage_menu_id'), $this->session->userdata('manage_menu_parent_link_id'), 'special', 0, $link['link_text'], $link['special_type']);
			}
		}
		else {
			$this->menu_model->add_link($this->session->userdata('manage_menu_id'), $this->session->userdata('manage_menu_parent_link_id'), 'external', 0, $this->input->post('text'), FALSE, $this->input->post('url'));
		}
		
		// return the current menu
		$this->get_links();
	}
	
	function remove_link () {
		$this->load->model('menu_model');
		$this->menu_model->remove_link($this->input->post('menu_link_id'));
		
		$this->get_links();
	}
	
	function get_links () {
		$this->load->model('menu_model');
		
		$this->load->model('users/usergroup_model');
		$groups = $this->usergroup_model->get_usergroups();
		
		$parent_id = ($this->session->userdata('manage_menu_parent_link_id')) ? $this->session->userdata('manage_menu_parent_link_id') : 0;
		
		$links = $this->menu_model->get_links(array('menu' => $this->session->userdata('manage_menu_id'), 'parent' => $parent_id));
			
		$data = array(
					'links' => $links,
					'groups' => $groups,
					'parent_id' => $parent_id
					);	
		$this->load->view('links', $data);
	}
	
	function show_possible_links () {
		$this->load->model('users/usergroup_model');
		
		$possible_links = $this->get_possible_links($this->session->userdata('manage_menu_id'));
		$this->load->view('possible_links', array('possible_links' => $possible_links));
	}
	
	/*
	* Saves Link Order
	*
	*/
	function save_order () {
		$order = $this->input->post('link');
		$count = 1;
		foreach ($order as $link) {
			$this->db->update('menus_links',array('menu_link_order' => $count), array('menu_link_id' => $link));
			
			$count++;
		}

		if (isset($this->CI->cache)) {
			$this->CI->cache->file->clean();
		}
	}
	
	function edit_link () {
		$link_id = $this->input->post('link_id');
		$text = $this->input->post('text');
		$privileges = $this->input->post('privileges');
		$class = $this->input->post('class');
		$external_url = $this->input->post('external_url') ? $this->input->post('external_url') : null;
		
		$this->load->model('menu_model');

		$this->menu_model->update_link($link_id, $text, $privileges, $class, $external_url);
		
		echo 'Edit saved.';
	}
	
	function create () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('New Menu');
		$form->text('Menu Name','name','','This name is only used in the control panel and theme files to reference this menu.  It will not be seen by your web site\'s visitors.',TRUE,'e.g., main_menu',TRUE);
		
		$this->load->model('menu_model');
		$menus = $this->menu_model->get_menus();
		
		$is_first_menu = (empty($menus)) ? TRUE : FALSE;
		
		$data = array(
					'form' => $form->display(),
					'form_title' => ($is_first_menu == TRUE) ? 'Create Your First Site Menu' : 'Create New Menu',
					'form_action' => site_url('admincp/menu_manager/post_menu/new')
				);
				
		$this->load->view('menu_form',$data);
	}
	
	function post_menu ($action = 'new', $id = FALSE) {
		$this->load->model('menu_model');
		
		if ($action == 'new') {
			$menu_id = $this->menu_model->new_menu($this->input->post('name'));
			$this->notices->SetNotice('Menu created successfully.');
			
			// manage this menu
			$this->session->set_userdata('manage_menu_id',$menu_id);
			$this->session->set_userdata('manage_menu_parent_link_id','0');
		}
		else {
			$this->menu_model->update_menu($id, $this->input->post('name'));
			$this->notices->SetNotice('Menu edited successfully.');
		}
		
		redirect('admincp/menu_manager');
	}
	
	function delete_menu() {
		$menu_id = $this->uri->segment(4);
		
		if (!is_numeric($menu_id))
		{
			$this->notices->SetError('Unable to delete menu.');
			return redirect('admincp/menu_manager');
		}
		
		$this->load->model('menu_model');
		$this->menu_model->delete_menu($menu_id);
		$this->notices->SetNotice('Menu successfully deleted.');
		
		$this->session->unset_userdata('manage_menu_id');
		
		return redirect('admincp/menu_manager');
	}
}