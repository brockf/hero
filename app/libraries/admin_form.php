<?php

class Admin_form {
	var $fields;
	var $fieldset;
	var $fieldsets;
	
	function __construct () {
		$this->fields = array();
		$this->fieldsets = array();
		
		$this->fieldset = 0;
	}
	
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
	* WYSIWYG can be either FALSE, mini, or full
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
	
	function fieldset($legend = '') {
		$this->fieldset++;
		
		$this->fieldsets[$this->fieldset] = array(
												'legend' => $legend
												);
	}
	
	function custom_fields ($custom_fields = array(), $values = array(), $no_defaults = FALSE) {
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