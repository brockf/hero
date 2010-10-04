<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends Front_Controller {
	function __construct () {
		parent::__construct();
	}

	function update ($key) {
		// give lots of time for processing
		set_time_limit(500);
		
		if ($this->config->item('cron_key') != $key) {
			return 'Invalid key.';
		}
		
		$this->app_hooks->trigger('cron');
		
		// update cron update setting
		if (setting('cron_last_update') === FALSE) {
			$this->settings_model->new_setting(1, 'cron_last_update', date('Y-m-d H:i:s'), 'When did the cron job last run?', 'text', '', FALSE, TRUE);
		}
		else {
			$this->settings_model->update_setting('cron_last_update', date('Y-m-d H:i:s'));
		}
	}
}