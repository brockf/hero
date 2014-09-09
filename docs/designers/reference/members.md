# Members

The account management and user actions in Hero are, by default, bound to specific templates and URL's.  This guide outlines those URL's and templates.  It also shows you member-related template plugins that can be used throughout your theme, including plugins to create registration forms, login forms, and list site members.

## Important URLs

* `/users/` - Account Manager
* `/users/register` - Registration Form
* `/users/login` - Login Form
* `/users/logout` - Logout
* `/users/forgot_password` - Forgot Password Form

## Templates

* `/account_templates/cancel_subscription.thtml`
* `/account_templates/change_password.thtml`
* `/account_templates/forgot_password_complete.thtml`
* `/account_templates/forgot_password.thtml`
* `/account_templates/home.thtml`
* `/account_templates/invoice.thtml`
* `/account_templates/invoices.thtml`
* `/account_templates/login.thtml`
* `/account_templates/profile.thtml`
* `/account_templates/registration.thtml`

## Member Template Variables

The following variables are available in the `{$member}` [global variable](/docs/designers/reference/global_variables.md) and within a `{members}` template call (documented below).

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
			<td>The member ID.</td>
		</tr>
		<tr>
			<td>`{$is_admin}`</td>
			<td>Set to TRUE if the user is an administrator, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$usergroups}`</td>
			<td>An array of usergroups that the user belongs to.</td>
		</tr>
		<tr>
			<td>`{$first_name}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$last_name}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$username}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$email}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$signup_date}`</td>
			<td>The date the user signed up on.</td>
		</tr>
		<tr>
			<td>`{$last_login}`</td>
			<td>The date the user last logged in.</td>
		</tr>
		<tr>
			<td>`{$suspended}`</td>
			<td>Set to TRUE if the user is suspended, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$admin_link}`</td>
			<td>A link to the user's profile in the administrator's control panel.</td>
		</tr>
		<tr>
			<td colspan="2">All *custom fields* for the member are accessible just like the other variables, with the *system name* of the custom field as the variable name (e.g., `{$my_custom_field}`).</td>
		</tr>
	</tbody>
</table>

## Specifying a Return URL during Login

> This tip is for developers only, or designers who are writing PHP template plugins.

If you want to send users to the main login page at `users/login` but have them sent back to a specific page when they have logged in, you can specify a return URL with the `return` query string variable.

This variable must be encoded with the helper function `query_value_encode` (availably globally).

Example:

```
<?php
// redirect to login page with specified URL
header('Location: ' . site_url('users/login?return=' . query_value_encode('my_relative_url')));
?>
```

This URL can also be absolute:

```
<?php
// redirect to login page with specified URL
header('Location: ' . site_url('users/login?return=' . query_value_encode('http://www.yahoo.com')));
?>
```

## Template Plugins

[tag]{login_form}[/tag]

