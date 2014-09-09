# User Model

Create, modify, retrieve, and delete member records, create and delete logged-in sessions.  This model provides a powerful API for developers to integrate and interact with the Hero user system, particularly to retrieve information about the currently logged-in user.

This model also provides an API to access "customer" records for users.  Customer records are the billing address and name that are linked to a user's account.  These are kept apart from the user records, because billing information is often not equivalent to the member's profile data.  It also provides a separate set of data for the billing engine to use (the billing models at `/app/modules/billing/models/` will interact with customer records, not user records).

## Initialization

This model is loaded automatically in both the frontend and control panel's controllers.

It's methods are accessible as the `user_model` object of the CI superobject.

## Method Reference

## `boolean login (string $username , string $password [, boolean $remember = FALSE])`

Attempt a login by username and password.  Passing the third `$remember` parameter will place a random has key cookie on the visitor's computer, linked to a database record, so that they will be auto-logged-in on their next visit (i.e., the "Remember Me" checkboxes).

```
$username = $this->input->post('username');
$password = $this->input->post('password');

if ($this->user_model->login($username, $password)) {
	redirect('home/logged_in');
}
else {
	show_error('Login failed!');
}
```

## `boolean login_by_id (int $user_id)`

Login a user by user ID.  This method is used internally by `login()` after the user's details are validated.

## `boolean logout ()`

End the logged-in user's session.  It will also remove the user's "remember me" key set in `login()`, if one exists.

## `boolean is_admin ()`

Is the logged in user an administrator?  Returns `FALSE` if the user is not an administrator of it the user isn't logged in.

```
if ($this->user_model->is_admin()) {
	echo 'Hi Admin';
}
```

## `boolean logged_in ()`

Is the visitor logged in?

```
if (!$this->user_model->logged_in()) {
	echo 'Please <a href="' . site_url('users/login') . '">login now</a>';
}
```

## `boolean in_group (int|array $group [, int $user_id = FALSE])`

Verify whether the user belongs to any of the groups in `$group` array of usergroup ID numbers.  Alternatively, only one group ID can be passed as an integer.

If the second parameter is not passed, the logged-in user will be assessed.

## `boolean not_in_group (int|array $group [, int $user_id = FALSE])`

Verify that the user does not belong on in any of the groups passed in the `$group` array.  You may also pass only one group ID as an integer.

If the second parameter is not passed, the logged-in user will be assessed.

## `string get (string $parameter)`

Retrieve a piece of data about the logged in user.

```
$username = $this->user_model->get('username');
$email = $this->user_model->get('email');
$user_id = $this->user_model->get('id');
$first_name = $this->user_model->get('first_name');
$last_name = $this->user_model->get('last_name');

// also works with any custom data you might have, referenced by field name
$school = $this->user_model->get('school');
```

## `array get_active_subscriptions (int $user_id)`

Retrieve an array of all active subscriptions linked to a user's account.  If none exist, returns `FALSE`.

## `array get_subscriptions (int $user_id , array Array)`

A wrapper for the [subscription model](/docs/developers/reference/subscription_model.md)'s `get_subscriptions()` method that sets a user filter automatically.

## `int get_customer_id (int $user_id)`

Retrieve the ID of the customer record (in table `customers`) linked to the user's account.

## `boolean set_charge_id (int $user_id , int $charge_id)`

During a 3rd party checkout (e.g., PayPal), we wait until the order is confirmed before processing the order.  So, the pending charge ID is stored in the user's record while they head off and finish the transaction.  This method sets the pending charge ID.  If they never finish the charge, it will just sit here.

## `boolean remove_charge_id (int $user_id)`

Unset the charge ID set by `set_charge_id()`.  The charge either completed successfully or never completed.

## `array validation ([boolean $editing = FALSE [, boolean $error_array = TRUE]])`

Validate POST data for the creation of a new user, includes validation of custom member data fields.  Results can be returned either as an error or an HTML-formatted string.  If editing, the first parameter should be set to `TRUE` so that we don't look for a unique username and email.

## `boolean validate_billing_address (int $user_id)`

Is the billing address on file in the linked customer record valid?  In essence, is it missing any data?

## `array get_billing_address (int $user_id)`

Retrieve the billing address on file for this user.  It will be returned as an array of address elements ("address_1", "address_2", "city", "state", "country", "postal_code").

## `boolean unique_email (string $email)`

Is the submitted email address unique?  Returns `TRUE` if it's unique.

## `boolean unique_username (string $username)`

Is the submitted username unique?  Returns `TRUE` if it's unique.

## `array add_group (int $user_id , int $group_id)`

Add the user to the group specified in `$group_id`.  Users can be added to as many groups as you wish.

## `array remove_group (int $user_id , int $group_id)`

Remove a user from a group.

## `boolean resend_validation_email (int $user_id)`

If the user's email account is not yet validated, you can resend their validation email which has a link to validate their account.

## `int new_user (string $email , string $password , string $username , string $first_name , string $last_name [, array $groups = FALSE [, int $affiliate = FALSE [, boolean $is_admin = FALSE [, array $custom_fields = array() [, boolean $require_validation = FALSE]]]]])`

Create a new user.  User records are quite simple, as you can see from the function parameters.  The user does not necessarily have to belong to any user groups.

Custom member data can be passed as an array with name => value pairs.  This is likely generated by the [form builder](/docs/developers/reference/form_builder_library.md)'s `post_to_array()` method.

If `$require_validation` is set to `TRUE`, a validation key will be stored with the user's record.  By default, an email will be sent out explaining that the user must validate their email or their access will be revoked.  Indeed, this is what happens.  The user can login without validation for 24 hours but, after 24 hours, they will be denied access until the validate.  So, don't delete the email that hooks to the *member_validate* hook or users will have no way of confirming their email!

