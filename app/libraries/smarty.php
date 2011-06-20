<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Smarty Class
 *
 * @package Electric Framework
 * @author  Electric Function, Inc.
 */
 
require_once(APPPATH.'libraries/smarty/Smarty.class.php');

class CI_Smarty extends Smarty {
	public $CI;
	
	// holds data from looping block plugins
	private $perpetual_data; 
	
	// everytime we update the Smarty library, change this version.
	// if this date is newer than the current library, all compiled templates
	// will be deleted
	private $library_version = '3.06';

	function __construct ($email_parser = FALSE) {
		parent::__construct();
	}
	
	/**
	* Initialize
	*
	* @param boolean $email_parser If this is the template parser for outgoing emails, set to TRUE
	*							   This overrides certain features of the parser.
	*/
	function initialize ($email_parser = FALSE) {
		// if these folders don't exist, we're probably not finished installing yet...
		// let's stop so we don't throw errors
		if (!file_exists(FCPATH . 'writeable/templates_compile')) {
			return FALSE;
		}
	
		// store CI within Smarty's object
		$this->CI =& get_instance();
		
		// turn down error reporting (makes templates a lot cleaner)
		$this->error_reporting = E_ALL & ~E_NOTICE;
		
		// specify directories
		$this->setCompileDir(FCPATH . 'writeable/templates_compile');
		$this->setCacheDir(FCPATH . 'writeable/templates_cache');
		
		if ($email_parser == FALSE) {
			$this->setTemplateDir(FCPATH . 'themes/' . $this->CI->config->item('current_theme'));
		}
		else {
			$this->setTemplateDir(setting('path_email_templates'));
		}
		
		// plugin directories
		$this->addPluginsDir(FCPATH . 'themes/_plugins/');
		
		if ($email_parser == FALSE) {
			$this->addPluginsDir(FCPATH . 'themes/' . $this->CI->config->item('current_theme') . '/plugins');
		}
		else {
			$this->addPluginsDir(setting('path_email_templates') . '/plugins');
		}
		
		// check for a library update
		$current_library = setting('smarty_library');
		if (empty($current_library) or version_compare($this->library_version,$current_library,'>') == TRUE) {
			$this->clearCompiledTemplate();
			
			// store setting
			if (empty($current_library)) {
				$this->CI->settings_model->new_setting(1, 'smarty_library', $this->library_version, '', 'text','', FALSE, TRUE);
			}
			else {
				$this->CI->settings_model->update_setting('smarty_library', $this->library_version);
			}
		}
		
		// set global template variables
		$this->assign('APPPATH',APPPATH);
		$this->assign('BASEPATH',BASEPATH);
		$this->assign('FCPATH',FCPATH);
		
		// put settings into template variables
		$settings = $this->CI->config->config;
		
		$this->assign('setting', $settings);
		$this->assign('settings', $settings);
		
		if ($email_parser == FALSE) {
			// assign current URL to variable
			$this->assign('current_url',current_url());
			
			// assign uri segments
			$uri_segments = array();
			$count = 1;
			foreach ($this->CI->uri->segments as $segment) {
				$uri_segments[$count] = $segment;
				$count++;
			}
			$this->assign('uri_segment',$uri_segments);
			$this->assign('uri_segments',$uri_segments);
			
			// are we loggedin
			$this->assign('logged_in',($this->CI->user_model->logged_in() ? TRUE : FALSE));
			
			// user data
			$this->assign('member', $this->CI->user_model->get());
		}
	}
	
	// modify the display class
	function display ($template, $cache_id = null, $compile_id = null, $parent = null) {
		// automatically add .thtml extension if it's not already there (and no extension is)
		if (strpos($template,'.') === FALSE) {
			$template .= '.thtml';
		}
		
		parent::display($template, $cache_id, $compile_id, $parent);
	}
	
	/**
	* Block Loop
	*
	* Loops through a block plugin intelligently from a content array
	*
	* @param string $data_name An identifiable system name for this dataset
	* @param array $content Either send the initial data array, or FALSE if you've already called it
	* @param string $var_name
	* @param boolean $repeat
	*
	* @return string $tagdata or NULL
	*/
	
	function block_loop($data_name, $content = FALSE, $var_name, &$repeat) {
		if (!$this->loop_data($data_name)) {
			if (empty($content)) {
				$repeat = FALSE;
				return;
			}
			
			// set loop data
			$this->loop_data($data_name, $content);
			$index = 0;
			$this->loop_data($data_name . '_index', $index);
		}
		else {
			$content = $this->loop_data($data_name);
			$index = $this->loop_data($data_name . '_index');
		}
		
		if (isset($content[$index])) {
			$this->assign($var_name, $content[$index]);
		}
		
		// continue looping?
		if (isset($content[($index)])) {
			$this->loop_data($data_name . '_index', ($index + 1));
			$repeat = TRUE;
		}
		else {
			$this->unset_loop_data($data_name);
			
			$repeat = FALSE;
		}
	}
	
	/**
	* Unset Loop Data
	* 
	* @param string$data_name
	*
	* @return boolean TRUE
	*/
	function unset_loop_data ($data_name) {
		unset($this->perpetual_data[$data_name]);
		
		return TRUE;
	}
	
	/**
	* Create Loop Data Key from $filters
	*
	* @param array $array Specify a series of filters which will identify this loop data amongst all other loop data
	* @param string $identifier Specify an additional key which will help identify this set of block data, in case your $filters are too generic
	*
	* @return string MD5 of array
	*/
	public function loop_data_key ($array, $identifier = '') {
		$string = '';
		
		foreach ($array as $k => $v) {
			$string = $k . '=' . $v;	
		}
		
		if (!empty($identifier)) {
			$string .= $identifier;
		}
		
		return md5($string);
	}
	
	/**
	* Store Loop Data
	*
	* Stores/retrieves a variable used in a looping block plugin
	*
	* @param string $key
	* @param array|object $data
	*
	* @return boolean|array Data, or FALSE
	*/
	function loop_data ($key, $data = FALSE) {
		if ($data === FALSE) {
			return (isset($this->perpetual_data[$key])) ? $this->perpetual_data[$key] : FALSE;
		}
		else {
			$this->perpetual_data[$key] = $data;
			return;
		}
	}
}
