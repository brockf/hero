<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Smarty Class
 *
 * @package Electric Publisher
 * @author  Electric Function, Inc.
 */
 
require_once(APPPATH.'libraries/smarty/Smarty.class.php');

class CI_Smarty extends Smarty {
	var $CI;

	function __construct() {
		parent::__construct();
		
		// store CI within Smarty's object
		$this->CI =& get_instance();
		
		// specify directories
		$this->setCompileDir(FCPATH . 'writeable/templates_compile');
		$this->setCacheDir(FCPATH . 'writeable/templates_cache');
		$this->setTemplateDir(FCPATH . 'themes/' . $this->CI->config->item('current_theme'));
		
		// plugin directories
		$this->addPluginsDir(FCPATH . 'themes/_plugins/');
		$this->addPluginsDir(FCPATH . 'themes/' . $this->CI->config->item('current_theme') . '/plugins');
		
		// set global template variables
		$this->assign('APPPATH',APPPATH);
		$this->assign('BASEPATH',BASEPATH);
		$this->assign('FCPATH',FCPATH);
	}
	
	// modify the display class
	function display ($template, $cache_id = null, $compile_id = null, $parent = null) {
		// automatically add .thtml extension if it's not already there (and no extension is)
		if (strpos($template,'.') === FALSE) {
			$template .= '.thtml';
		}
		
		parent::display($template, $cache_id, $compile_id, $parent);
	}
}
