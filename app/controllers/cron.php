<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Cron controller
*
* The target URL for the 5-minute cronjob.  Any method/function can be bound to the cron hook
* and be executed with the cronjob.
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/

class Cron extends Front_Controller {
	function __construct () {
		parent::__construct();
	}

	function update ($key) {
		// give lots of time for processing
		set_time_limit(0);
		
		// if wget times out, or the user stops requesting, don't end the cron processing
		// http://stackoverflow.com/questions/2291524/does-wget-timeout
		ignore_user_abort(TRUE);
		
		if ($this->config->item('cron_key') != $key) {
			die('Invalid key.');
		}
		
		$this->load->helper('cron_log');
		
		cron_log('Cron processes triggered.');
		
		$this->app_hooks->trigger('cron');
		
		cron_log('Cron processes complete.');
		
		// update cron update setting
		if (setting('cron_last_update') === FALSE) {
			$this->settings_model->new_setting(1, 'cron_last_update', date('Y-m-d H:i:s'), 'When did the cron job last run?', 'text', '', FALSE, TRUE);
		}
		else {
			$this->settings_model->update_setting('cron_last_update', date('Y-m-d H:i:s'));
		}
	}
}