Arguments:

* `$email` - Email Address
* `$password` - Password to use
* `$username` - Username
* `$first_name` - First name
* `$last_name` - Last name
* `$groups` - Array of group ID's to be entered into (default: FALSE)
* `$affiliate` - Affiliate ID of referrer.  Ignored for now. (default: FALSE)
* `$is_admin` - Set to TRUE to make an administrator (default: fALSE)
* `$custom_fields` - An array of custom field data (default: array())
* `$require_validation` - Should we require email validation? (default: FALSE)

## `int update_user (int $user_id , string $email , string $password , string $username , string $first_name , string $last_name [, array $groups = FALSE [, boolean $is_admin = FALSE [, array $custom_fields = array()]]])`

Update an existing user record.

## `boolean update_billing_address (int $user_id , array $new_address )`

Update a customer record with a new billing address.  Must include the keys:

* *first_name*
* *last_name*
* *address_1*
* *address_2*
* *city*
* *state* (possibly blank, if not in the USA or Canada)
* *country*
* *postal_code*

## `void delete_user (int $user_id)`

Mark a user as deleted in the database.

## `boolean update_password (int $user_id , string $new_password)`

Update a user's password.

## `boolean reset_password (int $user_id)`

Reset a password randomly.  This will trigger the *member_change_password* hook which, unless deleted, will send an email with the new password.

## `void suspend_user (int $user_id)`

Suspend a user.  This removes their ability to login.

## `void unsuspend_user (int $user_id)`

Unsuspend a user.  Re-activate logins.

## `void get_user (int $user_id , boolean $any_status)`

Retrieve a user record, in the format of `get_users`.  If `$any_status` is set to TRUE, a user record will be retrieved even if it has been deleted.

## `int count_users ([array $filters])`

Retrieve a count of the number of users who match the optional filters specified.  These filters match the `get_users()` method.

## `array get_users ( [array $filters = array() [, boolean $any_status = FALSE ], [ boolean $counting = FALSE ]])`

Retrieve an array of matching user records (as separate arrays) that match the optional filters.  If you specify `$any_status` as TRUE, even deleted records will be matched against your filters and returned.

Possible Filters: 

* int *id* - The user ID to select
* int *group* - The group ID to filter by
* int *suspended* - Set to 1 to retrieve suspended users
* string *email* - The email address to filter by
* string *name* - Search by first and last name
* string *username* - Member username
* string *first_name* - Search by first name
* string *last_name* - Search by last name
* date *signup_start_date* - Select after this signup date
* date *signup_end_date* - Select before this signup date
* string *keyword* - Search by ID, Name, Email, or Username
* string *sort* - Field to sort by
* string *sort_dir* - ASC or DESC
* int *limit* - How many records to retrieve
* int *offset* - Start records retrieval at this record

> You may also use any member custom field name (e.g., "school_name" for a "School Name" custom field) as a search parameter.  Each comparison will be a LIKE comparison so that a school_name filter of "Cambridge" will match either "Cambridge" or "University of Cambridge", etc.

Each user returns an array with the following data:

* *id*
* *is_admin*
* *customer_id*
* *usergroups* - An array of usergroups that the user is part of
* *first_name*
* *last_name*
* *username*
* *email*
* *referrer*
* *signup_date*
* *last_login*
* *suspended*
* *admin_link*
* *remember_key* - Their unique "remember me" key created by `login()`
* *validate_key* - Their possible email validation key created by `new_user()`
* *cart* - Their cart array, if available.  This is stored to the user record in the [cart model](/docs/developers/reference/cart_model.md).
* *pending_charge_id* - Their possible pending charge ID stored by `set_charge_id()`
* All *custom member data fields* will be returned with their field name as the key in the array

## `int new_custom_field (int $custom_field_id [, string $billing_equiv = '' [, boolean $admin_only = FALSE [, boolean $registration_form = TRUE]]])`

After a custom field has been created in the [custom fields model](/docs/developers/reference/custom_fields_model.md), a specific user custom field record must be created by this method because user custom fields have unique attributes.

Arguments:

* `$custom_field_id` - The ID corresponding to the custom field ID returned by `new_custom_field()` in the [custom fields model](/docs/developers/reference/custom_fields_model.md).
* `$billing_equiv` - If this field represents a billing address field (e.g, "address_1"), specify here:  options: address_1, address_2, state, country, postal_code, company (default: '')
* `$admin_only` - Is this an admin-only field? (default: FALSE)
* `$registration_form` - Should we show this in the registration form? (default: TRUE)

## `boolean update_custom_field (int $user_field_id [, string $billing_equiv = '' [, boolean $admin_only = FALSE [, boolean $registration_form = TRUE]]])`

Update an existing user ustom field record's user field-specific attributes.

## `boolean delete_custom_field (int $id)`

Delete the corresponding user custom field record for a custom field.

## `boolean get_custom_field (int $custom_field_id)`

Retrieve custom field data for a user custom field.  This is very similar to the [custom fields model](/docs/developers/reference/custom_fields_model.md) method of the same name, except that it returns the additional attributes stored by this model's `new_custom_field()` method.

## `array get_custom_fields ( [array $filters = array()])`

Retrieve an array of user custom field data, based on optional filters.

Possible Filters:

* int *id* - A custom field ID
* boolean *registration_form* - Set to TRUE to retrieve registration form fields
* boolean *not_in_admin* - Set to TRUE to not retrieve admin-only fields

