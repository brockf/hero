<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Twitter Model 
*
* Contains all the methods used to auto-tweet
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Twitter_model extends CI_Model
{
	function __construct()
	{
		parent::CI_Model();
	}
	
	function hook_cron () {
		echo 'Beginning Twitter cron.<br />';
		
		if (setting('twitter_enabled') != '1') {
			echo 'Twitter is disabled.<br />';
			
			$this->settings_model->update_setting('twitter_last_tweet', date('Y-m-d H:i:s'));
			
			return FALSE;
		}
		
		if (setting('twitter_content_types') == '') {
			echo 'No content types eligible for tweeting.<br />';
			
			$this->settings_model->update_setting('twitter_last_tweet', date('Y-m-d H:i:s'));
			return FALSE;
		}
		
		// load libraries
		$CI =& get_instance();
		
		$CI->load->model('publish/content_model');
		require(APPPATH . 'modules/twitter/libraries/twitteroauth.php');
		
		// get last date this cron ran
		$last_update = (setting('twitter_last_tweet') == '') ? '2010-01-01' : setting('twitter_last_tweet');
		
		$types = unserialize(setting('twitter_content_types'));
		$topics = (setting('twitter_topics') == '') ? NULL : unserialize(setting('twitter_topics'));
		
		// if they have all topics...
		if (in_array(0, $topics)) {
			$topics = NULL;
		}
		
		foreach ($types as $type) {
			$contents = $CI->content_model->get_contents(array('type' => $type, 'topic' => $topics, 'start_date' => $last_update, 'limit' => '5'));
			
			if (!empty($contents)) {
				// flip so that the latest posts are tweeted last
				$contents = array_reverse($contents);
			
				foreach ($contents as $content) {
					if (!isset($connection)) {
						$connection = new TwitterOAuth(setting('twitter_consumer_key'), setting('twitter_consumer_secret'), setting('twitter_oauth_token'), setting('twitter_oauth_token_secret'));
						
						echo 'Connected to Twitter<br />';
					}
					
					// build $status
					$status = setting('twitter_template');
					
					$vars = array(
									'site_name' => setting('site_url'),
									'title' => $content['title'],
									'url' => $content['url']
								);
								
					foreach ($vars as $k => $v) {
						$status = str_ireplace('[' . $k . ']', $v, $status);
					}			
					
					echo 'Posting status update: ' . $content['title'] . '<br />';
					
					$result = $connection->post('statuses/update', array('status' => $status));
					
					if ($connection->http_code != 200) {
						echo 'Connection to Twitter failed.<br />';
						return FALSE;
					}
				}
			}
		}
		
		// update cron run
		$this->settings_model->update_setting('twitter_last_tweet', date('Y-m-d H:i:s'));
	}	
}