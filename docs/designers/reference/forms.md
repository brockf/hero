# Forms

Forms, [created in the control panel](/docs/publishing/forms.md), are essentially a group of custom fields that, when submitted, are saved in the database (viewable in the control panel) and potentially emailed to a specified email address.  Each form is mapped to a custom URL.

## Templates

Each form can be mapped to any template in your theme folder but, by default, forms are displayed with the `form.thtml` template.

## Example Form Template

```
{extends file="layout.thtml"}
{block name="title"}
{$title} - {$smarty.block.parent}
{/block}
{block name="content"}
	<h1>{$title}</h1>
	{if $text}{$text}{/if}
	<form class="form validate" enctype="multipart/form-data" method="post" action="{url path="forms/form/submit"}">
		<input type="hidden" name="form_id" value="{$id}" />
		
		{if $validation_errors}
			<div class="errors">
				{$validation_errors}
			</div>
		{/if}
		
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
					{custom_field field=$field value=$values[$field.name]} <label for="{$field.name}" >{$field.friendly_name}</label>
				</li>
			{/if}
			{if $field.help}
				<li>
					<div class="help flush">{$field.help}</div>
				</li>
			{/if}
			{/foreach}
		</ul>

		<input type="submit" class="button" name="go" value="{$button_text}" />
	</form>
{/block}
```

## Form Template Variables

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
			<td>`{$id}`</td>
			<td>The form's ID.</td>
		</tr>
		<tr>
			<td>`{$link_id}`</td>
			<td>The corresponding link ID in the universal links database.</td>
		</tr>
		<tr>
			<td>`{$title}`</td>
			<td>The form's title</td>
		</tr>
		<tr>
			<td>`{$text}`</td>
			<td>The form's introductory text.</td>
		</tr>
		<tr>
			<td>`{$custom_fields}`</td>
			<td>An array of all of the custom fields building this form.  See [for details on displaying the custom fields](/docs/designers/reference/custom_fields.md).</td>
		</tr>
		<tr>
			<td>`{$table_name}`</td>
			<td>The database table used to store form responses.</td>
		</tr>
		<tr>
			<td>`{$admin_link}`</td>
			<td>The link in the administrator control panel for form responses.</td>
		</tr>
		<tr>
			<td>`{$url}`</td>
			<td>The absolute URL to the form.</td>
		</tr>
		<tr>
			<td>`{$url_path}`</td>
			<td>The relative path to the form.</td>
		</tr>
		<tr>
			<td>`{$email}`</td>
			<td>The email address used for form responses.</td>
		</tr>
		<tr>
			<td>`{$button_text}`</td>
			<td>The text for the form's submit button.</td>
		</tr>
		<tr>
			<td>`{$redirect}`</td>
			<td>The relative path for the redirection after form submission.</td>
		</tr>
		<tr>
			<td>`{$privileges}`</td>
			<td>If the form is restricted for viewing by certain member groups, this variable is an array of those member group ID's.</td>
		</tr>
		<tr>
			<td>`{$num_responses}`</td>
			<td>How many responses to the form have been submitted?</td>
		</tr>
		<tr>
			<td>`{$template}`</td>
			<td>The template file used to display the form.</td>
		</tr>
	</tbody>
</table>

## Template Plugins

[tag]{form}[/tag]

Retrieve a form and display it in any template.  Returns all variables specified above into the `$var`-named array.

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
			<td>var</td>
			<td>Required</td>
			<td>Specify the name for the returned variable array (e.g., "form" returns an array with keys like `{$form.title}`.</td>
		</tr>
		<tr>
			<td>id</td>
			<td>Required</td>
			<td>The form ID for a specific piece of content to retrieve.</td>
		</tr>
	</tbody>
</table>

```
{form var="form" id="X"}
    <form class="form validate" enctype="multipart/form-data" method="post" action="{url path="forms/form/submit"}">
        <input type="hidden" name="form_id" value="{$form.id}" />
         
        {if $form.validation_errors}
            <div class="errors">
                {$form.validation_errors}
            </div>
        {/if}
         
        <ul class="form">
            {foreach $form.custom_fields as $field}
            {if $field.type != 'checkbox'}
                <li>
                    <label class="full" for="{$field.name}">{$field.friendly_name}</label>
                </li>
                <li>
                    {custom_field field=$field value=$values[$field.name]}
                </li>
            {else}
                <li>
                    {custom_field field=$field value=$values[$field.name]} <label for="{$field.name}" >{$field.friendly_name}</label>
                </li>
            {/if}
            {if $field.help}
                <li>
                    <div class="help flush">{$field.help}</div>
                </li>
            {/if}
            {/foreach}
        </ul>
 
        <input type="submit" class="button" name="go" value="{$form.button_text}" />
    </form>
{/form}
```