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
	
	/**
	* Get Tweets
	*
	* @param int $filters['id'] The tweet ID to select
	* @param string $filters['tweet'] Search by tweet
	* @param date $filters['start_date'] Select after this tweet date
	* @param date $filters['end_date'] Select before this tweet date
	* @param string $filters['sort'] Field to sort by
	* @param string $filters['sort_dir'] ASC or DESC
	* @param int $filters['limit'] How many records to retrieve
	* @param int $filters['offset'] Start records retrieval at this record
	* @param boolean $counting Should we just count the number of tweets that match the filters? (default: FALSE)
	*
	* @return array Each tweet in an array of tweets
	*/
	function get_tweets ($filters = array(), $counting = FALSE) {
		// filters
		if (isset($filters['id'])) {
			$this->db->where('tweet_id',$filters['id']);
		}

		if (isset($filters['tweet'])) {
			$this->db->like('tweet',$filters['tweet']);
		}

		if (isset($filters['start_date'])) {
			$date = date('Y-m-d H:i:s', strtotime($filters['start_date']));
			$this->db->where('sent_time >=', $date);
		}

		if (isset($filters['end_date'])) {
			$date = date('Y-m-d H:i:s', strtotime($filters['end_date']));
			$this->db->where('sent_time <=', $date);
		}

		// standard ordering and limiting
		if ($counting == FALSE) {
			$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'tweet_id';
			$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
			$this->db->order_by($order_by, $order_dir);

			if (isset($filters['limit'])) {
				$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
				$this->db->limit($filters['limit'], $offset);
			}
		}

		if ($counting === TRUE) {
			$this->db->select('tweet_id');
			$result = $this->db->get('tweets_sent');
			$rows = $result->num_rows();
			$result->free_result();
			return $rows;
		}
		else {
			$this->db->from('tweets_sent');

			$result = $this->db->get();
		}

		if ($result->num_rows() == 0) {
			return FALSE;
		}

		// get custom fields
		$CI =& get_instance();
		$CI->load->model('custom_fields_model');
		$custom_fields = $CI->custom_fields_model->get_custom_fields(array('group' => '1'));

		return $result->result_array();
	}
}