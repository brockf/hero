<?php

/*
* File Upload Fieldtype
*
* @extends Fieldtype
* @class File_upload_fieldtype
*/

class File_upload_fieldtype extends Fieldtype {
	public $upload_directory;

	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'File Upload';
		$this->fieldtype_description = 'Upload a file.';
		$this->validation_error = '';
		$this->db_column = 'VARCHAR(150)';
		
		// configuration
		$this->upload_directory = setting('path_custom_field_uploads');
	}
	
	function output_shared () {
		// set defaults
		if ($this->width == FALSE) {
			$this->width = '275px';
		}
		
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		$this->field_class('file');
		$this->field_class('text');
		
		// add validator names to class list
		foreach ($this->validators as $validator) {
			$this->field_class($validator);
		}
		
		// prep final attributes	
		$attributes = array(
						'type' => 'file',
						'name' => $this->name,
						'style' => 'width: ' . $this->width,
						'class' => implode(' ', $this->field_classes)
						);
		
		// compile attributes
		$attributes = $this->compile_attributes($attributes);
		
		return $attributes;
	}
	
	function output_admin () {
		$attributes = $this->output_shared();
		
		$help = ($this->help == FALSE) ? '' : '<div class="help">' . $this->help . '</div>';
		
		// build HTML
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>
						<input ' . $attributes . ' /> ' . $this->value . '
						' . $help . '
					</li>';
					
		return $return;
	}
	
	function output_frontend () {
		$attributes = $this->output_shared();
		
		// build HTML
		$return = '<input ' . $attributes . ' />';
					
		return $return;
	}
	
	function validation_rules () {
		return array();
	}
	
	function validate_post () {
		$this->CI->load->helper('file_extension');
		
		if (isset($this->data['filetypes']) and !empty($this->data['filetypes'])) {
			if (is_uploaded_file($_FILES[$this->name]['tmp_name']) and !in_array(file_extension($_FILES[$this->name]['name']),$this->data['filetypes'])) {
				$this->validation_error = $this->label . ' is not of the proper filetype.';
			
				return FALSE;
			}
		}
	
		// nothing extra to validate here other than the rulers in $this->validators
		return TRUE;
	}
	
	function post_to_value () {
		if (isset($_FILES[$this->name]) and is_uploaded_file($_FILES[$this->name]['tmp_name'])) {
			$this->CI->settings_model->make_writeable_folder($this->upload_directory,FALSE);
			
			$config = array();
			$config['upload_path'] = $this->upload_directory;
			$config['allowed_types'] = '*';
			
			// only encrypt the name if this is a frontend form
			if (defined("_FRONTEND")) {
				$config['encrypt_name'] = TRUE;
			}
			
			// upload class may already be loaded
			if (isset($this->CI->upload)) {
				$this->CI->upload->initialize($config);
			}
			else {
				$this->CI->load->library('upload', $config);
			}
			
			// do upload
			if (!$this->CI->upload->do_upload($this->name)) {
				die(show_error($this->CI->upload->display_errors()));
			}
			
			$filename = $this->CI->upload->file_name;
			
			// reset filename in case we use the uploader again
			$this->CI->upload->file_name = '';
			
			$post_value = str_replace(FCPATH,'',$this->CI->upload_directory . $filename);
		}
		
		return $post_value;
	}
	
	function field_form () {
		// build fieldset with admin_form which is used when editing a field of this type
	}
	
	function field_form_process () {
		// build array for database
	}
}