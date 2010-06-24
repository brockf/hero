<?php

/*
* Image Gallery
*
* This library helps generate an image uploader and gallery arranger
* that is independent, with thumbnails and previews.
*
* Files are accessible in $_FILES['name'][]
*
*/
class Image_gallery {
	var $label; // field label
	var $name; // field name
	var $processor; // function that processes upload and displays gallery
	
	public function label ($label) {
		$this->label = htmlspecialchars($label);
	}
	
	public function name ($name) {
		$this->name = $name;
	}
	
	public function processor ($processor) {
		$this->processor = $processor;
	}
	
	function display () {
		$return = '<form method="post" enctype="multipart/form-data" action="' . $this->processor . '">';
		$return .= '<iframe id="upload_target" name="upload_target" src="" style="width:0;height:0;border:0px solid #fff;"></iframe>';
		$return .= '<div class="image_gallery" id="' . $this->name . '_box">
						<div class="uploader">
							<input type="file" name="' . $this->name . '_image[]" rel="1" />&nbsp;<input type="submit" id="' . $this->name . '_add" class="image_gallery_form button" name="' . $this->name . '_add" value="&#43; Upload Image to Gallery" />
						</div>
						<div class="images">
							<span id="no_images">No images have been uploaded.</span>
							<ul>
							
							</ul>
						</div>
					</div>';
		$return .= '</form>';
		
		return $return;
	}
}