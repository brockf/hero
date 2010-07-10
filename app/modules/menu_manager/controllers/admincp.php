<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Menu Manager Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
				
		$this->navigation->parent_active('design');
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
			
			return FALSE;
		}
		
		$active_menu_id = $this->session->userdata('manage_menu_id');
		$active_menu = $this->menu_model->get_menu($active_menu_id);
		
		// set title from menu and possibly child menu
		$title = '<a href="' . site_url('admincp/menu_manager') . '">' . $active_menu['name'] . '</a>';
		
		// get possible links
		$possible_links = $this->get_possible_links($active_menu['id']);
		
		$data = array(
						'menus' => $menus,
						'active_menu' => $active_menu_id,
						'title' => $title,
						'possible_links' => $possible_links
					);
	
		$this->load->view('menu_manager', $data);
	}
	
	function switch_active ($id) {
		$this->session->set_userdata('manage_menu_id',$id);
		
		redirect('admincp/menu_manager');
	}
	
	function get_possible_links ($menu_id) {
		// this array stores link_id > name combinations so that we can sort it and get a sorted array
		// each key is prefaced with "item" so that PHP doesn't ignore numerical indeces in asort()
		$sortable_links = array();
	
		// content
		$this->load->model('publish/content_model');
		$content = $this->content_model->get_contents(array('is_standard' => '1'));
		
		if (is_array($content)) {
			foreach ($content as $item) {
				$sortable_links['item' . $item['link_id']] = $item['title'];
				$links[$item['link_id']] = array(
													'name' => $item['title'],
													'module' => 'content',
													'type' => 'link',
													'code' => 'link[|]' . $item['link_id'] . '[|]content[|]' . $item['title']
												);
			}
		}
										
		// blogs/listings
		$this->load->model('blogs/blog_model');
		$blogs = $this->blog_model->get_blogs();
		
		if (is_array($blogs)) {
			foreach ($blogs as $blog) {
				$sortable_links['item' . $blog['link_id']] = $blog['title'];
				$links[$blog['link_id']] = array(
													'name' => $blog['title'],
													'module' => 'blog/listing',
													'type' => 'link',
													'code' => 'link[|]' . $blog['link_id'] . '[|]blog[|]' . $blog['title']
												);
			}
		}
		
		// rss feeds
		$this->load->model('rss/rss_model');
		$feeds = $this->rss_model->get_feeds();
		
		if (is_array($feeds)) {
			foreach ($feeds as $feed) {
				$sortable_links['item' . $feed['id']] = $feed['title'];
				$links[$feed['link_id']] = array(
												'name' => $feed['title'],
												'module' => 'rss feed',
												'type' => 'link',
												'code' => 'link[|]' . $feed['link_id'] . '[|]rss[|]' . $feed['title']
											);
			}
		}
		
		// sort the links by name
		asort($sortable_links);
		
		// get current links to stop duplicates
		$current_links = $this->menu_model->get_links(array('menu' => $this->session->userdata('manage_menu_id')));
		
		$link_duplicates = array();
		if (is_array($current_links)) {
			foreach ($current_links as $link) {
				if ($link['type'] == 'link') {
					$link_duplicates[] = $link['link_id'];
				}
			}
		}
		
		// now get the true links data in this order
		$possible_links = array();
		foreach ($sortable_links as $link_id => $name) {
			$link_id = str_replace('item','',$link_id);
			if (array_key_exists($link_id,$links)) {
				// no duplicates
				if ($links[$link_id]['type'] == 'link' and !in_array($link_id, $link_duplicates)) {
					$possible_links[] = $links[$link_id];
				}
			}
		}
		
		return $possible_links;
	}
	
	function add_link () {
		// parse code for type, ID, and module
		$code = $this->input->post('code');
		list($type,$data1,$data2,$name) = explode('[|]',$code);
		
		$this->load->model('menu_model');
		
		if ($type == 'link') {
			$this->menu_model->add_link($this->session->userdata('manage_menu_id'), FALSE, 'link', $data1, $data2, $name, FALSE, FALSE, FALSE, FALSE);
		}
		elseif ($type == 'external') {
			$this->menu_model->add_link($this->session->userdata('manage_menu_id'), FALSE, 'external', FALSE, FALSE, $name, FALSE, $data2, FALSE, FALSE);
		}
		
		/*
		* Add Link to Menu
		*
		* @param int $menu_id Which menu does it belong to?
		* @param int $parent_link If it's a 2nd_tier link name the parent
		* @param string $type Either 'external', 'special', or 'link'
		* @param int $link_id If it's in the universal link database, what's the link_id?
		* @param string $module If it's in the universal link database, which module will parse it?
		* @param string $name The display text
		* @param string $special_type If it's a "special" link, give it a name (e.g., "store", "account")
		* @param string $external_url The full URL for external links
		* @param array $privileges A serialized array of member groups who can see it
		* @param boolean $require_active_parent If it's a child, does it require an active parent to be visible?
		*
		* @return int $menu_link_id
		*/
		
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
		
		$links = $this->menu_model->get_links(array('menu' => $this->session->userdata('manage_menu_id'), 'parent' => '0'));
			
		$data = array(
					'links' => $links
					);	
		$this->load->view('links', $data);
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
		}
		else {
			$this->menu_model->update_menu($id, $this->input->post('name'));
			$this->notices->SetNotice('Menu edited successfully.');
		}
		
		redirect('admincp/menu_manager');
	}
}