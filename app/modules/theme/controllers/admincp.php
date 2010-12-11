<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Theme Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->navigation->parent_active('design');
		
		$this->navigation->module_link('Change Default Theme',site_url('admincp/theme/switcher'));
	}
	
	function index () {
		redirect('admincp/theme/editor');
	}
	
	function editor () {
		$this->load->model('theme/theme_model');
		$themes = $this->theme_model->get_themes();
		
		$options = array();
		foreach ($themes as $theme) {
			$options[$theme] = $theme;
		}
		
		// by default, the first theme to edit is the existing theme
		$theme = setting('frontend_theme');
		
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