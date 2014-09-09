# Admin Form Library

Generate control panel-standard HTML forms using this library.

> This library has been deprecated in favour of the [combination of the Form Builder library and the new custom fieldtype engine](/docs/developers/forms.md).  Thus, documentation is intentionally sparse.

Example from the RSS module:

```
$this->load->library('Admin_form');
$form = new Admin_form;

$form->fieldset('Design');
$this->load->helper('template_files');
$template_files = template_files();
$form->dropdown('Output Template', 'template', $template_files, 'rss_feed.txml', FALSE, TRUE, 'This template in your theme directory will be used to display this blog/archive page.');
```

## Initialization

The library should be initialized like a normal library, but then an object should be created of this class:

```
$this->load->library('Admin_form');
$my_form = new Admin_form;
// now build the form
```

## Method Reference

## `void fieldset (string $legend = '' [, array $ul_classes = FALSE])`

Create a new fieldset.  This must be called prior to any fields being added and can be called again at anytime to register a new fieldset.

## `void hidden (string $name , string $value)`

Add a hidden field.

## `void text (string $label , string $name [, string $value = '' [, string $help = FALSE [, boolean $required = FALSE [, boolean $mark_empty = FALSE [, boolean $full = FALSE [, string $width = '250px' [, string $li_id = '' [, array $classes = FALSE]]]]]]]])`

Add a text field.

## `void password (string $label , string $name [, string $help = FALSE [, boolean $required = FALSE [, boolean $full = FALSE [, string $width = '250px' [, string $li_id = '']]]]])`

Add a password field.

## `void names (string $label , string $first_value , string $last_value [, string $help = FALSE [, boolean $required = FALSE [, string $width = '250px' [, string $li_id = '']]]])`

Add a names field with side-by-side first and last names.

## `void textarea (string $label , string $name [, string $value = '' [, string $help = FALSE [, boolean $required = FALSE [, string $wysiwyg = FALSE [, boolean $full = FALSE [, string $width = '300px' [, string $height = '100px' [, string $li_id = '']]]]]]]])`

Add a textarea field.

## `void dropdown (string $label , string $name , array $options [, array $selected = FALSE [, boolean $multiselect = FALSE [, boolean $required = FALSE [, string $help = FALSE [, boolean $full = FALSE [, string $li_id = '']]]]]])`

Add a dropdown field.

## `void radio (string $label , string $name , array $options , string $selected [, boolean $required = FALSE [, string $help = FALSE [, boolean $full = FALSE [, string $li_id = '']]]])`

Add a radio field.

## `void checkbox (string $label , string $name , string $value [, boolean $checked = FALSE [, string $help = FALSE [, boolean $full = FALSE [, string $li_id = '']]]])`

Add a checkbox field.

## `void file (string $label , string $name [, string $width = '250px' [, string $full = FALSE [, string $li_id = '']]])`

Add a file upload field.

## `void date (string $label , string $name , string $value [, string $help = FALSE [, boolean $required = FALSE [, boolean $mark_empty = FALSE [, boolean $full = FALSE [, string $width = '250px' [, string $li_id = '' [, array $classes = FALSE]]]]]]])`

Add a date field enabled with the datepicker.

## `void value_row (string $label , string $value , boolean $full)`

Add a simple row to the form with a left side "label" and right side "value".

## `void custom_fields (array $custom_fields [, array $values = FALSE [, boolean $no_defaults = FALSE]])`

Load custom fields into the form from an array created by `custom_fields_model->get_custom_fields()`.  This method was updated to use the new [Fieldtype library](/docs/developers/reference/fieldtype_library.md) so that this entire library remains relevant and somewhat useful.

## `string display ()`

Return the formatted HTML for the form that has been built.