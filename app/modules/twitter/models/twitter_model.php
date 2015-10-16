<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Twitter Model 
*
* Contains all the methods used to auto-tweet
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Twitter_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function hook_cron () {
		cron_log('Beginning Twitter cronjob.');
		
		if (setting('twitter_enabled') != '1') {
			cron_log('Twitter is disabled.  Exiting.');
			
			$this->settings_model->update_setting('twitter_last_tweet', date('Y-m-d H:i:s'));
			
			return FALSE;
		}
		
		if (setting('twitter_content_types') == '') {
			cron_log('No content types have been configured for tweeting.  Exiting.');
			
			$this->settings_model->update_setting('twitter_last_tweet', date('Y-m-d H:i:s'));
			return FALSE;
		}
		
		// load libraries
		$CI =& get_instance();
		
		$CI->load->model('publish/content_model');
		require(APPPATH . 'modules/twitter/libraries/twitteroauth.php');
		
		// only process last hour
		$start_date = date('Y-m-d', strtotime('now - 1 hour'));
		
		$types = unserialize(setting('twitter_content_types'));
		$topics = (setting('twitter_topics') == '') ? NULL : unserialize(setting('twitter_topics'));
		
		// if they have all topics...
		if ((is_int($topics) && $topics = 0) || (is_array($topics) && in_array(0, $topics))) {
			$topics = NULL;
		}
		
		foreach ($types as $type) {
			$filter = array(
				'type' => $type
				,'start_date' => $start_date
				,'limit' => '50'
			);
			
			if ((is_int($topics) && $topics != 0) || (is_array($topics) && !in_array(0, $topics))) {
				$filter['topics'] = $topics;
			}
			
			$contents = $CI->content_model->get_contents($filter);
			
			
			if (!empty($contents)) {
				// flip so that the latest posts are tweeted last
				$contents = array_reverse($contents);
				
				foreach ($contents as $content) {
					// have we already tweeted this?
					if (
						$this->db->select('link_id')
								->from('links')
								->where('link_module','twitter')
								->where('link_parameter',$content['link_id'])
								->get()
								->num_rows() > 0
						) {
						
						continue;
					}
					
					if (!isset($connection)) {
						$connection = new TwitterOAuth(setting('twitter_consumer_key'), setting('twitter_consumer_secret'), setting('twitter_oauth_token'), setting('twitter_oauth_token_secret'));
						
						cron_log('Connected to Twitter via OAuth.');
					}
					
					// build $status
					
					// shorten URL
					$CI->load->model('link_model');
					$CI->load->helper('string');
					$string = random_string('alnum',5);
					
					// make sure it's unique
					$url_path = $CI->link_model->get_unique_url_path($string);
					
					$url = site_url($url_path);
					
					$this->load->model('twitter/bitly_model','bitly');
					$bitlyUrl = $this->bitly->shorten_url($url);
					if($bitlyUrl){
						$url = $bitlyUrl;
					}
					
					// start with URL
					$status = $url;
					
					// how many characters remain?
					$chars_remain = 140 - strlen($status);
					
					// shorten title to fit before link
					$CI->load->helper('shorten');
					$shortened_title = shorten($content['title'], ($chars_remain - 5), FALSE);
					$shortened_title = str_replace('&hellip','...',$shortened_title);
					
					$status = $shortened_title . ' ' . $status;
					
					// insert into links table
					$CI->link_model->new_link($url_path, FALSE, $content['title'], 'Twitter Link', 'twitter', 'twitter', 'redirect', $content['link_id']);
					
					//insert tweet content into tweets_sent
					$this->twitter_log($status, $content['id'], $type);
					
					cron_log('Posting status: ' . $status);
					
					$result = $connection->post('statuses/update', array('status' => $status));
					
					if ($connection->http_code != 200) {
						cron_log('Connection to Twitter failed.  Exiting.');
						return FALSE;
					}
				}
			}
		}
		
		// update cron run
		$this->settings_model->update_setting('twitter_last_tweet', date('Y-m-d H:i:s'));
		
		return TRUE;
	}

	/**
	 * Post the twitter status update into the db to keep a log of it
	 */
	public function twitter_log($tweet, $content_id, $content_type){
		$data = array(
			'tweet' => $tweet
			,'content_id' => $content_id
			,'content_type' => $content_type
			,'sent_time' => date('Y-m-d H:i:s', time())
		);
		$this->db->insert('tweets_sent', $data);
	}
}