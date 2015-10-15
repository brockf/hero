<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Twitter Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->admin_navigation->parent_active('configuration');
	}
	
	function index() {
		// we'll show varying levels of blank state screens
		
		// do we have a consumer key and secret?
		if (setting('twitter_consumer_key') == '' or setting('twitter_consumer_secret') == '') {
			return redirect('admincp/twitter/register_application');
		}
		
		// we have an app setup, but we may need the OAuth token...
		
		if (setting('twitter_oauth_token') == '' or setting('twitter_oauth_token_secret') == '') {
			return redirect('admincp/twitter/update_oauth');
		}
		
		// we have oauth tokens, but let's make sure they are up-to-date
		require(APPPATH . 'modules/twitter/libraries/twitteroauth.php');
		$connection = new TwitterOAuth(setting('twitter_consumer_key'), setting('twitter_consumer_secret'), setting('twitter_oauth_token'), setting('twitter_oauth_token_secret'));
		
		$test = $connection->get('account/verify_credentials');
		
		if ($connection->http_code != 200) {
			// connection failed
			$this->notices->SetError('Your Twitter OAuth tokens are out of date!  Please re-authorize.');
			
			return redirect('admincp/twitter/update_oauth');
		}
		
		// Twitter credentials are solid!
		
		$this->admin_navigation->module_link('Update Application Credentials',site_url('admincp/twitter/register_application'));
		$this->admin_navigation->module_link('Update Authorization Tokens',site_url('admincp/twitter/update_oauth'));
		
		$this->load->library('admin_form');
		$form = new Admin_form;
		$form->fieldset('Twitter Configuration');
		
		// get content types
		$this->load->model('publish/content_type_model');
		$types = $this->content_type_model->get_content_types(array('is_standard' => '1'));
		$type_options = array();
		foreach ($types as $type) {
			$type_options[$type['id']] = $type['name'];
		}
		
		// get topics
		$this->load->model('publish/topic_model');
		$topics = $this->topic_model->get_tiered_topics();
		$topic_options = array();
		$topic_options[0] = 'Any Topic';
		foreach ($topics as $topic) {
			$topic_options[$topic['id']] = $topic['name'];
		}
		
		$form->value_row('&nbsp;','Configure ' . setting('app_name') . ' to automatically tweet your latest content with the options below.');
	
		$checked = (setting('twitter_enabled') == '1') ? TRUE : FALSE;
		$form->checkbox('Enable Tweeting?','enabled', '1', $checked);
	
		$selected = (setting('twitter_content_types') != '') ? unserialize(setting('twitter_content_types')) : array();
		$form->dropdown('Content Types','content_types', $type_options, $selected, TRUE, FALSE, 'Only posts of these content types will be tweeted.');
		
		$selected = (setting('twitter_topics') != '') ? unserialize(setting('twitter_topics')) : array();
		$form->dropdown('Topics','topics', $topic_options, $selected, TRUE, FALSE, 'Only posts in these topics will be tweeted.');
		
		//$value = (setting('twitter_template') == '') ? '[TITLE]: [URL]' : setting('twitter_template');
		//$form->textarea('Tweet Template','template',$value,'Specify the format of the Tweet.  You may use the tags: [SITE_NAME], [TITLE], and [URL].');
		
		$data = array(
					'form_title' => 'Twitter: Configuration',
					'form_action' => site_url('admincp/twitter/post_config'),
					'form' => $form->display(),
					'form_button' => 'Save Configuration'
				);
	
		$this->load->view('generic', $data);
	}
	
	function post_config () {
		$this->settings_model->update_setting('twitter_content_types',serialize($_POST['content_types']));
		$this->settings_model->update_setting('twitter_topics',serialize($_POST['topics']));
		$this->settings_model->update_setting('twitter_enabled', $this->input->post('enabled'));
		//$this->settings_model->update_setting('twitter_template', $this->input->post('template'));
		
		$this->notices->SetNotice('Twitter configuration saved.');
		
		redirect('admincp/twitter');
	}
	
	function oauth_callback () {
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
	
	function post_update_oauth () {
		require(APPPATH . 'modules/twitter/libraries/twitteroauth.php');
		
		$connection = new TwitterOAuth(setting('twitter_consumer_key'), setting('twitter_consumer_secret'));
 
		$request_token = $connection->getRequestToken(site_url('admincp/twitter/oauth_callback'));
		
		/* Save temporary credentials to session. */
		$this->session->set_userdata('twitter_oauth_token', $request_token['oauth_token']);
		$this->session->set_userdata('twitter_oauth_token_secret', $request_token['oauth_token_secret']);
		 
		/* If last connection failed don't display authorization link. */
		switch ($connection->http_code) {
		  case 200:
		    /* Build authorize URL and redirect user to Twitter. */
		    $url = $connection->getAuthorizeURL($request_token['oauth_token']);
		    header('Location: ' . $url); 
		    break;
		  default:
		    die(show_error('Could not connect to Twitter. Refresh the page or try again later.'));
		}
	}
	
	function update_oauth () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Introduction');
		$form->value_row('&nbsp;','<div style="float:left; width: 600px">Your OAuth credentials are either non-existent or out-of-date.  These are
								   required for ' . setting('app_name') . ' to have permission to post on your behalf in your Twitter account.</div>');
								   
		$form->value_row('&nbsp;','Click the button below to authorize ' . setting('app_name') . ' to post on your behalf.  No Tweets will be
								   sent out until you select which content to post and how often to do so.');
		
		$data = array(
					'form_title' => 'Twitter: Update Your Authorization',
					'form_action' => site_url('admincp/twitter/post_update_oauth'),
					'form' => $form->display(),
					'form_button' => 'Authorize ' . setting('app_name') . ' Now!'
				);
	
		$this->load->view('generic', $data);
	}
	
	function post_register_application () {
		$this->load->library('form_validation');
		$this->form_validation->set_rules('consumer_key','Consumer Key','required');
		$this->form_validation->set_rules('consumer_secret','Consumer Secret','required');
		
		if ($this->form_validation->run() === FALSE) {
			$this->notices->SetError(validation_errors());
			
			return redirect('admincp/register_application');
		}
		else {
			$this->settings_model->update_setting('twitter_consumer_key', $this->input->post('consumer_key'));
			$this->settings_model->update_setting('twitter_consumer_secret', $this->input->post('consumer_secret'));
			
			$this->notices->SetNotice('Twitter application registered successfully.');
			
			return redirect('admincp/twitter');
		}
	}
	
	function register_application () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Introduction');
		$form->value_row('&nbsp;','<div style="float:left; width: 600px">Before you connect your ' . setting('app_name') . ' installation to Twitter, you must register an
								   application at Twitter.  This sounds more complex than it really is:  All you have to do is go and
								   complete a form telling them about your website and you\'ll instantly receive a Consumer Key and a
								   Consumer Secret that you will then enter below.<br /><br />
								   <b>Be sure to specify that your app is a BROWSER app and enter a Callback URL of "' . site_url() . '".</b></div>');
								   
		$form->value_row('&nbsp;','<a href="http://dev.twitter.com/apps/new" target="_blank">Click here to register your application at Twitter</a>.');
		
		$form->text('Consumer Key','consumer_key');
		$form->text('Consumer Secret','consumer_secret');
	
		$data = array(
					'form_title' => 'Twitter: Register Your Application',
					'form_action' => site_url('admincp/twitter/post_register_application'),
					'form' => $form->display(),
					'form_button' => 'Save Application Credentials'
				);
	
		$this->load->view('generic', $data);
	}
}