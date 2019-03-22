<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Search Module
*
* Displays the search results (and likely a search form at the top of these results)
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Search extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function index () {
		// are we doing a search?
		$searching = FALSE;
		$query = FALSE;
		$pagination = FALSE;
		$results = FALSE;
		$num_results = FALSE;
		$title = 'Search ' . setting('site_name');
		
		if ($this->input->get('q', TRUE) != FALSE) {
			// have we waited long enough before searches?
			if (setting('search_delay') != 0 and $this->session->userdata('last_search') != FALSE and ((time() - $this->session->userdata('last_search')) < setting('search_delay'))) {
				die(show_error('You must wait ' . setting('search_delay') . ' seconds between searches.  <a href="javascript:location.reload(true)">Refresh and try again</a>.'));
			}
		
			$searching = TRUE;
			$query = $this->input->get('q', TRUE);
			
			$this->load->library('search/search_results');
			
			$page = ($this->input->get('page')) ? $this->input->get('page') : 0;
			$results = $this->search_results->search($query, $page);
			
			$num_results = $this->search_results->get_total_results();
			
			$pagination = $this->search_results->get_pagination(site_url('search/?q=' . urlencode($query)));
			
			$title = 'Search Results for "' . $query . '"';
			
			// record latest search time
			$this->session->set_userdata('last_search',time());
		}
				
		// display
		$this->smarty->assign('searching',$searching);
		$this->smarty->assign('query',$query);
		$this->smarty->assign('pagination',$pagination);
		$this->smarty->assign('num_results',$num_results);
		$this->smarty->assign('results',$results);
		$this->smarty->assign('title',$title);
		return $this->smarty->display('search.thtml');
	}
}