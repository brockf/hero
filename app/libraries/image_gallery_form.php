<?php

/*
* Image Gallery Form
*
* This library helps generate an image uploader
* for an administration panel form.
*
* Files are accessible in $_FILES['name'][]
*
*/
class Image_gallery_form {
	var $label; // field label
	var $name; // field name
	var $show_upload_button;
	
	public function label ($label) {
		$this->label = htmlspecialchars($label);
		
		$show_upload_button = FALSE;
	}
	
	public function name ($name) {
		$this->name = $name;
	}
	
	public function show_upload_button ($show_upload_button) {
		$this->show_upload_button = $show_upload_button;
	}
	
	function display () {
		$CI =& get_instance();
		$CI->load->library('Admin_form');
		$form = new Admin_form;
		$form->fieldset($this->label);
		
		$upload_now = ($this->show_upload_button == TRUE) ? '&nbsp;<input type="submit" id="' . $this->name . '_upload" class="button" name="' . $this->name . '_upload" value="Upload Images Now" />' : '';
		
		$form->value_row($this->label, '<div class="image_gallery_form" id="' . $this->name . '_box">
											<div class="uploader">
												<input type="file" name="' . $this->name . '_image[]" rel="1" />&nbsp;<input type="button" id="' . $this->name . '_add" class="image_gallery_form button" name="' . $this->name . '_add" value="&#43; Add Image to Upload Queue" />' . $upload_now . '
											</div>
											<div class="images">
												<span id="no_images">No images have been selected for upload.</span>
												<ul>
												
												</ul>
											</div>
										</div>');
		
		return $form->display();
	}
}