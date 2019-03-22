<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Blog Model 
*
* Contains all the methods used to create, update, and delete blogs.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Blog_model extends CI_Model
{
	var $blogs; // cache

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Create New Blog
	*
	* @param int $content_type_id Each blog displays content of only one type, specified here
 	* @param string $title Blog title
 	* @param string $url_path
 	* @param string $description Blog description
 	* @param array $filter_author The user ID(s) to filter by (default: array())
 	* @param array $filter_topic The topic ID(s) to filter by (default: array())
 	* @param string $summary_field The column name to use for the summary (default: FALSE)
 	* @param string $sort_field The column name to sort by (default: FALSE)
 	* @param string $sort_dir Sort direction (default: FALSE)
 	* @param boolean $auto_trim Should we auto trim the summary field in listings? (default: FALSE)
 	* @param string $template The filename of the template in the theme directory to use for output (default: blog.html)
 	* @param int $per_page How many items to show per page? (default: 25)
 	* @param array $privileges array of member group ID's (default: array())
 	*
 	* @return int $blog_id
 	*/
	function new_blog ($content_type_id, $title, $url_path, $description, $filter_author = array(), $filter_topic = array(), $summary_field = FALSE, $sort_field = FALSE, $sort_dir = FALSE, $auto_trim = TRUE, $template = 'blog.thtml', $per_page = 25, $privileges = array()) {
		$this->load->helper('clean_string');
		$url_path = (empty($url_path)) ? clean_string($title) : $url_path;
		
		$this->load->model('link_model');
		$link_id = $this->link_model->new_link($url_path, FALSE, $title, 'Blog/Listing', 'blogs', 'blog', 'view');
		
		$insert_fields = array(
							'link_id' => $link_id,
							'content_type_id' => $content_type_id,
							'blog_title' => $title,
							'blog_description' => $description,
							'blog_filter_author' => (is_array($filter_author) and !empty($filter_author)) ? serialize($filter_author) : '',
							'blog_filter_topic' => (is_array($filter_topic) and !empty($filter_topic)) ? serialize($filter_topic) : '',
							'blog_summary_field' => (!empty($summary_field)) ? $summary_field : '',
							'blog_sort_field' => (!empty($sort_field)) ? $sort_field : '',
							'blog_sort_dir' => (!empty($sort_dir)) ? $sort_dir : '',
							'blog_auto_trim' => ($auto_trim == TRUE) ? '1' : '0',
							'blog_privileges' => (is_array($privileges) and !in_array(0, $privileges)) ? serialize($privileges) : '',
							'blog_template' => $template,
							'blog_per_page' => $per_page
							);
							
		$this->db->insert('blogs',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Update Blog
	*
	* @param int $blog_id
	* @param int $content_type_id Each blog displays content of only one type, specified here
 	* @param string $title Blog title
 	* @param string $url_path
 	* @param string $description Blog description
 	* @param array $filter_author The user ID(s) to filter by (default: array())
 	* @param array $filter_topic The topic ID(s) to filter by (default: array())
 	* @param string $summary_field The column name to use for the summary (default: FALSE)
 	* @param string $sort_field The column name to sort by (default: FALSE)
 	* @param string $sort_dir Sort direction (default: FALSE)
 	* @param boolean $auto_trim Should we auto trim the summary field in listings? (default: FALSE)
 	* @param string $template The filename of the template in the theme directory to use for output (default: blog.html)
 	* @param int $per_page How many items to show per page? (default: 25)
 	* @param array $privileges array of member group ID's (default: array())
 	*
 	* @return boolean
 	*/
	function update_blog ($blog_id, $content_type_id, $title, $url_path, $description, $filter_author = array(), $filter_topic = array(), $summary_field = FALSE, $sort_field = FALSE, $sort_dir = FALSE, $auto_trim = TRUE, $template = 'blog.thtml', $per_page = 25, $privileges = array()) {
		$blog = $this->get_blog($blog_id);
		
		$this->load->model('link_model');
		
		if (empty($url_path)) {
			$this->load->helper('url_string');
			$url_path = clean_string($title);
		}
		
		if ($url_path != $blog['url_path']) {
			$url_path = $this->link_model->prep_url_path($url_path);
			$url_path = $this->link_model->get_unique_url_path($url_path);
			$this->link_model->update_url($blog['link_id'], $url_path);
		}
		$this->link_model->update_title($blog['link_id'], $title);
	
		$update_fields = array(
							'content_type_id' => $content_type_id,
							'blog_title' => $title,
							'blog_description' => $description,
							'blog_filter_author' => (is_array($filter_author) and !empty($filter_author)) ? serialize($filter_author) : '',
							'blog_filter_topic' => (is_array($filter_topic) and !empty($filter_topic)) ? serialize($filter_topic) : '',
							'blog_summary_field' => (!empty($summary_field)) ? $summary_field : '',
							'blog_sort_field' => (!empty($sort_field)) ? $sort_field : '',
							'blog_sort_dir' => (!empty($sort_dir)) ? $sort_dir : '',
							'blog_auto_trim' => ($auto_trim == TRUE) ? '1' : '0',
							'blog_privileges' => (is_array($privileges) and !in_array(0, $privileges)) ? serialize($privileges) : '',
							'blog_template' => $template,
							'blog_per_page' => $per_page
							);
							
		$this->db->update('blogs',$update_fields,array('blog_id' => $blog_id));
		
		return TRUE;
	}
	
	/**
	* Delete Blog
	*
	* @param int $blog_id
	*
	* @return boolean TRUE
	*/
	function delete_blog ($blog_id) {
		$blog = $this->get_blog($blog_id);
		
		$this->db->delete('blogs',array('blog_id' => $blog_id));
		
		$this->load->model('link_model');
		$this->link_model->delete_link($blog['link_id']);
		
		return TRUE;
	}
	
	/**
	* Get Blog ID
	*
	* Returns blog ID from a URL_path
	*
	* @param string $url_path
	* 
	* @return int $blog_id
	*/
	function get_blog_id($url_path) {
		$this->db->select('blog_id');
		$this->db->where('link_url_path',$url_path);
		$this->db->join('links','blogs.link_id = links.link_id','inner');
		$result = $this->db->get('blogs');
		
		if ($result->num_rows() == FALSE) {
			return FALSE;
		}
		
		$blog = $result->row_array();
		
		return $blog['blog_id'];
	}
	
	/**
	* Get Blog Content
	*
	* Retrieves content for a specific blog
	*
	* @param int $blog_id
	* @param int $page (default: 0)
	*
	* @return array Array of content else FALSE
	*/
	function get_blog_content ($blog_id, $page = 0) {
		$blog = $this->get_blog($blog_id);
		
		if (empty($blog)) {
			return FALSE;
		}
		
		// prepare filters for get_contents
		$filters = array();
		$filters['type'] = $blog['type'];
		
		// filter by author?
		if ($blog['filter_authors'] !== FALSE) {
			$filters['author'] = $blog['filter_authors'];
		}
		
		// filter by topic?
		if ($blog['filter_topics'] !== FALSE) {
			$filters['topic'] = $blog['filter_topics'];
		}
		
		if (!empty($blog['sort_field'])) {
			$filters['sort'] = $blog['sort_field'];
			$filters['sort_dir'] = $blog['sort_dir'];
		}
		
		if (!empty($page)) {
			$filters['offset'] = (int)$page;
			$filters['offset'] = ($filters['offset'] < 0) ? 0 : $filters['offset'];
		}
		
		$filters['limit'] = $blog['per_page'];
		
		// get content
		$CI =& get_instance();
		$CI->load->model('publish/content_model');
		$CI->load->helper('shorten');
		$contents = $CI->content_model->get_contents($filters);
		
		if (empty($contents)) {
			return FALSE;
		}
		
		foreach ($contents as $key => $content) {
			// prep summary
			$blog['summary_field'] = str_replace('content_','',$blog['summary_field']);
			$contents[$key]['summary'] = ($blog['auto_trim'] == TRUE) ? shorten($content[$blog['summary_field']], setting('blog_summary_length'), TRUE) : $content[$blog['summary_field']];
		}
		
		return $contents;
	}
	
	/**
	* Get Blog Pagination
	*
	* Generates Pagination HTML for a blog when given the current page
	*
	* @param int $blog_id
	* @param string $base_url The full URL to the blog on which pagination will be built
	* @param int $page (default: 0)
	*
	* @return string HTML
	*/
	function get_blog_pagination($blog_id, $base_url, $page = 0) {
		$blog = $this->get_blog($blog_id);
		
		if (empty($blog)) {
			return FALSE;
		}
		
		// do we need to append the "?" for the query string?
		if (strpos($base_url, '?') === FALSE) {
			$base_url .= '?';
		}

		$this->load->library('pagination');
		
		// get total rows
		$this->db->select('content.content_id');
		$this->db->where('content_type_id',$blog['type']);
		
		// filter by author?
		if ($blog['filter_authors'] !== FALSE) {
			$this->db->where_in('user_id', $blog['filter_authors']);
		}
		
		// filter by topic?
		if ($blog['filter_topics'] !== FALSE) {
			$this->db->where_in('topic_id', $blog['filter_topics']);
		}
		
		$this->db->join('topic_maps','topic_maps.content_id = content.content_id', 'left');
		$this->db->group_by('content.content_id');
		
		$result = $this->db->get('content');
		$total_rows = $result->num_rows();

		$config['base_url'] = $base_url;
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $blog['per_page'];
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'page';
		
		$this->pagination->initialize($config);
		
		$links = $this->pagination->create_links();

		// we may have cases of ?& because of CodeIgniter thinking we have universally enabled query strings
		$links = str_replace('?&amp;','?', $links);
		
		return $links;
	}
	
	/**
	* Get Blog
	*
	* @param int $blog_id
	*
	* @return array Blog details
	*/
	function get_blog ($blog_id) {
		// available in cache?
		if (!isset($this->blogs[$blog_id])) {
			$blog = $this->get_blogs(array('id' => $blog_id));
			
			// set cache
			$this->blogs[$blog_id] = $blog;
		}
		else {
			$blog = $this->blogs[$blog_id];
		}
		
		if (empty($blog)) {
			return FALSE;
		}
		
		return $blog[0];
	}
	
	/**
	* Get Blogs
	*
	* @param int $filters['id']
	* @param int $filters['type']
	* @param string $filters['title']
	*
	* @return array Blogs
	*/
	function get_blogs ($filters = array()) {
		if (isset($filters['id'])) {
			$this->db->where('blog_id',$filters['id']);
		}
		if (isset($filters['type'])) {
			$this->db->where('content_types.content_type_id',$filters['type']);
		}
		if (isset($filters['title'])) {
			$this->db->like('blog_title',$filters['title']);
		}
	
		$this->db->order_by('blog_title');
		$this->db->join('content_types','content_types.content_type_id = blogs.content_type_id','left');
		$this->db->join('links','links.link_id = blogs.link_id','left');
		$result = $this->db->get('blogs');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$blogs = array();
		foreach ($result->result_array() as $row) {
			$blogs[] = array(
						'id' => $row['blog_id'],
						'link_id' => $row['link_id'],
						'title' => $row['blog_title'],
						'description' => $row['blog_description'],
						'filter_authors' => (!empty($row['blog_filter_author'])) ? unserialize($row['blog_filter_author']) : FALSE,
						'filter_topics' => (!empty($row['blog_filter_topic'])) ? unserialize($row['blog_filter_topic']) : FALSE,
						'type' => $row['content_type_id'],
						'type_name' => $row['content_type_friendly_name'],
						'summary_field' => (!empty($row['blog_summary_field'])) ? $row['blog_summary_field'] : FALSE,
						'url' => site_url($row['link_url_path']),
						'url_path' => $row['link_url_path'],
						'auto_trim' => ($row['blog_auto_trim'] == '1') ? TRUE : FALSE,
						'template' => $row['blog_template'],
						'sort_field' => (!empty($row['blog_sort_field'])) ? $row['blog_sort_field'] : FALSE,
						'sort_dir' => (!empty($row['blog_sort_dir'])) ? $row['blog_sort_dir'] : FALSE,
						'privileges' => (!empty($row['blog_privileges'])) ? unserialize($row['blog_privileges']) : FALSE,
						'per_page' => $row['blog_per_page']
					);
		}
		
		return $blogs;
	}
}