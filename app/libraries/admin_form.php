<?php

/**
* Admin Form Library
*
* This class will generate a proper administration panel form
* through a series of element methods like "text" and "select".
* It also can parse a standard custom_fields array from the
* custom_fields_model.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/
class Admin_form {
	var $fields;
	var $fieldset;
	var $fieldsets;
	
	function __construct () {
		// field data
		$this->fields = array();
		// fieldset legends
		$this->fieldsets = array();
		// current fieldset ID
		$this->fieldset = 0;
	}
	
	/**
	* Add Value Row
	*
	* Adds a row that is just a <label></label> Value row
	*
	* @param string $label What to put in the <label>
	* @param string $value What to put as the value
	* @param boolean $full
	*
	* @return void 
	*/
	function value_row ($label, $value, $full = FALSE) {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
		
		$CI =& get_instance();
		
		$CI->load->helper('clean_string_helper');
		$name = clean_string($label);
	
		$this->fields[$this->fieldset][] = array(
											'type' => 'value_row',
											'label' => $label,
											'value' => $value,
											'full' => $full,
											'name' => $name
											);
	}
	
	/**
	* Add Hidden Field
	*
	* @param string $name
	* @param string $value
	*
	* @return void 
	*/
	function hidden ($name, $value) {
		$this->fields[$this->fieldset][] = array(
												'type' => 'hidden',
												'name' => $name,
												'value' => $value
											);
	}
	
	/**
	* Add Text Field
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $value Current value of the field (default: '')
	* @param string $help A piece of help text (default: FALSE)
	* @param boolean $required Is the field required for submission? (default: FALSE)
	* @param boolean $mark_empty Should the field have an "empty" default value? (default: FALSE)
	* @param boolean $full Should the field take up the entire pane? (default: FALSE)
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%") (default: 250px)
	* @param string $li_id The ID of the LI element containing the field (default: '')
	* @param array $classes Additional classes to apply to the element (default: FALSE)
	* 
	* @return void 
	*/
	function text ($label, $name, $value = '', $help = FALSE, $required = FALSE, $mark_empty = FALSE, $full = FALSE, $width = '250px', $li_id = '', $classes = FALSE) {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
	
		$this->fields[$this->fieldset][] = array(
								'type' => 'text',
								'name' => $name,
								'label' => $label,
								'value' => $value,
								'width' => $width,
								'help' => $help,
								'required' => $required,
								'mark_empty' => $mark_empty,
								'full' => $full,
								'li_id' => $li_id,
								'classes' => $classes
							);
	}
	
	/**
	* Add Password Field
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $help A piece of help text (default: FALSE)
	* @param boolean $required Is the field required for submission? (default: FALSE)
	* @param boolean $full Should the field take up the entire pane? (default: FALSE)
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%") (default: 250px)
	* @param string $li_id The ID of the LI element containing the field (default: '')
	*
	* @return void
	*/
	function password ($label, $name, $help = FALSE, $required = FALSE, $full = FALSE, $width = '250px', $li_id = '') {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
	
		$this->fields[$this->fieldset][] = array(
								'type' => 'password',
								'name' => $name,
								'label' => $label,
								'width' => $width,
								'help' => $help,
								'required' => $required,
								'full' => $full,
								'li_id' => $li_id
							);
	}
	
	/**
	* Add Names Fields
	*
	* Adds a horizontal First Name / Last Name field with names "first_name" and "last_name"
	*
	* @param string $label The human friendly form label
	* @param string $first_value Value of first name
	* @param string $last_value Value of last name
	* @param string $help A piece of help text (default: FALSE)
	* @param boolean $required Is the field required for submission? (default: FALSE)
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%") (default: 250px)
	* @param string $li_id The ID of the LI element containing the field (default: '')
	*
	* @return void 
	*/
	function names ($label, $first_value, $last_value, $help = FALSE, $required = FALSE, $width = '250px', $li_id = '') {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
	
		$this->fields[$this->fieldset][] = array(
								'type' => 'names',
								'label' => $label,
								'first_value' => $first_value,
								'last_value' => $last_value,
								'width' => $width,
								'help' => $help,
								'required' => $required,
								'li_id' => $li_id
							);
	}
	
	/**
	* Add Textarea Field
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $value Current value of the field (default: '')
	* @param string $help A piece of help text (default: FALSE)
	* @param boolean $required Is the field required for submission? (default: FALSE)
	* @param boolean|string $wysiwyg FALSE for no WYSIWYG, or the name of the toolbar class to load with ckEditor (e.g., "complete", "basic") (default: FALSE)
	* @param boolean $full Should the field take up the entire pane? (default: FALSE)
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%") (default: 300px)
	* @param string $height The complete style:height element definition (default: 100px)
	* @param string $li_id The ID of the LI element containing the field (default: '')
	*
	* @return void 
	*/
	function textarea ($label, $name, $value = '', $help = FALSE, $required = FALSE, $wysiwyg = FALSE, $full = FALSE, $width = '300px', $height = '150px', $li_id = '') {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
	
		$this->fields[$this->fieldset][] = array(
								'type' => 'textarea',
								'name' => $name,
								'label' => $label,
								'value' => $value,
								'width' => $width,
								'help' => $help,
								'height' => $height,
								'required' => $required,
								'wysiwyg' => $wysiwyg,
								'full' => $full,
								'li_id' => $li_id
							);
	}
	
	/**
	* Add Select Dropdown
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param array $options The select options in the form of array(value1 => display1, value2 => display2)
	* @param string|array $selected The selected value(s).  A string for selects, an array for multiselects (default: FALSE)
	* @param boolean $multiselect Is this a multiselect? (default: FALSE)
	* @param boolean $required Is the field required for submission? (default: FALSE)
	* @param string $help A piece of help text (default: FALSE)
	* @param boolean $full Should the field take up the entire pane? (default: FALSE)
	* @param string $li_id The ID of the LI element containing the field (default: '')
	*
	* @return void 
	*/
	function dropdown ($label, $name, $options, $selected = FALSE, $multiselect = FALSE, $required = FALSE, $help = FALSE, $full = FALSE, $li_id = '') {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
		
		if ($multiselect == TRUE and !is_array($selected)) {
			//show_error($name . ': This is a multiselect field but was passed a string for $selected.');
			$selected = array();
		}
		
		if ($multiselect == TRUE and substr($name, -2, 2) != '[]') {
			$name .= '[]';
		}
	
		$this->fields[$this->fieldset][] = array(
								'type' => 'dropdown',
								'multiselect' => $multiselect,
								'required' => $required,
								'options' => $options,
								'name' => $name,
								'label' => $label,
								'value' => $selected,
								'help' => $help,
								'full' => $full,
								'li_id' => $li_id
							);
	}
	
	/**
	* Add Radio Buttons
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param array $options The select options in the form of array(value1 => display1, value2 => display2)
	* @param string $selected The selected value
	* @param boolean $required Is the field required for submission? (default: FALSE)
	* @param string $help A piece of help text (default: FALSE)
	* @param boolean $full Should the field take up the entire pane? (default: FALSE)
	* @param string $li_id The ID of the LI element containing the field (default: '')
	*
	* @return void 
	*/
	function radio ($label, $name, $options, $selected, $required = FALSE, $help = FALSE, $full = FALSE, $li_id = '') {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
		
		$this->fields[$this->fieldset][] = array(
								'type' => 'radio',
								'required' => $required,
								'options' => $options,
								'name' => $name,
								'label' => $label,
								'value' => $selected,
								'help' => $help,
								'full' => $full,
								'li_id' => $li_id
							);
	}
	
	/**
	* Add Checkbox
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $value The value of the field, when checked
	* @param boolean $checked TRUE to check the box (default: FALSE)
	* @param string $help A piece of help text (default: FALSE)
	* @param boolean $full Should the field take up the entire pane? (default: FALSE)
	* @param string $li_id The ID of the LI element containing the field (default: '')
	*
	* @return void 
	*/
	function checkbox ($label, $name, $value, $checked = FALSE, $help = FALSE, $full = FALSE, $li_id = '') {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
		
		$this->fields[$this->fieldset][] = array(
								'type' => 'checkbox',
								'name' => $name,
								'label' => $label,
								'value' => $value,
								'checked' => $checked,
								'help' => $help,
								'full' => $full,
								'li_id' => $li_id
							);
	}
	
	/**
	* Add File Upload
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%") (default: 250px)
	* @param string $full Should the field take up the entire pane? (default: FALSE)
	* @param string $li_id The ID of the LI element containing the field (default: '')
	*
	* @return void
	*/
	function file ($label, $name, $width = '250px', $full = FALSE, $li_id = '') {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
		
		$this->fields[$this->fieldset][] = array(
											'type' => 'file',
											'label' => $label,
											'name' => $name,
											'width' => $width,
											'full' => $full,
											'li_id' => $li_id
										);
	}
	
	/**
	* Add Date Field
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $value Current value of the field
	* @param string $help A piece of help text (default: FALSE)
	* @param boolean $required Is the field required for submission? (default: FALSE)
	* @param boolean $mark_empty Should the field have an "empty" default value? (default: FALSE)
	* @param boolean $full Should the field take up the entire pane? (default: FALSE)
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%") (default: 250px)
	* @param string $li_id The ID of the LI element containing the field (default: '')
	* @param array $classes Additional classes to apply to the element (default: FALSE)
	*
	* @return void
	*/
	function date ($label, $name, $value, $help = FALSE, $required = FALSE, $mark_empty = FALSE, $full = FALSE, $width = '250px', $li_id = '', $classes = FALSE) {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
	
		$this->fields[$this->fieldset][] = array(
								'type' => 'date',
								'name' => $name,
								'label' => $label,
								'value' => $value,
								'width' => $width,
								'help' => $help,
								'required' => $required,
								'mark_empty' => $mark_empty,
								'full' => $full,
								'li_id' => $li_id,
								'classes' => $classes
							);
	}
	
	/**
	* Add New Fieldset
	*
	* Adds a fieldset to the form, breaking up the form
	*
	* @param string $legend The fieldset legend (default: '')
	* @param array $ul_classes (default: FALSE)
	*
	* @return void 
	*/
	function fieldset($legend = '', $ul_classes = array()) {
		$this->fieldset++;
		
		$this->fieldsets[$this->fieldset] = array(
												'legend' => $legend,
												'ul_classes' => $ul_classes
												);
	}
	
	/**
	* Import Custom Fields
	*
	* Imports a standard array of custom fields from the custom_fields_model into the form
	*
	* @param array $custom_fields The standard custom_fields array generated from get_custom_fields() or equivalent method
	* @param array $values The array of current values, if there are any (e.g, when editing) (default: FALSE)
	* @param boolean $no_defaults Do not use default values for empty fields (i.e., when editing an existing record) (default: FALSE)
	*
	* @return void
	*/
	function custom_fields ($custom_fields = array(), $values = array(), $no_defaults = FALSE) {
		$CI =& get_instance();
		$CI->load->library('custom_fields/fieldtype');
		
		if (!is_array($custom_fields) or empty($custom_fields)) {
			return FALSE;
		}
	
		foreach ($custom_fields as $field) {
			$CI->load->library('custom_fields/fieldtype');
			$field_object =& $CI->fieldtype->load($field);
			
			// set value
			if (!empty($values) and isset($values[$field_object->name])) {
				$field_object->value($values[$field_object->name]);
			}
			
			// clear default?
			if ($no_defaults == TRUE) {
				$field_object->default_value(FALSE);
			}
			
			// get HTML
			
			// This line was written here and I don't know why.  Commenting it out shows help fields in the admin...
			// $field_object->help(FALSE);
			$html = $field_object->output_admin();
			unset($field_object);
			
			$this->fields[$this->fieldset][] = array(
														'type' => 'custom',
														'li_id' => $field['id'],
														'html' => $html
													);
		}
		
		return;
	}
	
	/**
	* Display the form
	*
	* @return string Form HTML
	*/
	function display () {
		$return = '';
		
		for ($i = 1; $i <= $this->fieldset; $i++) {
			$return .= '<fieldset>';
			
			if (!empty($this->fieldsets[$i]['legend'])) {
				$return .= '<legend>' . $this->fieldsets[$i]['legend'] . '</legend>';
			}
			
			$classes = array();
			$classes[] = 'form';
			$classes = array_merge($classes,$this->fieldsets[$i]['ul_classes']);
			
			$return .= '<ul class="' . implode(' ',$classes) . '">';
			
			foreach ($this->fields[$i] as $field) {
				if ($field['type'] == 'hidden') {
					$return .= '<input type="hidden" id="' . $field['name'] . '" name="' . $field['name'] . '" value="' . $field['value'] . '" />';
				}
				elseif ($field['type'] == 'custom') {
					if (isset($field['li_id'])) {
						$field['html'] = str_replace('<li','<li id="row_' . $field['li_id'] . '"',$field['html']);
					}
					
					$return .= $field['html'];
				}
				else {
					$field['li_id'] = (isset($field['li_id'])) ? $field['li_id'] : $field['name'];
				
					$return .= '<li id="row_' . $field['li_id'] . '">';
					
					// label
					$classes = array();
					if (isset($field['full']) and $field['full'] == TRUE) {
						$classes[] = 'full';
					}
					$class = implode(' ',$classes);
					
					// names fields don't have names
					if ($field['type'] == 'names') {
						$field['name'] = 'first_name';
					}
					
					$return .= '<label class="' . $class . '" for="' . $field['name'] . '">' . $field['label'] . '</label>';
					
					// create new line?
					if (isset($field['full']) and $field['full'] == TRUE) {
						$return .= '</li><li>';
					}
					
					// text fields
					if ($field['type'] == 'text') {
						$classes = array('text');
						$rel = '';
						$placeholder = '';
						
						if ($field['required'] == TRUE) {
							$classes[] = 'required';
						}
						
						if ($field['full'] == TRUE) {
							$classes[] = 'full';
							$field['width'] = '100%';
						}
						
						if ($field['mark_empty'] != FALSE) {
							$placeholder = $field['mark_empty'];
						}
						
						if (is_array($field['classes'])) {
							$classes = array_merge($classes,$field['classes']);
						}
						
						$return .= '<input type="text" class="' . implode(' ',$classes) . '" placeholder="' . $placeholder . '" style="width:' . $field['width'] . '" name="' . $field['name'] . '" rel="' . $rel . '" id="' . $field['name'] . '" value="' . $field['value'] . '" />';
					}
					// password fields
					elseif ($field['type'] == 'password') {
						$classes = array('text');
						$rel = '';
						
						if ($field['required'] == TRUE) {
							$classes[] = 'required';
						}
						
						if ($field['full'] == TRUE) {
							$classes[] = 'full';
							$field['width'] = '100%';
						}
						
						$return .= '<input type="password" class="' . implode(' ',$classes) . '" style="width:' . $field['width'] . '" rel="' . $rel . '" name="' . $field['name'] . '" id="' . $field['name'] . '" value="" />';
					}
					// dropdowns
					elseif ($field['type'] == 'dropdown') {
						$classes = array();
						$rel = '';
						
						if ($field['required'] == TRUE) {
							$classes[] = 'required';
						}
						
						if ($field['full'] == TRUE) {
							$classes[] = 'full';
						}
						
						$multiple = ($field['multiselect'] == TRUE) ? ' multiple="multiple"' : '';
						
						$return .= '<select name="' . $field['name'] . '" class="' . implode(' ',$classes) . '" ' . $multiple . '>';
						
						foreach ($field['options'] as $value => $option) {
							$selected = '';
							if ($multiple == TRUE and in_array($value, $field['value'])) {
								$selected = ' selected="selected"';
							}
							elseif ($multiple == FALSE and $value == $field['value']) {
								$selected = ' selected="selected"';
							}
						
							$return .= '<option value="' . $value . '"' . $selected. '>' . $option . '</option>';
						}
						
						$return .= '</select>';
					}
					// names
					elseif ($field['type'] == 'names') {
						$classes = array();
						$rel = '';
						
						if ($field['required'] == TRUE) {
							$classes[] = 'required';
						}
						
						$classes[] = 'mark_empty';
						
						$return .= '<input type="text" class="' . implode(' ',$classes) . '" style="width:' . $field['width'] . '" rel="First Name" id="first_name" name="first_name" value="' . $field['first_value'] . '" />&nbsp;&nbsp;<input type="text" class="' . implode(' ',$classes) . '" style="width:' . $field['width'] . '" rel="Last Name" id="last_name" name="last_name" value="' . $field['last_value'] . '" />';
					}
					// textarea fields
					elseif ($field['type'] == 'textarea') {
						$classes = array('text');
						
						if ($field['required'] == TRUE) {
							$classes[] = 'required';
						}
						
						if ($field['full'] == TRUE) {
							$classes[] = 'full';
							$field['width'] = '100%';
							
						}
						
						if ($field['wysiwyg'] != FALSE) {
							$classes[] = 'wysiwyg';
							$classes[] = $field['wysiwyg']; // the toolbar set
							
							if (!defined('INCLUDE_CKEDITOR')) {
								define('INCLUDE_CKEDITOR','TRUE');
							}
						}
						
						if ($field['full'] != TRUE) {
							$return .= '<div style="float:left;width:' . ((int)$field['width'] + 20) . 'px">';
						}
						
						$return .= '<textarea class="' . implode(' ',$classes) . '" style="width:' . $field['width'] . '" name="' . $field['name'] . '" id="' . $field['name'] . '">' . $field['value'] . '</textarea>';
						
						if ($field['full'] != TRUE) {
							$return .= '</div>';
						}
					}
					// radio
					elseif ($field['type'] == 'radio') {
						$classes = array();
						
						if ($field['required'] == TRUE) {
							$classes[] = 'required';
						}
						
						foreach ($field['options'] as $value => $option) {
							$selected = '';
							if ($value == $field['value']) {
								$selected = ' checked="checked"';
							}
						
							$return .= '<input type="radio" id="' . $field['name'] . '" name="' . $field['name'] . '" class="' . implode(' ',$classes) . '" value="' . $value . '"' . $selected. ' />&nbsp;' . $option . '&nbsp;&nbsp;&nbsp;';
						}
					}
					// checkbox
					elseif ($field['type'] == 'checkbox') {
						$classes = array();
						
						$checked = ($field['checked'] == TRUE) ? ' checked="checked"' : '';
					
						$return .= '<input type="checkbox" id="' . $field['name'] . '" name="' . $field['name'] . '" class="' . implode(' ',$classes) . '" value="' . $field['value'] . '"' . $checked. ' />';
					}
					// file
					elseif ($field['type'] == 'file') {
						$return .= '<input type="file" id="' . $field['name'] . '" name="' . $field['name'] . '" />';
					}
					// date
					elseif ($field['type'] == 'date') {
						if (!defined('INCLUDE_DATEPICKER')) {
							define('INCLUDE_DATEPICKER','TRUE');
						}
						
						$classes = array('text','datepick');
						$rel = '';
						$placeholder = '';
						
						if ($field['required'] == TRUE) {
							$classes[] = 'required';
						}
						
						if ($field['full'] == TRUE) {
							$classes[] = 'full';
							$field['width'] = '100%';
						}
						
						if ($field['mark_empty'] != FALSE) {
							$placeholder = $field['mark_empty'];
						}
						
						if (is_array($field['classes'])) {
							$classes = array_merge($classes,$field['classes']);
						}
						
						$return .= '<input type="text" class="' . implode(' ',$classes) . '" placeholder="' . $placeholder . '" style="width:' . $field['width'] . '" name="' . $field['name'] . '" rel="' . $rel . '" id="' . $field['name'] . '" value="' . $field['value'] . '" />';
					}
					elseif ($field['type'] == 'value_row') {
						$return .= $field['value'];
					}
					
					$return .= '</li>';
					
					// help
					if (!empty($field['help'])) {
						$style = ($field['full'] == TRUE) ? 'style="margin-left:0"' : '';
					
						$return .= '</li>
									<li>
										<div class="help" ' . $style. '>' . $field['help'] . '</div>
									</li>';
					}
				}
			}
			
			$return .= '</ul>';
			
			$return .= '</fieldset>';
		}
		
		return $return;
	}
}