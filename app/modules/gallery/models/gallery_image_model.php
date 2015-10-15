<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Gallery Image Model
*
* Manages content
*
* @author Electric Function, Inc.
* @package Electric Framework
* @copyright Electric Function, Inc.
*/

class Gallery_image_model extends CI_Model
{
	private $cache;
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Get Images
	*
	* Retrieves gallery images into an array
	*
	* @param int $content_id
	*
	* @return array Of all gallery images, stored
	*/
	function get_images ($content_id) {
		$this->db->where('content_id',$content_id);
		$this->db->order_by('gallery_image_featured DESC, gallery_image_order ASC');
		$result = $this->db->get('gallery_images');
		
		$images = array();
		foreach ($result->result_array() as $image) {
			$images[] = array(
								'id' => $image['gallery_image_id'],
								'path' => setting('path_writeable') . 'gallery_images/' . $image['gallery_image_filename'],
								'url' => site_url(str_replace(FCPATH,'',setting('path_writeable') . 'gallery_images/' . $image['gallery_image_filename'])),
								'featured' => ($image['gallery_image_featured'] == '1') ? TRUE : FALSE
							);
		}
		
		return $images;
	}
	
	/**
	* Add Image
	* 
	* Adds an image to an image gallery
	*
	* @param int $content_id Gallery content ID
	* @param string $filename The filename of the file in /writeable/gallery_images/
	*
	* @return int $image_id
	*/
	function add_image ($content_id, $filename) {
		// get next order
		$this->db->where('content_id',$content_id);
		$this->db->order_by('gallery_image_order','DESC');
		$result = $this->db->get('gallery_images');
		
		if ($result->num_rows() > 0) {
			$last_field = $result->row_array();
			$order = $last_field['gallery_image_order'] + 1;
		}
		else {
			$order = '1';
		}
		
		$insert_fields = array(
							'content_id' => $content_id,
							'gallery_image_filename' => $filename,
							'gallery_image_order' => $order,
							'gallery_image_uploaded' => date('Y-m-d H:i:s')
							);
		
		$this->db->insert('gallery_images',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/**
	* Remove Image
	*
	* Removes an image from the gallery
	*
	* @param int $image_id
	*
	* @return void
	*/
	function remove_image ($image_id) {
		$this->db->select('content_id');
		$this->db->where('gallery_image_id',$image_id);
		$result = $this->db->get('gallery_images');
		
		$image = $result->row_array();
		if (!isset($image['content_id'])) {
			return FALSE;
		}
		$content_id = $image['content_id'];
	
		$this->db->delete('gallery_images',array('gallery_image_id' => $image_id));
		
		// replace feature image?
		$this->db->select('gallery_image_id');
		$this->db->where('content_id',$content_id);
		$this->db->where('gallery_image_featured','1');
		if ($this->db->get('gallery_images')->num_rows() == 0) {
			// there's no feature image
			$this->db->select('gallery_image_id');
			$this->db->where('content_id',$content_id);
			$this->db->order_by('gallery_image_order','ASC');
			$this->db->limit(1);
			$result = $this->db->get('gallery_images');
			if ($result->num_rows() == 0) {
				// no images to be a feature
				return;
			}
			else {
				$image = $result->row_array();
				$image_id = $image['gallery_image_id'];
				
				$this->db->update('gallery_images',array('gallery_image_featured' => '1'),array('gallery_image_id' => $image_id));
			}
		}
	}
	
	/**
	* Make Featured Image
	*
	* Makes this image the featured image
	*
	* @param int $image_id The image ID
	* 
	* @return boolean TRUE
	*/
	function make_feature_image ($image_id) {
		$this->db->where('gallery_image_id',$image_id);
		$result = $this->db->get('gallery_images');
		
		$image = $result->row_array();
		$content_id = $image['content_id'];
		
		// null all other features for this gallery
		$this->db->update('gallery_images',array('gallery_image_featured' => '0'),array('content_id' => $content_id));
		
		// make this the feature
		$this->db->update('gallery_images',array('gallery_image_featured' => '1'),array('gallery_image_id' => $image_id));
	
		$this->db->update('galleries',array('feature_image' => setting('path_writeable') . 'gallery_images/' . $image['gallery_image_filename']),array('content_id' => $content_id));
		
		return TRUE;
	}
	
	/**
	* Reset Order for Images
	*
	* @param int $content_id
	*
	*/
	function images_reset_order ($content_id) {
		$this->db->update('gallery_images',array('gallery_image_order' => '0'), array('content_id' => $content_id));
	}
	
	/**
	* Update Order
	*
	* @param int $field_id
	* @param int $new_order
	*
	* @return void
	*/
	function image_update_order ($image_id, $new_order) {
		$this->db->update('gallery_images',array('gallery_image_order' => $new_order), array('gallery_image_id' => $image_id));
	}
}