<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Theme Control Panel
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
		$this->load->model('theme/theme_model');
		$themes = $this->theme_model->get_themes();
		
		$data = array(
						'themes' => $themes
					);
					
		$this->load->view('themes', $data);
	}
	
	function install ($theme) {
		$this->admin_navigation->module_link('Back to Themes',site_url('admincp/theme'));
		
		$this->load->model('theme/theme_model');
		$themes = $this->theme_model->get_themes();
		
		if (empty($theme) or !in_array($theme, $themes)) {
			die(show_error('Invalid theme selection.'));
		}
		
		if (file_exists(FCPATH . 'themes/' . $theme . '/install.php')) {
			$install_file = TRUE;
		}
		else {
			$install_file = FALSE;
		}
		
		$data = array(
					'theme' => $theme,
					'install_file' => $install_file
				);
				
		$this->load->view('install_confirm', $data);
	}
	
	function complete_install () {
		$theme = $this->input->post('theme');
		
		// the deadliest code in the application!
		// wipes everything clean
		if ($this->input->post('reset') == 'yes') {
			// perform reset
			
			// Content, content types, forms, blogs, topics, RSS feeds, products,
			// collections, product options, subscriptions, and menus WILL be erased.
			
			// content & conte types
			$this->load->model('publish/content_type_model');
			$types = $this->content_type_model->get_content_types();
			
			if (!empty($types)) {
				foreach ($types as $type) {
					$this->content_type_model->delete_content_type($type['id']);
				}
			}
			
			// forms
			$this->load->model('forms/form_model');
			$forms = $this->form_model->get_forms();
			
			if (!empty($forms)) {
				foreach ($forms as $form) {
					$this->form_model->delete_form($form['id']);
				}
			}
			
			// blogs
			$this->load->model('blogs/blog_model');
			$blogs = $this->blog_model->get_blogs();
			
			if (!empty($blogs)) {
				foreach ($blogs as $blog) {
					$this->blog_model->delete_blog($blog['id']);
				}
			}
			
			// rss feeds
			$this->load->model('rss/rss_model');
			$feeds = $this->rss_model->get_feeds();
			
			if (!empty($feeds)) {
				foreach ($feeds as $feed) {
					$this->rss_model->delete_feed($feed['id']);
				}
			}
			
			// topics
			$this->load->model('publish/topic_model');
			$topics = $this->topic_model->get_topics();
			
			if (!empty($topics)) {
				foreach ($topics as $topic) {
					$this->topic_model->delete_topic($topic['id']);
				}
			}
			
			// products
			if (module_installed('store')) {
				$this->load->model('store/products_model');
				$products = $this->products_model->get_products();
				
				if (!empty($products)) {
					foreach ($products as $product) {
						$this->products_model->delete_product($product['id']);
					}
				}
				
				// collections
				$this->load->model('store/collections_model');
				$collections = $this->collections_model->get_collections();
				
				if (!empty($collections)) {
					foreach ($collections as $collection) {
						$this->collections_model->delete_collection($collection['id']);
					}
				}
				
				// product options
				$this->load->model('store/product_option_model');
				$options = $this->product_option_model->get_options();
				
				if (!empty($options)) {
					foreach ($options as $option) {
						$this->product_option_model->delete_option($option['id']);
					}
				}
			}
			
			if (module_installed('billing')) {
				// subscriptions
				$this->load->model('billing/subscription_plan_model');
				$subscriptions = $this->subscription_plan_model->get_plans();
				
				if (!empty($subscriptions)) {
					foreach ($subscriptions as $plan) {
						$this->subscription_plan_model->delete_plan($plan['id']);
					}
				}
			}
			
			// menus
			$this->load->model('menu_manager/menu_model');
			$menus = $this->menu_model->get_menus();
			
			if (!empty($menus)) {
				foreach ($menus as $menu) {
					$this->menu_model->delete_menu($menu['id']);
				}
			}
			
			// custom fields
			$this->load->model('custom_fields_model');
			
			// custom fields - products
			if  (module_installed('store')) {
				if ($this->config->item('products_custom_field_group')) {
					$group = $this->config->item('products_custom_field_group');
					$this->custom_fields_model->delete_group($group, 'products');
					$this->settings_model->delete_setting('products_custom_field_group');
				}
				
				// custom fields - collections
				if ($this->config->item('collections_custom_field_group')) {
					$group = $this->config->item('collections_custom_field_group');
					$this->custom_fields_model->delete_group($group, 'collections');
					$this->settings_model->delete_setting('collections_custom_field_group');
				}	
			}
			
			// clear custom fields cache
			$this->custom_fields_model->cache = array();
		}
		
		// pause to let MySQL have a break
		sleep(1);
		
		// reload settings
		$this->settings_model->set_settings();
		
		if ($this->input->post('default_content') == 'yes') {
			// check for default content
			$install_file = FCPATH . 'themes/' . $theme . '/install.php';
			
			if (file_exists($install_file)) {
				include($install_file);
			}
			else {
				die(show_error('Unable to locate installation file.'));
			}
		}
		
		// set setting
		$this->settings_model->update_setting('theme', $theme);
		
		$this->load->view('install_complete');
	}	
	
	function editor () {
		$this->admin_navigation->module_link('Change Site Theme',site_url('admincp/theme'));
		
		$this->load->model('theme/theme_model');
		$themes = $this->theme_model->get_themes();
		
		$options = array();
		foreach ($themes as $theme) {
			$options[$theme] = $theme;
		}
		
		// by default, the first theme to edit is the existing theme
		$theme = setting('theme');
		
		$data = array(
						'themes' => $options,
						'theme' => $theme,
						'path' => FCPATH . 'themes/'
					);
		$this->load->view('editor', $data);
	}
	
	function save_file () {
		$theme = $this->input->post('theme');
		$file = $this->input->post('file');
		$filename = $this->input->post('new_filename');
		$contents = $this->input->post('contents');
		
		$path = FCPATH . 'themes/' . $theme . '/' . $filename;
		
		// they can only write to the themes directory!
		$path = preg_replace('/\w+\/\.\.\//', '', $path);
		
		if (strpos($path, FCPATH . 'themes/') === FALSE) {
			return FALSE;
		}
		
		// you can only write certain filetypes
		$extension = end(explode('.',$path));
		if (!in_array($extension, array('html','thtml','txml','trss','xml','rss','js','css'))) {
			return FALSE;
		}
				
		if (file_exists($path) and !is_really_writable($path)) {
			return FALSE;
		}
		
		// attempt write
		$this->load->helper('file');
		write_file($path, $contents);
		
		echo 'saved';
	}
	
	function delete_file () {
		$theme = $this->input->post('theme');
		$file = $this->input->post('file');
		
		$path = FCPATH . 'themes/' . $theme . '/' . $file;
		
		// attempt write
		if (!file_exists($path) or unlink($path)) {
			echo 'deleted';
		}
	}
	
	function map_url() {
		$url = $this->input->post('url');
		$template = $this->input->post('template');
		$title = $this->input->post('title');
		
		if (empty($title) or empty($url) or empty($template)) {
			return FALSE;
		}
		
		$this->load->model('link_model');
		if (!$this->link_model->is_unique($url)) {
			echo 'duplicate';
			return FALSE;
		}
		else {
			if ($this->link_model->new_link($url, FALSE, $title, 'Template Map', 'theme', 'template', 'view', $template)) {
				echo 'success';
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function unmap () {
		$link_id = $this->input->post('link_id');
		
		$this->load->model('link_model');
		$this->link_model->delete_link($link_id);
	}
	
	function set_frontpage () {
		$template = $this->input->post('template');
		
		$this->settings_model->update_setting('frontpage_template', $template);
		
		echo 'success';
	}
	
	function get_maps () {
		$this->load->model('link_model');
		$maps = $this->link_model->get_links(array('parameter' => $this->input->post('template')));
		
		if (empty($maps)) {
			echo '<ul><li>None</li></ul>';
		}
		else {
			echo '<ul>';
			foreach ($maps as $map) {
				echo '<li>' . site_url($map['url_path']) . ' (<a rel="' . $map['id'] . '" class="remove_map" href="javascript:void(0)">unmap</a>)</li>';
			}
			echo '</ul>';
		}
	}
	
	function check_writeable () {
		$theme = $this->input->post('theme');
		$file = $this->input->post('file');
		
		$path = FCPATH . 'themes/' . $theme . '/' . $file;
		
		// check writeability
		if (!is_really_writable($path)) {
			echo 'fail';
			return FALSE;
		}
		
		echo 'success';
	}
	
	function get_file () {
		$theme = $this->input->post('theme');
		$file = $this->input->post('file');
		
		$path = FCPATH . 'themes/' . $theme . '/' . $file;
		
		$contents = file_get_contents($path);
		
		echo $contents;
	}
	
	function file_list () {
		$this->load->helper('directory');
		
		$directory = FCPATH . 'themes/' . $this->input->post('theme');
		
		$files = directory_map($directory);
		
		if (empty($files)) {
			echo 'No files in directory: ' . $directory;
			return FALSE;
		}
		
		echo '<ul>';
		$this->parse_files($files);
		echo '</ul>';
	}
	
	function parse_files ($files, $path = '') {
		//asort($files);
		
		$this->load->helper('file_extension');
	
		foreach ($files as $folder => $file) {
			if (is_array($file)) {
				// folder
				$path = rtrim($path,'/') . '/';
				echo '<li class="folder"><a class="folder_link tooltip" title="view folder contents" href="javascript:void(0)" rel="' . $path . $folder . '">' . $folder . '</a><ul>';
				$this->parse_files($file, $folder . '/');
				echo '</ul></li>';
			}
			else {
				// file		
				$ext = file_extension($file);
				
				if (str_replace('.thtml','',$file) == setting('frontpage_template') or $file == setting('frontpage_template') or $path.$file == setting('frontpage_template')) {
					$filetype = 'template home editable';
				}
				elseif (strcasecmp($ext,'thtml') === 0) {
					$filetype = 'template';
				}
				elseif (strcasecmp($ext,'txml') === 0) {
					$filetype = 'xml';
				}
				elseif (strcasecmp($ext,'jpg') === 0) {
					$filetype = 'image';
				}
				elseif (strcasecmp($ext,'jpeg') === 0) {
					$filetype = 'image';
				}
				elseif (strcasecmp($ext,'bmp') === 0) {
					$filetype = 'image';
				}
				elseif (strcasecmp($ext,'gif') === 0) {
					$filetype = 'image';
				}
				elseif (strcasecmp($ext,'png') === 0) {
					$filetype = 'image';
				}
				elseif (strcasecmp($ext,'css') === 0) {
					$filetype = 'css';
				}
				elseif (strcasecmp($ext,'php') === 0) {
					$filetype = 'php';
				}
				elseif (strcasecmp($ext,'js') === 0) {
					$filetype = 'javascript';
				}
				else {
					$filetype = '';
				}
				
				$classes = array();
				$classes[] = 'file';
				if (!empty($filetype)) {
					$classes[] = $filetype;
				}
				
				// editable classes
				if (in_array($filetype, array('css','javascript','template','php','xml'))) {
					$classes[] = ' editable';
				}
				echo '<li class="' . implode(' ',$classes) . '" rel="' . $path . $file . '">' . $file . '</li>';
			}
		}
	}
}