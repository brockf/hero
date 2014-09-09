# Forms

A critical component of the control panel is *forms*.  Forms are built using an expandable library of fieldtypes (defined as PHP classes at `/app/modules/custom_fields/libraries/fieldtypes/`).

In the control panel, forms are displayed with the [Form_builder library](/docs/developers/reference/form_builder_library.md).  In the frontend, forms are built by parsing each individual field with the [{custom_field}](/docs/designers/reference/custom_fields.md) template plugin.

## The Fieldtype library

At the heart of the Hero form/field architecture is the [Fieldtype](/docs/developers/reference/fieldtype_library.md) library.  This class is inherited by specific fieldtype definition files (e.g., "text", "select", and "file_upload") and these complete fieldtype objects are manipulated to output proper fields.

This library has a set of "super" methods which load fieldtype definition files, create fieldtype objects from field arrays (retrieved from [custom_fields_model->get_custom_fields(](/docs/developers/reference/custom_fields_model.md))), and other functions associated with fieldtypes.

The library also has methods meant for inheritance by the specific fieldtype objects (e.g., property assignment methods `$this->name()`, `$this->label()`, and `$this->value()`).

If you wanted to use the Fieldtype library to create a Fieldtype object and begin manipulating it, you can do so like so:

```
$this->load->library('custom_fields/fieldtype');
$my_text_field = $this->fieldtype->create('text');
$my_text_field->name('fake_field');
$my_text_field->label('Fake Field');
$my_text_field->validators(array('alpha_numeric','trim'));
$my_text_field->required(TRUE);

// retrieve output in the standard admin panel format
$output = $my_text_field->output_admin();
```

In this example, the validators are kind of useless because we aren't running any validation.  But, this code building the field object might be shared amongst a class that both creates the field and validates a post, in which case this makes sense.

It's important to note that the methods like `name()` and `label()` can be chained because they return the field object:

```
$my_text_field->name('fake_field')->label('Fake Field')->required(TRUE);
```

For complete documentation of all of the Fieldtype libraries methods, visit the [Fieldtype_library reference](/docs/developers/reference/fieldtype_library.md).

Fieldtypes can do anything they would like with the `data()` method and associated "data" array property.  For example, the "Content Relationship" fieldtype stores the content_type of it's linked content as `$this->data['content_type']`. 

So, as you can see, you can build and manipulate an isolated Fieldtype object.  However, this is not very practical for a typical application routine.  Most likely, you'll want to build a complete form with the [Form_builder library](/docs/developers/reference/form_builder_library.md) described below.

## Using Form_builder (control panel forms)

The [Form_builder library](/docs/developers/reference/form_builder_library.md) (part of the custom fields module) is a way to combine Field objects into a form.  When the objects are compiled, you can do things like validate the POST submission across all form elements, or retrieve a database-ready array of information based on a POST submission and processed by each Fieldtype object.

`Form_builder` has various methods that are meant specifically to interact with the arrays returned by [custom_fields_model->get_custom_fields(](/docs/developers/reference/custom_fields_model.md)).  For example, to build an entire form from a custom field group:

```
$this->load->library('custom_fields/form_builder');
$this->form_builder->build_form_from_group(4); // use custom field group #4

$output = $this->form_builder->output_admin();
$this->form_builder->reset();

// $output contains the HTML for the form
```

You could also build a form from a multi-key array of custom fields retrieved from `custom_fields_model->get_custom_fields()` with `build_form_from_array()`:

```
$this->load->library('custom_fields/form_builder');
$this->load->model('custom_fields_model');

// get custom fields
$fields = $this->custom_fields_model->get_custom_fields(array('group' => '4'));

$this->form_builder->build_form_from_array($fields);

$output = $this->form_builder->output_admin();
$this->form_builder->reset();
```

> The legacy form library, [Admin_form](/docs/developers/reference/admin_form_library.md), replicates this functionality within its framework.

```
$this->load->library('admin_form');
$this->load->model('custom_fields_model');

// get custom fields
$fields = $this->custom_fields_model->get_custom_fields(array('group' => '4'));

$form = new Admin_form;
$form->custom_fields($fields);

$output = $form->display();
```

Aside from rapidly combining preconfigured custom fields from the database, you can also build forms dyamically with `Form_builder` using `add_field()`:

```
$this->load->library('custom_fields/form_builder');
$my_text_field = $this->form_builder->add_field('text');
$my_text_field->name('my_text')->label('My Text Field')->default_value('I like this!');

$my_select_field = $this->form_builder->add_field('select');
$my_select_field->name('school')->label('My School')->options(array(
											array('name' => 'MPSS', 'value' => 'MPSS'),
											array('name' => 'CSS', 'value' => 'CSS'),
											array('name' => 'HSS', 'value' => 'HSS')
											))->help('Please select your school.');
											
$output = $this->form_builder->output_admin();
```