Display a login form which (optionally) returns the user to a specified URL after a successful login.

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
			<td>Specify the name for the returned variable array (e.g., "form" returns an array with keys like `{$form.return}`.</td>
		</tr>
		<tr>
			<td>return</td>
			<td>No</td>
			<td>Specify an absolute (e.g., http://www.example.com) or relative (e.g., /members_area) URL to redirect the user to after logging in.</td>
		</tr>
		<tr>
			<td>username</td>
			<td>No</td>
			<td>Specify the username to auto-populate the username field with.  It's highly unlikely that you would pass this parameter, but it is used in system calls.</td>
		</tr>
	</tbody>
</table>

Available block variables:

* `{$var.form_action}` (the value for the form's "action" attribute")
* `{$var.return}` (the encoded value for the hidden "return" field)
* `{$var.username}` (the value to populate the username field - may be empty)

Example login form:

```
{login_form var="login" return=$return}
	<h1>Account Login</h1>
	<form method="post" action="{$login.form_action}">
		<input type="hidden" name="return" value="{$login.return}">
	
		<ul class="form">
			<li>
				<label for="username">Username/Email</label>
				<input type="text" id="username" name="username" value="{$login.username}">
			</li>
			<li>
				<label>Password</label>
				<input type="password" id="password" name="password" />
			</li>
			<li>
				<input type="checkbox" value="1" name="remember" /> Remember me for future visits?
			</li>
			<li>
				<input type="submit" name="login" value="Login" />
			</li>
		</ul>
		
		<ul class="login_form_links">
			<li>
				<a href="{url path="users/register"}">Don't have an account? Click here to register.</a>
			</li>
			<li>
				<a href="{url path="users/forgot_password"}">Forgot your password?</a>
			</li>
		</ul>
	</form>
{/login_form}
```

[tag]{register_form}[/tag]

Display a register form which (optionally) returns the user to a specified URL after a successful registration.

> If you are using custom fields in your registration form, you should set the `<form>`'s `enctype` attribute to "multipart/form-data".  This is necessary for the form to handle file uploads.

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
			<td>Specify the name for the returned variable array (e.g., "form" returns an array with keys like `{$form.return}`.</td>
		</tr>
		<tr>
			<td>return</td>
			<td>No</td>
			<td>Specify an absolute (e.g., http://www.example.com) or relative (e.g., /members_area) URL to redirect the user to after registering.</td>
		</tr>
	</tbody>
</table>

Available block variables:

* `{$var.form_action}` (the value for the form's "action" attribute")
* `{$var.return}` (the encoded value for the hidden "return" field)
* `{$var.custom_fields}` (a standard custom fields array of registration form variables)

Example registration form:

```
{registration_form var="form" return=$return}
	<h1>Create an Account</h1>
	
	<p>Complete the fields below to create your account at {setting name="site_name"}.</p>
	{if $setting.validate_emails == "1"}
		<p>You will be required to validate your email address before your account is fully activated.</p>
	{/if}
	
	<form class="form validate" enctype="multipart/form-data" method="post" action="{$form.form_action}">
		<input type="hidden" name="return" value="{$form.return}">
		
		{if $validation_errors}
			<div class="errors">
				{$validation_errors}
			</div>
		{/if}
	
		<fieldset>
			<legend>Access Information</legend>
			<ul class="form">
				<li>
					<label for="username">Username</label>
					<input type="text" class="text required" id="username" name="username" value="{if $values.username}{$values.username}{/if}">
				</li>
				<li>
					<label for="email">Email</label>
					<input type="email" class="text required" id="email" name="email" value="{if $values.email}{$values.email}{/if}" />
				</li>
				<li>
					<div class="help">After registering, you will be able to login with either your username or your email.</div>
				</li>
				<li>
					<label for="password">Password</label>
					<input type="password" class="text required" id="password" name="password" />
				</li>
				<li>
					<div class="help">Passwords must be greater than 6 characters in length.</div>
				</li>
				<li>
					<label for="password2">Repeat Password</label>
					<input type="password" class="text required" id="password2" name="password2" />
				</li>
			</ul>
		</fieldset>
		
		<fieldset>
			<legend>Profile Information</legend>
			<ul class="form">
				<li>
					<label class="full" for="first_name">First Name</label>
				</li>
				<li>
					<input type="text" class="text required" id="first_name" name="first_name" value="{if $values.first_name}{$values.first_name}{/if}">
				</li>
				<li>
					<label class="full" for="last_name">Last Name</label>
				</li>
				<li>
					<input type="text" class="text required" id="last_name" name="last_name" value="{if $values.last_name}{$values.last_name}{/if}">
				</li>
				{foreach $custom_fields as $field}
					{if $field.name}
						{if $field.type != 'checkbox'}
							<li>
								<label class="full" for="{$field.name}">{$field.friendly_name}</label>
							</li>
							<li>
								{custom_field value=$values[$field.name] field=$field}
							</li>
						{else}
							<li>
								{custom_field value=$values[$field.name] field=$field} <label style="display: inline; float: none" for="field_{$field.name}">{$field.friendly_name}</label>
							</li>
						{/if}
						{if $field.help}
						<li>
							<div class="help flush">{$field.help}</div>
						</li>
						{/if}
					{/if}
				{/foreach}
			</ul>
		</fieldset>
		
		{if $setting.require_tos == "1"}
		<fieldset>
			<legend>Terms &amp; Conditions</legend>
			<textarea style="width: 85%; height: 200px" class="text">{$setting.terms_of_service}</textarea>
			<p>
				<input type="checkbox" value="1" name="agree_tos" /> I agree to the terms and conditions above.
			</p>
		</fieldset>
		{/if}
		
		<input type="submit" class="button" name="go" value="Create Account" />
	</form>
{/registration_form}
```

[tag]{members}[/tag]

Retrieve data for members matching the passed parameters.  This can be used to display a members list, search a members database, or to gather information on administrators or members in other fashions.

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
			<td>Specify the name for the returned variable array (e.g., "form" returns an array with keys like `{$form.return}`.</td>
		</tr>
		<tr>
			<td>id</td>
			<td>No</td>
			<td>Retrieve a specific member by ID.</td>
		</tr>
		<tr>
			<td>email</td>
			<td>No</td>
			<td>Search by email address.</td>
		</tr>
		<tr>
			<td>username</td>
			<td>No</td>
			<td>Search by username.</td>
		</tr>
		<tr>
			<td>name</td>
			<td>No</td>
			<td>Search by first or last name.</td>
		</tr>
		<tr>
			<td>group</td>
			<td>No</td>
			<td>Only retrieve users in this particular group (e.g, "2").  You can also join multiple groups like "2|5|6".</td>
		</tr>
		<tr>
			<td>*custom fields*</td>
			<td>No</td>
			<td>You can search by any custom member data field.  For example, if you have a custom field called "School" with a system name of "school", you can add "school" as a parameter to search by school.</td>
		</tr>
	</tbody>
</table>

After a `{members}` call, a `{$members_total_count}` variable is available for the `{paginate}` tag to do some pagination.

Example usage:

```
<table class="table" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<td style="width: 25%">Name</td>
			<td style="width: 20%">Company</td>
			<td style="width: 20%">School</td>
			<td style="width: 20%">Chapter</td>
			<td style="width: 15%">Date of Initiation</td>
		</tr>
	</thead>
	<tbody>
		{members var="member" group="3" sort="user_last_name" sort_dir="ASC" limit="100"}
			{assign var="member_id" value=$member.id}
			<tr>
				<td>
					<a href="{url path="members/profile/$member_id"}">{$member.last_name}, {$member.first_name}</a>
				</td>
				<td>
					{$member.company}
				</td>
				<td>
					{$member.school_name}
				</td>
				<td>
					{$member.chapter_name}
				</td>
				<td>
					{$member.year_of_initiation}
				</td>
			</tr>
		{/members}
	</tbody>
</table>

{paginate variable="page" base_url=$current_url total_rows=$members_total_count per_page="100"}
```