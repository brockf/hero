<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Twitter OAuth Callback Module
*
* Retrieves callbacks from OAuthorization attempts
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Oauth_callback extends Admincp_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function index() {
		require(APPPATH . 'modules/twitter/libraries/twitteroauth.php');
		
		/* If the oauth_token is old redirect to the connect page. */
		if ($this->input->get('oauth_token') && $this->session->userdata('twitter_oauth_token') !== $this->input->get('oauth_token')) {
			return redirect('admincp/twitter');
		}
		
		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		$connection = new TwitterOAuth(setting('twitter_consumer_key'), setting('twitter_consumer_secret'), $this->session->userdata('twitter_oauth_token'), $this->session->userdata('twitter_oauth_token_secret'));
		
		/* Request access tokens from twitter */
		$access_token = $connection->getAccessToken($this->input->get('oauth_verifier'));
		
		/* Save the access tokens. Normally these would be saved in a database for future use. */
		$this->settings_model->update_setting('twitter_oauth_token', $access_token['oauth_token']);
		$this->settings_model->update_setting('twitter_oauth_token_secret', $access_token['oauth_token_secret']);
		
		/* Remove no longer needed request tokens */
		$this->session->unset_userdata('twitter_oauth_token');
		$this->session->unset_userdata('twitter_oauth_token_secret');
		
		/* If HTTP response is 200 continue otherwise send to connect page to retry */
		if (200 == $connection->http_code) {
			$this->notices->SetNotice('OAuthorization retrieved successfully.');
			return redirect('admincp/twitter');
		} else {
		 	$this->notices->SetError('Error making connection in OAuth callback.');
			return redirect('admincp/twitter');
		}
	}
}
