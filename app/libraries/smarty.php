<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Smarty Class
 *
 * @package Hero Framework
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
		
		$this->perpetual_data = array();
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
		$this->setCompileDir($this->CI->config->item('path_writeable') .'templates_compile');
		$this->setCacheDir($this->CI->config->item('path_writeable') .'templates_cache');
		
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
		
		// register the prefilter which parses {module_installed} tags
		$this->registerFilter('pre',array($this,'pre_filter'));
		
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
	
	/**
	* Display
	*
	* @param string $template
	*/
	function display ($template, $cache_id = null, $compile_id = null, $parent = null) {
		// automatically add .thtml extension if it's not already there (and no extension is)
		if (strpos($template,'.') === FALSE) {
			$template .= '.thtml';
		}
		
		parent::display($template, $cache_id, $compile_id, $parent);
	}
	
	/**
	* Pre Filter
	*
	* Templates pass through this function
	*
	* @param string $template
	* @object $smarty
	*
	* @return string
	*/
	function pre_filter ($template, $smarty) {
		$template = preg_replace_callback ('/\{module_installed\s*?name="(.*?)"\}(.*?)\{\/module_installed\}/is', array($this, 'pre_filter_module_installed') , $template);
		
		return $template;
	}
	
	/**
	* Pre Filter: {module_installed}
	*
	* @param array $matches
	*
	* @return string
	*/
	function pre_filter_module_installed ($matches) {
		$module = $matches[1];
		$tagdata = $matches[2];

		$module_installed = module_installed($module);
		
		if ($module_installed == FALSE) {
			return '';
		}
		else {
			return $tagdata;
		}
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
	
	function block_loop($data_name, $content = array(), $var_name, &$repeat) {
		// do we have data stored for this loop or is this a new loop?
		if (!isset($this->perpetual_data[$data_name])) {
			// start loop
			$this->perpetual_data[$data_name] = array();
		
			// it's a new loop
			// store the data and begin iteration
			if (empty($content)) {
				$repeat = FALSE;
				$this->unset_loop_data($data_name);
				return;
			}
			
			// store loop content array
			$this->set_loop_content($data_name, $content);
			
			// set loop index to track our traversing
			$index = 1;
			$this->loop_data($data_name, 'index', $index);
			
			// set loop count so we know when to stop
			$count = count($content);
			$this->loop_data($data_name, 'count', $count);
			
			// retrieve the first content item
			$content = $this->loop_content($data_name, $index);
		}
		elseif (empty($this->perpetual_data[$data_name])) {
			$repeat = FALSE;
			$this->unset_loop_data($data_name);
			return;
		}
		else {
			// it's an existing loop
			// retrieve date and continue iterating
			$index = $this->loop_data($data_name, 'index');
			$count = $this->loop_data($data_name, 'count');
			
			$content = $this->loop_content($data_name, $index);
		}
				
		if (!empty($content)) {
			$this->assign($var_name, $content);
		}
		
		// continue looping?
		// no, this doesn't really make sense - we want it to loop through one more time
		
		if ($count > ($index - 1)) {
			$this->loop_data($data_name, 'index', ($index + 1));
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
	* @return boolean TRUE
	*/
	function unset_loop_data ($data_name) {
		unset($this->perpetual_data[$data_name]);
		
		return TRUE;
	}
	
	/**
	* Create Loop Data Key from $filters
	*
	* Used for caching and persistent storage
	*
	* @param string|array $identifier Specify an array or string by which to identify this unique instance of content retrieval
	*
	* @return string MD5 of array
	*/
	public function loop_data_key ($identifier) {
		if (is_array($identifier)) {
			$string = '';
			
			foreach ($identifier as $k => $v) {
				$string = $k . '=' . $v;	
			}
			
			$identifier = $string;
		}
		else {
			$identifier = $identifier;	
		}
		
		$identifier = md5($identifier);
	
		return $identifier;
	}
	
	/**
	* Store Loop Data
	*
	* Stores/retrieves a variable used in a looping block plugin
	*
	* @param string $data_name
	* @param string $key
	* @param array|object $data
	*
	* @return boolean|array Data, or FALSE
	*/
	function loop_data ($data_name, $key = FALSE, $data = FALSE) {
		if ($key === FALSE and $data === FALSE) {
			// legacy call for the *content*
			if (isset($this->perpetual_data[$data_name]['content'])) {
				return $this->perpetual_data[$data_name]['content'];
			}
			else {
				return FALSE;
			}
		}
		elseif ($data === FALSE) {
			return (isset($this->perpetual_data[$data_name][$key])) ? $this->perpetual_data[$data_name][$key] : FALSE;
		}
		else {
			$this->perpetual_data[$data_name][$key] = $data;
			return;
		}
	}
	
	/**
	* Store Loop Content
	*
	* Stores/retrieves the loop content used in a looping block plugin.
	* We store independently of $this->loop_data() because we use unique
	* iteration procedures here.
	*
	* @param array $data The content array.  Pass only this to store a content array.
	*
	* @return boolean|array Content
	*/
	function set_loop_content ($data_name, $data = array()) {
		$this->perpetual_data[$data_name]['content'] = $data;
			
		return TRUE;
	}
	
	/**
	* Get Loop Content
	*
	* @param int $index The index to retrieve.
	*
	* @return boolean|array Content
	*/
	function loop_content ($data_name, $index = FALSE) {
		if ((int)$index === 1) {
			$content_item = current($this->perpetual_data[$data_name]['content']);
		}
		else {
			// returns FALSE at end of array so we know
			$content_item = next($this->perpetual_data[$data_name]['content']);
		}
		
		return $content_item;
	}
}
