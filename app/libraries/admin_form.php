<?php

/*
* Admin Form Library
*
* This class will generate a proper administration panel form
* through a series of element methods like "text" and "select".
* It also can parse a standard custom_fields array from the
* custom_fields_model.
*
* @author Electric Function, Inc.
* @package Electric Publisher
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
	
	/*
	* Add Text Field
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $value Current value of the field
	* @param string $help A piece of help text
	* @param boolean $required Is the field required for submission?
	* @param boolean $mark_empty Should the field have an "empty" default value?
	* @param boolean $full Should the field take up the entire pane?
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%")
	*/
	function text ($label, $name, $value, $help = FALSE, $required = FALSE, $mark_empty = FALSE, $full = FALSE, $width = '250px') {
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
								'full' => $full
							);
	}
	
	/*
	* Add Password Field
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $help A piece of help text
	* @param boolean $required Is the field required for submission?
	* @param boolean $full Should the field take up the entire pane?
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%")
	*/
	function password ($label, $name, $help = FALSE, $required = FALSE, $full = FALSE, $width = '250px') {
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
								'full' => $full
							);
	}
	
	/*
	* Add Names Fields
	*
	* Adds a horizontal First Name / Last Name field with names "first_name" and "last_name"
	*
	* @param string $label The human friendly form label
	* @param string $first_value Value of first name
	* @param string $last_value Value of last name
	* @param string $help A piece of help text
	* @param boolean $required Is the field required for submission?
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%")
	*/
	function names ($label, $first_value, $last_value, $help = FALSE, $required = FALSE, $width = '250px') {
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
								'required' => $required
							);
	}
	
	/*
	* Add Textarea Field
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $value Current value of the field
	* @param string $help A piece of help text
	* @param boolean $required Is the field required for submission?
	* @param boolean|string FALSE for no WYSIWYG, 'mini' for a lite WYSIWYG editor, and 'full' for a full-featured WYSIWYG editor
	* @param boolean $full Should the field take up the entire pane?
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%")
	* @param string $height The complete style:height element definition
	*/
	function textarea ($label, $name, $value, $help = FALSE, $required = FALSE, $wysiwyg = FALSE, $full = FALSE, $width = '300px', $height = '150px') {
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
								'required' => $required,
								'wysiwyg' => $wysiwyg,
								'full' => $full
							);
	}
	
	/*
	* Add Select Dropdown
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param array $options The select options in the form of array(value1 => display1, value2 => display2)
	* @param string|array $selected The selected value(s).  A string for selects, an array for multiselects
	* @param boolean $required Is the field required for submission?
	* @param string $help A piece of help text
	* @param boolean $full Should the field take up the entire pane?
	*/
	function dropdown ($label, $name, $options, $selected, $multiselect = FALSE, $required = FALSE, $help = FALSE, $full = FALSE) {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
		
		if ($multiselect == TRUE and !is_array($selected)) {
			show_error($name . ': This is a multiselect field but was passed a string for $selected.');
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
								'full' => $full
							);
	}
	
	/*
	* Add Radio Buttons
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param array $options The select options in the form of array(value1 => display1, value2 => display2)
	* @param string $selected The selected value
	* @param boolean $required Is the field required for submission?
	* @param string $help A piece of help text
	* @param boolean $full Should the field take up the entire pane?
	*/
	function radio ($label, $name, $options, $selected, $required = FALSE, $help = FALSE, $full = FALSE) {
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
								'full' => $full
							);
	}
	
	/*
	* Add Checkbox
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $value The value of the field, when checked
	* @param boolean $checked TRUE to check the box
	* @param string $help A piece of help text
	* @param boolean $full Should the field take up the entire pane?
	*/
	function checkbox ($label, $name, $value, $checked = FALSE, $help = FALSE, $full = FALSE) {
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
								'full' => $full
							);
	}
	
	/*
	* Add File Upload
	*
	* @param string $label The human friendly form label
	* @param string $name Field name
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%")
	*/
	function file ($label, $name, $width = '250px') {
		if ($this->fieldset == 0) {
			show_error('You must create a fieldset before adding fields.');
		}
		
		$this->fields[$this->fieldset][] = array(
											'type' => 'file',
											'label' => $label,
											'name' => $name,
											'width' => $width
										);
	}
	
	/*
	* Add New Fieldset
	*
	* Adds a fieldset to the form, breaking up the form
	*
	* @param string $legend The fieldset legend
	*/
	function fieldset($legend = '') {
		$this->fieldset++;
		
		$this->fieldsets[$this->fieldset] = array(
												'legend' => $legend
												);
	}
	
	/*
	* Import Custom Fields
	*
	* Imports a standard array of custom fields from the custom_fields_model into the form
	*
	* @param array $custom_fields The standard custom_fields array generated from get_custom_fields() or equivalent method
	* @param array $values The array of current values, if there are any (e.g, when editing)
	* @param boolean $no_defaults Do not use default values for empty fields (i.e., when editing an existing record)
	*/
	function custom_fields ($custom_fields = array(), $values = array(), $no_defaults = FALSE) {
		if (!is_array($custom_fields) or empty($custom_fields)) {
			return FALSE;
		}
	
		foreach ($custom_fields as $field) {
			if ($field['type'] == 'text') {
				$value = (isset($values[$field['name']])) ? $values[$field['name']] : '';		
				
				if (empty($value) and $no_defaults == FALSE) {
					$value = $field['default'];
				}
				
				$this->text($field['friendly_name'], $field['name'], $value, $field['help'], $field['required'], FALSE, FALSE, $field['width']);
			}
			elseif ($field['type'] == 'password') {
				$this->password($field['friendly_name'], $field['name'], $field['help'], $field['required'], FALSE, $field['width']);
			}
			elseif ($field['type'] == 'textarea') {
				$value = (isset($values[$field['name']])) ? $values[$field['name']] : '';			
				
				if (empty($value) and $no_defaults == FALSE) {
					$value = $field['default'];
				}
					
				$this->textarea($field['friendly_name'], $field['name'], $value, $field['help'], $field['required'], FALSE, FALSE, $field['width']);
			}
			elseif ($field['type'] == 'wysiwyg') {
				$value = (isset($values[$field['name']])) ? $values[$field['name']] : '';		
				
				if (empty($value) and $no_defaults == FALSE) {
					$value = $field['default'];
				}
				
				$this->textarea($field['friendly_name'], $field['name'], $value, $field['help'], $field['required'], 'mini', TRUE, $field['width']);
			}
			elseif ($field['type'] == 'select') {
				$value = (isset($values[$field['name']])) ? $values[$field['name']] : '';		
				
				if (empty($value) and $no_defaults == FALSE) {
					$value = $field['default'];
				}
				
				$options = array();
				foreach ($field['options'] as $option) {
					$options[$option['value']] = $option['name'];
				}
				
				$this->dropdown($field['friendly_name'], $field['name'], $options, $value, FALSE, $field['required'], $field['help']);
			}
			elseif ($field['type'] == 'multiselect') {
				$value = (isset($values[$field['name']]) and is_array($values[$field['name']])) ? $values[$field['name']] : array();
				
				if (empty($value) and $no_defaults == FALSE) {
					$value = array($field['default']);
				}
				
				$options = array();
				foreach ($field['options'] as $option) {
					$options[$option['value']] = $option['name'];
				}
				
				$this->dropdown($field['friendly_name'], $field['name'], $options, $value, TRUE, $field['required'], $field['help']);
			}
			elseif ($field['type'] == 'radio') {
				$value = (isset($values[$field['name']])) ? $values[$field['name']] : '';
				
				if (empty($value) and $no_defaults == FALSE) {
					$value = $field['default'];
				}
				
				$options = array();
				foreach ($field['options'] as $option) {
					$options[$option['value']] = $option['name'];
				}
				
				$this->radio($field['friendly_name'], $field['name'], $options, $value, $field['required'], $field['help']);
			}
			elseif ($field['type'] == 'checkbox') {
				$value = (isset($values[$field['name']])) ? $values[$field['name']] : '';
				
				if (empty($value) and $no_defaults == FALSE) {
					$value = $field['default'];
				}
				
				$options = array();
				foreach ($field['options'] as $option) {
					$options[$option['value']] = $option['name'];
				}
				
				$this->checkbox($field['friendly_name'], $field['name'], 'yes', $field['required'], $field['help']);
			}
			elseif ($field['type'] == 'file') {
				$this->file($field['friendly_name'], $field['name'], $field['width']);
			}
		}
	}
	
	/*
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
			
			$return .= '<ul class="form">';
			
			foreach ($this->fields[$i] as $field) {
				$return .= '<li>';
				
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
					
					if ($field['required'] == TRUE) {
						$classes[] = 'required';
					}
					
					if ($field['full'] == TRUE) {
						$classes[] = 'full';
						$field['width'] = '100%';
					}
					
					if ($field['mark_empty'] != FALSE) {
						$classes[] = 'mark_empty';
						$rel = $field['mark_empty'];
					}
					
					$return .= '<input type="text" class="' . implode(' ',$classes) . '" style="width:' . $field['width'] . '" name="' . $field['name'] . '" rel="' . $rel . '" id="' . $field['name'] . '" value="' . $field['value'] . '" />';
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
					
					if ($field['wysiwyg'] == 'mini') {
						$classes[] = 'wysiwyg';
					}
					
					$return .= '<textarea class="' . implode(' ',$classes) . '" style="width:' . $field['width'] . '" name="' . $field['name'] . '" id="' . $field['name'] . '">' . $field['value'] . '</textarea>';
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
				
					$return .= '<input type="checkbox" name="' . $field['name'] . '" class="' . implode(' ',$classes) . '" value="' . $value . '"' . $checked. ' />';
				}
				// file
				elseif ($field['type'] == 'file') {
					$return .= '<input type="file" name="' . $field['name'] . '" />';
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
			
			$return .= '</ul>';
			
			$return .= '</fieldset>';
		}
		
		return $return;
	}
}