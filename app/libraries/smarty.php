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
		
		// put settings into template variables
		$settings = $this->CI->config->config;
		$this->assign('setting', $settings);
		
		// are we loggedin
		$this->assign('logged_in',($this->CI->user_model->logged_in() ? TRUE : FALSE));
		
		// register output variable which manipulates templates prior to Smarty parsing
		$this->loadFilter('pre','pre_smarty_parse');
	}
	
	/**
	* Pre Smarty Parsing
	*
	* This function is executed on all templates prior to Smarty parsing them
	*
	* @param string $template The template source
	*
	* @return string $template The modified template
	*/
	function pre_smarty_parse ($template) {
		$template = preg_replace('/\{block\:(.*?)\}/i','[[$1]]',$template);
		
		return $template;
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
	* Parse Variables within String
	*
	* Called by block functions to parse variables as if they were being parsed by Smarty
	* Because of $this->pre_smarty_parse(), all block data tags are now like [$title] (previously {$block:title}
	*
	* @param string $tagdata The Smarty/HTML code between two {block}test{/block} tags
	* @param array $variables An array of variables to substitute
	*
	* @return string HTML, parsed for variables
	*/
	function parse_string ($tagdata, $variables = array()) {
		// save current block's variables
		$this->string_variables = $variables;
	
		// variable replacement, with modifier support thanks to callback
		$tagdata = preg_replace_callback('/\[\[\$(.*?)\]\]/i', array($this, 'parse_variable'), $tagdata);
		
		return $tagdata;
	}
	
	/**
	* Parse a Variable
	*
	* Replaces a variable with variable data, optional uses simple modifiers/manipulation
	*
	* @param string $matches The variable is in key 1 of this array, can have modifier
	*                        like "{block:$test|trim}" or with param like "{block:$test|shorten(150)}"
	*						 or multiple arguments like "{block:$test|fake(1,sbc,2)}"
	*
	*
	* @return string $data
	*/
	function parse_variable ($matches) {
		$variable = $matches[1];
		
		// get variables
		$variables = $this->string_variables;
		
		// do we have a modifier
		if (strpos($variable,'|') !== FALSE) {
			list($variable, $modifier) = explode('|',$variable);
			
			// does the modifier have arguments?
			if (strpos($modifier,'(') !== FALSE) {
				list($modifier,$mod_argument) = explode('(', $modifier);
				
				// remove trailing ")"
				$mod_argument = substr_replace($mod_argument, '', -1, 1);
			}
		}
		else {
			$modifier = '';
		}
		
		// get data from vars
		$data = $variables[$variable];
		
		// manipulate?
		
		// modifier: shorten[length]
		
		if ($modifier == "shorten") {
			// we need one argument, string length
			$length = $mod_argument;
			
			$this->CI->load->helper('shorten');
			$data = shorten($data, $length);
		}
		
		// modifier: date_format[format]
		elseif ($modifier == "date_format") {
			$format = $mod_argument;
			
			// we'll take strftime or date formatting:
			if (strpos($format, '%') !== FALSE) {				
				$data = strftime($format, strtotime($data));
			}
			else {
				$data = date($format, strtotime($data));
			}
		}
		
		return $data;
	}
}
