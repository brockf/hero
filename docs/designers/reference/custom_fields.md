# Custom Fields

Custom form fields are display in various areas of Hero.  Most of the time, they are used in the control panel to display content publishing forms or store product editing forms.  However, they are also used in the frontend of websites.  Primarily, they are used to display the [forms](/docs/publishing/forms.md) created in the control panel and in [registration forms](/docs/designers/reference/members.md) if the user has created custom member data fields.

When custom fields are available in a template, they are typically available in a template variable called `{$custom_fields}`.  It is expected that the template will iterate through this array and display each custom field in the template.  But how is this done?  Well, it's actually quite easy.  Simply send each array item, containing configuration data for the field like "name", "type", "options", etc., to the `{custom_field}` template plugin (documented below).

## Example Custom Field Template Code

Before we look at the template plugin, let's take a quick look at a basic template code snippet that displays custom fields in a form:

```
<ul class="form">
	{foreach $custom_fields as $field}
	{if $field.type != 'checkbox'}
		<li>
			<label class="full" for="{$field.name}">{$field.friendly_name}</label>
		</li>
		<li>
			{custom_field field=$field value=$values[$field.name]}
		</li>
	{else}
		<li>
			{custom_field field=$field value=$values[$field.name]} <label for="field_{$field.name}">{$field.friendly_name}</label>
		</li>
	{/if}
	{if $field.help}
		<li>
			<div class="help">{$field.help}</div>
		</li>
	{/if}
	{/foreach}
</ul>
```

As you can see, the code above does a few simple things:

* All form elements are displayed in a `<ul>` list.
* It iterates through the given `{$custom_fields}` variable, creating a `{$field}` item for each field.
* If the field is not a checkbox, it outputs the field label (`{$field.friendly_name}`) and form field itself on two separate lines (two `<li>` elements).
* If the field is a checkbox, it outputs the field label and checkbox element right beside each other.
* If there is help text for a field (in `{$field.help}`), the text is display in a help box.
* In this case, we have an array called `{$values}` which has been passed to the template.  It includes the form field values, referenced by name.  We pass this with each `{custom_field}` call.

## Custom Field Variables

Each custom field item contains all of the data associated with the custom field's fieldtype (e.g., "checkbox", "text", "textarea", "radio", and any other custom fieldtypes [created by a developer](/docs/developers/forms.md)).  This data is unique to the fieldtype and not important for a designer when displaying custom fields.

However, some variables are important for the designer because you must display these manually.  In essence, they aren't part of the form field itself displayed by `{custom_field}` (documented below).

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Variables</td>
		</tr>
		<tr>
			<td class="variable_name">Variable</td>
			<td class="variable_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>`{$name}`</td>
			<td>The system name of the custom field, use as "name" attribute for the form input element.  This is used by designers in the "for" attribute of a `<label>` element.</td>
		</tr>
		<tr>
			<td>`{$friendly_name}`</td>
			<td>The friendly label for the field (e.g., "Your Name").  Used in labelling the form fields.</td>
		</tr>
		<tr>
			<td>`{$type}`</td>
			<td>The custom fieldtype (e.g., "checkbox", "text", "radio", "date", etc.).  If you want to display form fields differently based on their type, you can use an `{if}` switch with this variable.</td>
		</tr>
		<tr>
			<td>`{$help}`</td>
			<td>The help text associated with a field.  It may be empty, depending on how you configured this custom field.</td>
		</tr>
		<tr>
			<td>`{$required}`</td>
			<td>Is this field required?  TRUE if yes, FALSE if no.</td>
		</tr>
	</tbody>
</table>

## Template Plugin

[tag]{custom_field}[/tag]

Display the form field for a custom field.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Parameters</td>
		</tr>
		<tr>
			<td class="parameter_name">Variable</td>
			<td class="is_required">Required?</td>
			<td class="parameter_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>field</td>
			<td>Required</td>
			<td>The array containing all of the field data, like "type", "name", "options", etc.</td>
		</tr>
		<tr>
			<td>value</td>
			<td>No</td>
			<td>If you want to pass a specific value for this field, specify it here.  Otherwise, it will automatically retrieve the value from the POST submission if possible.</td>
		</tr>
		<tr>
			<td>default</td>
			<td>No</td>
			<td>You can override the field's default value [configured in the control panel](/docs/configuration/custom_fields.md) by passing this parameter.  Particularly useful for eliminating all default values when you don't want to re-populate a form element that has been left blank (just pass "FALSE" for this parameter).</td>
		</tr>
	</tbody>
</table>

Basic usage:

```
{custom_field field=$field}
```

Specify a value from a values array:

```
{custom_field field=$field value=$values[$field.name]}
```

Override the default value for a custom field:

```
{custom_field field=$field default=FALSE}
```