<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Blog Model 
*
* Contains all the methods used to create, update, and delete blogs.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Blog_model extends CI_Model
{
	function __construct()
	{
		parent::CI_Model();
	}
	
	/*
	* Create New Blog
	* @param int $content_type_id
 	* @param string $title Blog title
 	* @param string $url_path
 	* @param string $description Blog description
 	* @param array $filter_author The user ID(s) to filter by
 	* @param array $filter_topic The topic ID(s) to filter by
 	* @param string $summary_field The column name to use for the summary
 	* @param boolean $auto_trim Should we auto trim the summary field in listings?
 	*
 	* @return $blog_id
 	*/
	function new_blog ($content_type_id, $title, $url_path, $description, $filter_author = array(), $filter_topic = array(), $summary_field = FALSE, $auto_trim = TRUE) {
		$this->load->helper('clean_string');
		$url_path = (empty($url_path)) ? clean_string($title) : clean_string($url_path);
		
		$this->load->model('link_model');
		$url_path = $this->link_model->get_unique_url_path($url_path);
		$link_id = $this->link_model->new_link($url_path, 'blog', 'blog', 'view');
		
		$insert_fields = array(
							'link_id' => $link_id,
							'content_type_id' => $content_type_id,
							'blog_title' => $title,
							'blog_description' => $description,
							'blog_filter_author' => (is_array($filter_author) and !empty($filter_author)) ? serialize($filter_author) : '',
							'blog_filter_topic' => (is_array($filter_topic) and !empty($filter_topic)) ? serialize($filter_topic) : '',
							'blog_summary_field' => (!empty($summary_field)) ? $summary_field : '',
							'blog_auto_trim' => ($auto_trim == TRUE) ? '1' : '0'
							);
							
		$this->db->insert('blogs',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/*
	* Update Blog
	*
	* @param int $blog_id
	* @param int $content_type_id
 	* @param string $title Blog title
 	* @param string $url_path
 	* @param string $description Blog description
 	* @param array $filter_author The user ID(s) to filter by
 	* @param array $filter_topic The topic ID(s) to filter by
 	* @param string $summary_field The column name to use for the summary
 	* @param boolean $auto_trim Should we auto trim the summary field in listings?
 	*
 	* @return TRUE
 	*/
	function update_blog ($blog_id, $content_type_id, $title, $url_path, $description, $filter_author = array(), $filter_topic = array(), $summary_field = FALSE, $auto_trim = TRUE) {
		$blog = $this->get_blog($blog_id);
		
		if ($url_path != $blog['url_path']) {
			$this->load->helper('clean_string');
			$url_path = clean_string($url_path);
			
			$this->load->model('link_model');
			$url_path = $this->link_model->get_unique_url_path($url_path);
		}
	
		$update_fields = array(
							'content_type_id' => $content_type_id,
							'blog_title' => $title,
							'blog_description' => $description,
							'blog_filter_author' => (is_array($filter_author) and !empty($filter_author)) ? serialize($filter_author) : '',
							'blog_filter_topic' => (is_array($filter_topic) and !empty($filter_topic)) ? serialize($filter_topic) : '',
							'blog_summary_field' => (!empty($summary_field)) ? $summary_field : '',
							'blog_auto_trim' => ($auto_trim == TRUE) ? '1' : '0'
							);
							
		$this->db->update('blogs',$update_fields,array('blog_id' => $blog_id));
		
		return TRUE;
	}
	
	/*
	* Delete Blog
	*
	* @param int $blog_id
	*
	* @return boolean TRUE
	*/
	function delete_blog ($blog_id) {
		$blog = $this->get_blog($blog_id);
	
		$this->db->delete('blogs',array('blog_id' => $blog_id));
		$this->db->delete('links',array('link_id' => $blog['link_id']));
		
		return TRUE;
	}
	
	/*
	* Get Blog
	*
	* @param int $blog_id
	*
	* @return array
	*/
	function get_blog ($blog_id) {
		$blog = $this->get_blogs(array('id' => $blog_id));
		
		if (empty($blog)) {
			return FALSE;
		}
		
		return $blog[0];
	}
	
	/*
	* Get Blogs
	* @param int $filters['id']
	* @param int $filters['type']
	* @param string $filters['title']
	*
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
						'auto_trim' => ($row['blog_auto_trim'] == '1') ? TRUE : FALSE
					);
		}
		
		return $blogs;
	}
}