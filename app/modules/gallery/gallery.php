<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Gallery Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Gallery_module extends Module {
	var $version = '1.03';
	var $name = 'gallery';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	/*
	* Pre-admin function
	*
	* Initiate navigation in control panel
	*/
	function admin_preload ()
	{
		$this->CI->admin_navigation->child_link('publish',29,'&bull; Manage Galleries',site_url('admincp/gallery'));
	}
	
	function update ($db_version) {
		if ($db_version < 1.0) {
			$this->CI->db->query('CREATE TABLE `gallery_images` (
 								 `gallery_image_id` int(11) NOT NULL auto_increment,
 								 `content_id` int(11) NOT NULL,
 								 `gallery_image_filename` varchar(250) NOT NULL,
  								 `gallery_image_featured` tinyint(1) NOT NULL,
  								 `gallery_image_order` int(5) NOT NULL,
  								 `gallery_image_uploaded` DATETIME,
  								 `gallery_title` varchar(255) NOT NULL,
 								 `gallery_caption` text,
  								 PRIMARY KEY  (`gallery_image_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');		
							
			// create gallery content type								 
			$this->CI->load->model('publish/content_type_model');
			$gallery_content_type_id = $this->CI->content_type_model->new_content_type('Galleries', TRUE, TRUE, FALSE, 'gallery.thtml');
			
			$this->CI->settings_model->new_setting(1, 'gallery_content_type_id', $gallery_content_type_id, '', 'text', '', FALSE, TRUE);
			
			// create "description" custom field
			$content_type = $this->CI->content_type_model->get_content_type($gallery_content_type_id);

			$this->CI->load->model('custom_fields_model');
			$this->CI->custom_fields_model->new_custom_field($content_type['custom_field_group_id'], 'Description', 'wysiwyg', FALSE, FALSE, '650px', FALSE, TRUE, FALSE, $content_type['system_name']);						 
		}
		
		if ($db_version < 1.01) {
			$path = $this->CI->config->item('path_writeable') . 'gallery_images';
			$this->CI->settings_model->make_writeable_folder($path);
		}
		
		if ($db_version < 1.02) {
			$this->CI->db->update('content_types', array('content_type_is_module' => '1'), array('content_type_id' => $this->CI->config->item('gallery_content_type_id')));
		}
		
		if ($db_version < 1.03) {
			$content_type = $this->CI->db->where('content_type_system_name','galleries')
									     ->get('content_types')
									     ->row_array();
			
			$this->CI->load->model('custom_fields_model');						     
			$this->CI->custom_fields_model->new_custom_field($content_type['custom_field_group_id'], 'Feature Image', 'text', FALSE, FALSE, '650px', 'Leave blank - this will be set automatically', FALSE, FALSE, $content_type['content_type_system_name']);						 
		}
		
		return $this->version;
	}
}