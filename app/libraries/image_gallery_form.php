<?php

/*
* Image Gallery Form
*
* This library helps generate an image uploader and gallery arranger
* for an administration panel form.
*
* Files are accessible in $_FILES['name'][]
*
*/
class Image_gallery_form {
	var $label; // field label
	var $name; // field name
	var $folder; // folder to store images in
	var $ajax; // will we be using AJAX to upload files simultaneously? (not if we are in a larger form)
	
	public function label ($label) {
		$this->label = htmlspecialchars($label);
	}
	
	public function name ($name) {
		$this->name = $name;
	}
	
	public function folder ($folder) {
		$this->folder = $folder;
	}
	
	function ajax ($ajax = FALSE) {
		$this->ajax = $ajax;
	}
	
	function display () {
		$CI =& get_instance();
		$CI->load->library('Admin_form');
		$form = new Admin_form;
		$form->fieldset($this->label);
		
		if ($this->ajax == FALSE) {
			$form->value_row($this->label, '<div class="image_gallery_form" id="' . $this->name . '_box" rel="' . $this->folder . '">
												<div class="uploader">
													<input type="file" name="' . $this->name . '_image[]" rel="1" />&nbsp;<input type="button" id="' . $this->name . '_add" class="image_gallery_form button" name="' . $this->name . '_add" value="&#43; Upload Image to Gallery" />
												</div>
												<div class="images">
													<span id="no_images">No images have been uploaded.</span>
													<ul>
													
													</ul>
												</div>
											</div>');
		}
		
		return $form->display();
	}
}