<?php
/**
* Jerrymail Cron Controller 
*
* Sends out the newsletter.  This should be run once-per-day. wget example.com/jerrymail/cron
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/
class Cron extends Front_Controller {
	function __construct ()
	{
		parent::__construct();
	}
	
	function index () {
		// do we have a post today?
		$this->load->model('publish/content_model');
		$contents = $this->content_model->get_contents(array(
									'start_date' => date('Y-m-d H:i:s', strtotime('24 hours ago')),
									'type' => '1'
								));
								
		if (!empty($contents)) {
			// we have an article published today
			// build the email from our email views
			
			// lead article
			$content = $this->content_model->get_contents(array('type' => '1', 'limit' => '1'));
			$lead_article = $content[0];
			
			// get promo
			$content = $this->content_model->get_contents(array('type' => '2', 'limit' => '1'));
			$promo = $content[0];
			
			
			$data = array(
					'lead_article' => $lead_article,
					'promo' => $promo
				);
			
			$email = $this->load->view('daily_newsletter', $data, TRUE);
			
			// send to everyone
			$this->load->model('newsletter_model');
			$this->newsletter_model->send($email, strip_tags($lead_article['title']));
		}
	}
}