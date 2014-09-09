# Custom Fields Model

The custom fields model is used to create/update/delete custom field groups, as well as create/update/delete the custom fields that comprise those groups.  Custom fields are invoked throughout Hero, including in publishing content, customizing member profile date, customizing product data, etc.  For more information on their management, [click here for a guide on configuring custom fields](/docs/configuration/custom_fields.md).

## Initialization

Note that this model is **not** in the custom_fields' module folder; it is in the main `/app/models/` folder.

```
$this->load->model('custom_fields_model');
```

## Method Reference

## `array get_custom_field (int $custom_field_id)`

Retrieve a configuration array for a specific custom field, referenced by ID.  This array is in the format of the arrays that make up the array returned by `get_custom_fields()`.

## `array get_custom_fields (array $filters)`

Retrieve an array of custom fields matching the filters, if filtered.  This array can be passed to the [Form Builder library](/docs/developers/reference/form_builder_library.md) to easily build control panel forms with a custom field group.

Possible filters:

* *group* - An ID of a custom field group.
* *id* - An ID of a specific custom field.

Each returned field array has the following keys:

* *id*
* *group_id*
* *friendly_name* - The human-readable field name
* *name* - The system name, equivalent to the column name in the associated MySQL table, or the name of the actual HTML `<input>` element.
* *type* - The fieldtype
* *options* - A possible array of potential values (for certain fieldtypes)
* *help* - Help text, if available
* *width* - Specified width, if available
* *default* - Default value(s), if available.  May be a serialized array of values depending on the fieldtype.
* *required* - TRUE if this field is required.
* *validators* - An array of [CodeIgniter-standard form validators](http://codeigniter.com/user_guide/libraries/form_validation.html), if available.
* *data* - An array of additional fieldtype data attributes, if available.

## `int new_custom_field (int $group , string $name , string $type [, string $options = array() [, string $default = '' [, string $width = '' [, string $help = '' [, boolean $required = FALSE [, array $validators = array() [, boolean $db_table = FALSE [, array $data = array()]]]]]]]])`

Create a new custom field in a specific custom field group.  The `$name` passed will be used as the human-friendly "label" for the field.  However, a "system_name" will automatically be generated based on this field (processed with [the clean string helper](/docs/developers/reference/clean_string_helper.md)).

If a `$db_table` is passed, a column will be created in this table with this "system_name".  The column's MySQL type (e.g., VARCHAR, TEXT) will be retrieved from the [fieldtype's definition](/docs/developers/reference/fieldtype_library.md).

Returns the `$custom_field_id`.

## `boolean update_custom_field (int $custom_field_id, int $group , string $name , string $type [, string $options = array() [, string $default = '' [, string $width = '' [, string $help = '' [, boolean $required = FALSE [, array $validators = array() [, boolean $db_table = FALSE [, array $data = array()]]]]]]]])`

Update an existing custom field record.

If a `$db_table` is passed, the column in the database table will be renamed.

## `boolean delete_custom_field (int $id [, string $db_table = FALSE])`

Delete an existing custom field.  If a `$db_table` is passed, the column that shares this field's "name" will be removed from the MySQL table.

## `void reset_order (int $custom_field_group)`

When re-ordering custom fields in a group, it's standard procedure to call this method to reset the order across all fields prior to sorting.

## `void update_order (int $field_id , int $new_order)`

Set the order (`$new_order`) (e.g., "1", "2", "3", "4", ...) of a particular field.

## `string get_system_name (int $id)`

Return the system/column/POST name of a field referenced by its custom field ID.

## `int new_group (string $name)`

Create a new custom field group.  Returns the `$custom_field_group_id`.

## `void delete_group (int $group_id [, string $db_table] )`

Delete a custom field group (and all of its fields!).