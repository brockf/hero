# Form Builder Library

This library provides a way to assemble multiple [Fieldtype objects](/docs/developers/reference/fieldtype_library.md) into one form, and then run form-wide methods such as validation routines, assigning values to the form fields, or displaying the form.  It is most commonly used in conjunction with pre-defined custom field groups, but it can also be used to build a form manually by adding new Fieldtype objects sequentially and manipulating these objects to add values, help text, default options, etc.

## Initialization

```
$this->load->library('custom_fields/form_builder');
// methods calls: $this->form_builder->add_field(), $this->form_builder->reset(), etc.
```

## Method Reference

## `boolean build_form_from_group (int $custom_field_group_id)`

Pass this method an ID of a custom field group in the system, and it will automatically load and build your form based on the returned data from `$this->custom_fields_model->get_custom_fields()` ([documentation here](/docs/developers/reference/custom_fields_model.md)).

Form objects are not directly manipulable in this case, because it does not return any field objects.  However, you can use form-wide functions like `set_values()` and `clear_defaults()` to work with your form.

```
$this->form_builder->build_form_from_group (15);
// get form, formatted for the control panel
$form = $this->form_builder->output_admin();
// easy as that
```

## `boolean build_form_from_array (array get_custom_fields)`

Like `build_form_from_group()`, this method builds a form using pre-defined custom field group data.  However, this method takes an array from `custom_fields_model->get_custom_fields()` ([documentation here](/docs/developers/reference/custom_fields_model.md)) as its only parameter, thereby eliminating another call to that method.  Usage is identical after its loaded.

## `object add_field (string $type)`

If you are not using predefined fields, you can build your form programmatically as you go by creating and manipulating field objects.  You have access to all of the custom fieldtypes in your system (defined at `/app/modules/custom_fields/libraries/fieldtypes/`).

This is the optimal way to use this library to build dynamic, control panel-ready forms for your new module without touching any HTML.

```
$this->load->library('custom_fields/form_builder');
$my_text_field = $this->form_builder->add_field('text');
$my_text_field->name('my_text')
	   	      ->label('My Text Field')
	   	      ->default_value('I like this!')
	   	      ->validation(array('alphanumeric','minlength[5]'));

$my_select_field = $this->form_builder->add_field('select');
$my_select_field->name('school')
	            ->label('My School')
	            ->options(array(
							array('name' => 'MPSS', 'value' => 'MPSS'),
							array('name' => 'CSS', 'value' => 'CSS'),
							array('name' => 'HSS', 'value' => 'HSS')
							))
				->required(TRUE)
				->help('Please select your school.');
											
$output = $this->form_builder->output_admin();
```

## `string output_admin ()`

Return the formatted HTML for the form in memory.

In your controller:

```
$form = $this->form_builder->output_admin();
$this->load->view('my_form_view', array('form' => $form));
```

In your view:

```
<form method="post" action="admincp/my_module/post">
	<fieldset>
		<ul class="form">
			<?=$form;?>
		</ul>
	</fieldset>
</form>
```

## `boolean validate_post ()`

When a form has been loaded/built, you can run this method to validate a POST submission across all the form fields based on their individual validation routines.  It will return `FALSE` if some value that has been POST'd is invalid.

```
// reminder: this controller is invoked by an POST form submissions
$this->load->library('form_builder');
$this->form_builder->build_form_from_group(29);
if ($this->form_builder->validate_post() === FALSE) {
	// we have a problem
	
	$errors = $this->form_builder->validation_errors();
	
	// not the best style, but we'll just print the HTML-formatted errors
	echo $errors;
}
else {
	$values = $this->form_builder->post_to_array();
	
	// we now have an array of $values for the form, keyed with each field's "name",
	// for insertion into a database or processing of some kind
}
```

## `string|array validation_errors ([boolean $array = FALSE])`

When `validate_post()` has been called and returned `FALSE`, validation errors can be retrieved with this method.

If called without parameters or with `$array` as `FALSE`, the method will return HTML-formatted errors (each error in a `<p>` element).

If `$array` is set to `TRUE`, an array of errors is retrieved.

```
$array = $this->form_builder->validation_errors(TRUE);

foreach ($array as $error) {
	echo $error . '<br />';
}
```

## `array post_to_array ()`

This method invokes each field object's `post_to_value()` method to retrieve a value from the POST submission for this field.  For some fields (e.g., text fields), this is as simple as returning the element in POST of the same name.  However, for other complex fields (e.g., file uploads), this involves seeing if a file was uploaded, whether a file was previously uploaded and they are just leaving this file, or whether they entered a file uploaded by FTP that should override the specific file upload field.  Either way, the fields do this processing themselves.

This method simply returns an array that has a key for each field and its subsequent value.

```
// we'll build our form.
// this would likely be in a method/function that is called by both the form displaying controller and
// the form processing controller so that we aren't rewriting code.

$this->load->library('custom_fields/form_builder');
$my_text_field = $this->form_builder->add_field('text');
$my_text_field->name('my_text')
	   	      ->label('My Text Field')
	   	      ->default_value('I like this!')
	   	      ->validation(array('alphanumeric','minlength[5]'));

$my_select_field = $this->form_builder->add_field('select');
$my_select_field->name('school')
	            ->label('My School')
	            ->options(array(
							array('name' => 'MPSS', 'value' => 'MPSS'),
							array('name' => 'CSS', 'value' => 'CSS'),
							array('name' => 'HSS', 'value' => 'HSS')
							))
				->required(TRUE)
				->help('Please select your school.');
											
// now, we can process the POST submission with one simple call
$post_data = $this->form_builder->post_to_array();

// we now have an array like, array( 'my_text' => '1234abcd', 'school' => 'MPSS' )
// this obviously changes depending on the form submission, itself.
```

## `boolean set_values (array $values)`

Once a form is built, you may want to set values for all the form fields en masse.  To do so, pass this method an array of data (with each key representing a field's name), and they will be set.

```
// we have a form built with fields "name", "school", and "team"...
$data = array(
				'name' => 'Paul N',
				'school' => 'MPSS',
				'team' => 'Thunder'
			);
$this->form_builder->set_values($data);
```

## `void clear_defaults ()`

When you are editing a piece of published content or data, it is unlikely that you want to substitute in the field's default values for empty fields.  This method will wipe out all default values for the form in memory.

```
if ($editing == TRUE) {
	$this->form_builder->clear_defaults();
}
```

## `void reset ()`

Once you have built a form, it will remain in Form Builder until this method resets the library.  It's smart to call this before building any form.

```
$this->form_builder->reset();
```

