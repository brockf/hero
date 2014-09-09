# Fieldtype Library

The Fieldtype library exists for three purposes:

* To provide methods that deal with all custom fieldtypes, such as retrieving them all in a list, or loading a fieldtype into memory so that we can access some property of that fieldtype.
* To create and return an object of a specific fieldtype, so that it can be manipulated
* To be inherited by a specific custom fieldtype, and thus give it methods common to all fieldtypes such as `value()` and `name()` (all fields have names and values that need to be assigned).

Because this library has both "global" and "local" functions, it can be a bit confusing.  The examples in this guide should clear this up.

The vast majority of interaction with this library will be done secondarily through the [Form Builder library](/docs/developers/reference/form_builder_library.md).  Direct calls to these methods are unlikely by third-party developers.

## Initialization

```
$this->load->library('custom_fields/fieldtype');
```

## Method Reference

## `object create ($type)`

Create and return an object of a specific fieldtype.  Once created, the fieldtype object can be be manipulated.

```
$field = $this->fieldtype->create('textarea');
$field->name('Story')
	  ->label('Write a Story')
	  ->validators(array('max_length[1000]'));
```

## `object load (int|array $field_data)`

Pass this method either an ID of an existing custom field (i.e., a `$custom_field_id`) or an array of a single custom field's configuration as returned by `custom_fields_model->get_custom_fields()`, and it will return the fieldtype object just like `create()`.

```
// get field data
$field_config = $this->custom_fields_model->get_custom_field(14);

if ($field = $this->fieldtype->load($field_config)) {
	// we can do anything with the object now, but we'll just print it
	echo $field->output_admin();
}
else {
	die('Error loading field from array');
}
```

## `boolean load_type (string $type)`

Load a particular fieldtype into memory.  This method is called automatically if a fieldtype object is being created but the type has not been defined.  However, you may want to call it independently if you are trying to access properties of a custom fieldtype, such as it's `$description`.

Once loaded, the fieldtype is an object of the Fieldtype library.

```
// we want to show the names and descriptions for 5 fieldtypes

$types = array('text', 'textarea', 'file_upload', 'select', 'date');

$this->load->library('custom_fields/fieldtype');
foreach ($types as $type) {
	$this->fieldtype->load_type($type);
	
	$fieldtype_name = $this->fieldtype->$type->name;
	$fieldtype_description = $this->fieldtype->$type->description;
	
	echo 'Name: ' . $fieldtype_name . ' - ' . $fieldtype_description;
}
```

## `boolean load_all_types ()`

Load all possible fieldtypes from `/app/modules/custom_fields/libraries/fieldtypes/` as objects of the Fieldtype library with one call.

## `array get_fieldtype_options ()`

Retrieve an array of all possible fieldtypes:

```
array(
	'text' => 'Text',
	'date' => 'Date',
	'select' => 'Select Dropdown'
);
```

## `string db_column ()`

Return the MySQL column type for a specific fieldtype.  Useful when adjusting a database table schema based on the addition of a new custom field.

```
// we are adding a new file_upload field to a MySQL table
$this->fieldtype->load('file_upload');
$db_type = $this->fieldtype->file_upload->db_column();

$this->db->query('ALTER TABLE `my_table` ADD COLUMN `my_new_field` ' . $db_type);
```

## `object id (string $id)`

Set the id of a field.  Returns the field object for method chaining.

## `object type (string $type)`

Set the type of a field.  Returns the field object for method chaining.

## `object default_value (string|array $default)`

Set the default value of a field.  Returns the field object for method chaining.

## `object options (array $options)`

Set the options array of a field.  Returns the field object for method chaining.  Array format:

```
array(
	[0] = array('value' => 'black', 'name' => 'Black'),
	[1] = array('value' => 'white', 'name' => 'White'),
	[2] = array('value' => 'blue', 'name' => 'Blue')
);
```

## `object data (array $data)`

Set additional data of a field.  Data is used for atypical field attributes (e.g., "allowed_filetypes" for file_upload fields).  Returns the field object for method chaining.

## `object value (string|array|boolean $value)`

Set the value of a field.  Returns the field object for method chaining.

## `object label (string $label)`

Set the label of a field.  Returns the field object for method chaining.

## `object name (string $name)`

Set the name of a field.  Returns the field object for method chaining.

## `object width (string $width)`

Set the width of a field.  Returns the field object for method chaining.

## `object help (string $help)`

Set the help text of a field.  Returns the field object for method chaining.

## `object placeholder (string $placeholder)`

Set the placeholder text of a field.  Returns the field object for method chaining.

## `object required (boolean $required)`

Set to TRUE to make the field required upon submission/validation.  Returns the field object for method chaining.

## `object validators (array $validators)`

Specify an array of validators.  These are in the format of [CodeIgniter's Form Validation library](http://codeigniter.com/user_guide/libraries/form_validation.html).  Returns the field object for method chaining.

## `object li_attribute (string $name , string $value)`

Specify a name/value attribute to be used with the returned `<li>` element when displaying a field.  Returns the field object for method chaining.

## `object field_class (string $name)`

Specify an additional class to be assigned to the field's HTML element when displaying a field.  Returns the field object for method chaining.

## `string compile_attributes (array $attributes)`

A helper method for fieldtype libraries, this method will take a bunch of attributes and turn them into a nice string like so:

```
$attr = $this->compile_attributes(array('width' => '250px', 'type' => 'text', 'placeholder' => 'empty'));

// $attr is now: width="250px" type="text" placeholder="empty"
```