## Defining new fieldtypes

Defining a new fieldtype is very easy.  The beauty of this system is that you can just drop in a single file to `/app/modules/custom_fields/libraries/fieldtypes/` and have a new fieldtype available across the entire system.  The default Fieldtypes are highly documented and very simple to modify, so it's best to take an existing fieldtype and adopt it to your needs.

Each fieldtype has the following methods:

* `construct()`
* `output_admin()`
* `output_frontend()`
* `field_form()` - Build the form to add/edit fields of this type in the control panel
* `field_form_process()` - Process the submission of the above form (returns an array)
* `post_to_value()` - Convert a POST submission into a database-ready value for this field
* `validate_post()` - Return `form_validation` library rules for this field, based on its current settings
* `additional_validation()` - Perform any additional, atypical validation routines (e.g, validate file upload extensions)

### Example fieldtype library: Text fields

```
<?php

/*
* Text Fieldtype
*
* @extends Fieldtype
* @class Text_fieldtype
*/
class Text_fieldtype extends Fieldtype {
	/**
	* Constructor
	*
	* Assign basic properties to this fieldtype, useful in listing available fieldtypes.
	* Also defines the MySQL column format for fields of this type.
	*/
	function __construct () {
		parent::__construct();
	 
		$this->compatibility = array('publish','users','products','collections','forms');
		$this->enabled = TRUE;
		$this->fieldtype_name = 'Text';
		$this->fieldtype_description = 'A single line of text.';
		$this->validation_error = '';
		$this->db_column = 'VARCHAR(250)';
	}
	
	/**
	* Output Shared
	*
	* Perform actions shared between admin- and frontend-outputs.  Compile attributes of this
	* fieldtype object into an HTML attribute line.
	*
	* @return string $attributes
	*/
	function output_shared () {
		// set defaults
		if ($this->width == FALSE) {
			$this->width = '275px';
		}
		
		// prep classes
		if ($this->required == TRUE) {
			$this->field_class('required');
		}
		
		$this->field_class('text');
		
		// add validator names to class list
		foreach ($this->validators as $validator) {
			$this->field_class($validator);
		}
		
		// prep final attributes	
		$placeholder = ($this->placeholder !== FALSE) ? ' placeholder="' . $this->placeholder . '" ' : '';
		
		$attributes = array(
						'type' => 'text',
						'name' => $this->name,
						'id' => $this->name,
						'value' => htmlspecialchars($this->value),
						'placeholder' => $this->placeholder,
						'style' => 'width: ' . $this->width,
						'class' => implode(' ', $this->field_classes)
						);
		
		// compile attributes
		$attributes = $this->compile_attributes($attributes);
		
		return $attributes;
	}
	
	/**
	* Output Admin
	*
	* Returns the field with it's <label> in an <li> suitable for the admin forms.
	*
	* @return string $return The HTML to be included in a form
	*/
	function output_admin () {
		if (empty($this->value) and $this->CI->input->post($this->name) == FALSE) {
			$this->value($this->default);
		}
	
		$attributes = $this->output_shared();
		
		$help = ($this->help == FALSE) ? '' : '<div class="help">' . $this->help . '</div>';
		
		// build HTML
		$return = '<li>
						<label for="' . $this->name . '">' . $this->label . '</label>
						<input ' . $attributes . ' />
						' . $help . '
					</li>';
					
		return $return;
	}
	
	/**
	* Output Frontend
	*
	* Returns the isolated field.  Likely called from the {custom_field} template function.
	*
	* @return string $return The HTML to be included in a form.
	*/
	function output_frontend () {
		if (empty($this->value)) {
			if ($this->CI->input->post($this->name) == FALSE) {
				$this->value($this->default);
			}
			elseif ($this->CI->input->post($this->name) != FALSE) {
				$this->value($this->CI->input->post($this->name));
			}
		}
		
		$attributes = $this->output_shared();
		
		// build HTML
		$return = '<input ' . $attributes . ' />';
					
		return $return;
	}
	
	/**
	* Validation Rules
	*
	* Return an array of CodeIgniter form_validation rules for this fieldtype.  These are used
	* by form_builder to run a validation across all fields at once using CodeIgniter.
	*
	* @return array $rules
	*/
	function validation_rules () {
		$rules = array();
		
		$this->CI->load->helper('valid_domain');
		
		// run $this->validators
		if (!empty($this->validators)) {
			foreach ($this->validators as $validator) {
				if ($validator == 'whitespace') {
					$rules[] = 'trim';
				}
				elseif ($validator == 'alphanumeric') {
					$rules[] = 'alpha_numeric';
				}
				elseif ($validator == 'numeric') {
					$rules[] = 'numeric';
				}
				elseif ($validator == 'domain') {
					$rules[] = 'valid_domain';
				}
				elseif ($validator == 'email') {
					$rules[] = 'valid_email';
				}
				elseif ($validator == 'strip_tags') {
					$rules[] = 'strip_tags';
				}
			}
		}
		
		// check required
		if ($this->required == TRUE) {
			$rules[] = 'required';
		}
		
		return $rules;
	}
	
	/**
	* Validate Post
	*
	* This validation is outside of CodeIgniter's form_validation library.  It is run specifically
	* for this field after it passes the major form_validation check.  Not all fieldtypes
	* will require it.  If an error is found, it should be stored in $this->validation_error
	* (using $this->label to refer to the field) and should return FALSE so that the form
	* processor in form_builder knows there was an error.  It will pull the error from
	* $this->validation_error.
	*
	* @return boolean
	*/
	function validate_post () {
		// nothing extra to validate here other than the rulers in $this->validators
		return TRUE;
	}
	
	/**
	* Post to Value
	*
	* Convert the $_POST value to the value that should be inserted into the database.
	*
	* @return string $db_value
	*/
	function post_to_value () {
		return $this->CI->input->post($this->name);
	}
	
	/**
	* Field Form
	*
	* Build the form that will be used to add/edit fields of this type.
	* 
	* @return string $form Built using form_builder.
	*/
	function field_form ($edit_id = FALSE) {
		// build fieldset with admin_form which is used when editing a field of this type
		$this->CI->load->library('custom_fields/form_builder');
		$this->CI->form_builder->reset();
		
		$default = $this->CI->form_builder->add_field('text');
		$default->label('Default Value')
	          ->name('default');
	          
	    $help = $this->CI->form_builder->add_field('textarea');
	    $help->label('Help Text')
	    	 ->name('help')
	    	 ->width('500px')
	    	 ->height('80px')
	    	 ->help('This help text will be displayed beneath the field.  Use it to guide the user in responding correctly.');
	    	 
	   	$width = $this->CI->form_builder->add_field('text');
	   	$width->label('Width')
	   	 	  ->name('width')
	   	 	  ->width('75px')
	   	 	  ->default_value('250px')
	   		  ->help('Enter the width of this field.');
	    	 
	    $required = $this->CI->form_builder->add_field('checkbox');
	    $required->label('Required Field')
	    	  ->name('required')
	    	  ->help('If checked, this box must not be empty for the form to be processed.');
	    	  
	   	$validators = $this->CI->form_builder->add_field('multicheckbox');
	   	$validators->label('Validators')
	   			   ->name('validators')
	   			   ->options(
	   			   		array(
	   			   			array('name' => 'Trim whitespace from around response', 'value' => 'trim'),
	   			   			array('name' => 'Strip HTML tags', 'value' => 'strip_tags'),
	   			   			array('name' => 'Only alphanumeric characters', 'value' => 'alpha_numeric'),
	   			   			array('name' => 'Only numbers', 'value' => 'numeric'),
	   			   			array('name' => 'Must be a valid domain (e.g., "yahoo.com")', 'value' => 'valid_domain'),
	   			   			array('name' => 'Must be a valid email address (e.g., "test@example.com")', 'value' => 'valid_email')
	   			   		)
	   			   );
	    	  
	    if (!empty($edit_id)) {
	    	$this->CI->load->model('custom_fields_model');
	    	$field = $this->CI->custom_fields_model->get_custom_field($edit_id);
	    	
	    	$default->value($field['default']);
	    	$help->value($field['help']);
	    	$validators->value($field['validators']);
	    	$required->value($field['required']);
	    	$width->value($field['width']);
	    }	  
	          
		return $this->CI->form_builder->output_admin();      
	}
	
	/**
	* Field Form Process
	*
	* Process the submission of $this->field_form() and return an array of data to be used in custom_fields_model->new_custom_field().
	*
	* Available keys for the returned array: name, type, default (string/array), help, required, validators (array), data (array), 
	*										 options (array), width
	*
	* @return array
	*/
	function field_form_process () {
		// build array for database
		
		// $options will be automatically serialized by the custom_fields_model::new_custom_field() method
		
		return array(
					'name' => $this->CI->input->post('name'),
					'type' => $this->CI->input->post('type'),
					'default' => $this->CI->input->post('default'),
					'help' => $this->CI->input->post('help'),
					'validators' => $this->CI->input->post('validators'),
					'required' => ($this->CI->input->post('required')) ? TRUE : FALSE,
					'width' => $this->CI->input->post('width')
				);
	}
}
```

## Legacy Forms: Admin_form

If you don't need the object-oriented nature of the Fieldtype library, and just want to quickly build a form that is formatted for the control panel, you can use the [Admin_form library](/docs/developers/reference/admin_form_library.md).

This library has various methods like `text()`, `textarea()`, `dropdown()`, etc.  All of the basic HTML fieldtypes are there.  However, you lose the expandibility of `fieldtype` + `form_builder`.