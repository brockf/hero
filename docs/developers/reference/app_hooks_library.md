# App Hooks Library

Hooks are system actions such as *store_order*, *member_registration*, and *subscription_new* that serve as possible triggers for emails or method/function calls.

All users, even novice users, will configure for emails to be sent by adding/editing emails attached to hooks at *Configuration > Emails* in the control panel.  [Learn more about configuring emails](/docs/configuration/emails.md).

Developers and advanced users can use the App Hooks library to extend Hero's functionality beyond what it is today by tying it into third-party software, weaving new routines into the software, etc.  You can bind any `class->method()` call to a hook, but you can also bind any simple `function()` call as long as you specify the `$filename` during your `bind()` creation.

Most hooks will automatically pass arguments to your called methods/functions.  These are not comprehensive arguments, but rather ID's that can be used to retrieve full details of the relevant system action via other methods like `$this->invoice_model->get_invoice()`.

## Default Available Hooks

The following hooks are available in an Hero installation, by default.  They are stored in the `hooks` table of your database.

* *checkout_billing_shipping* - In checkout, the billing/shipping submission has been processed.
* *checkout_shipping_method* - In checkout, the shipping method has been selected.
* *checkout_payment* - In checkout, the payment has been successfully processed.
* *cron* - The daily cronjob for maintenance updates.
* *coupon_validate* - A coupon is attempted to be applied during checkout.  (arguments: $coupon_id)
* *mass_email* - An email is sent from the control panel's Send Email function. (arguments: $recipients, $subject, $body, $queued)
* *member_change_password* - A member changes their password. (arguments: $user_id, $new_password)
* *member_delete* - A member account is deleted. (arguments: $user_id)
* *member_forgot_password* - A member requests a new password via the "Forgot Password" feature.
* *member_login* - A member logs in. (arguments: $user_id, $password)
* *member_logout* - A member logs out. (arguments: $user_id)
* *member_register* - A new member account is created. (arguments: $user_id, $password)
* *member_suspend* - A member account is suspended. (arguments: $user_id)
* *member_update* - A member account is updated. (arguments: $user_id, $old_user_data)
* *member_unsuspend* - A member account is unsuspended. (arguments: $user_id)
* *member_validate_email* - A member must validate their email address after registration.
* *new_content* - A content item is published (arguments: $content_id)
* *new_topic* - A topic is created (arguments: $topic_id)
* *store_order* - An order is made from the store (includes at least one product). (arguments: $invoice_id)
* *store_order_product* - A product is ordered from the store (hook called for each product). (arguments: $invoice_id, $product_id)
* *store_order_product_downloadable* - A downloadable product is ordered from the store (hook called for each downloadable product). (arguments: $invoice_id, $product_id)
* *subscription_cancel* - A subscription is cancelled. (arguments: $subscription_id)
* *subscription_charge* - A subscription charge is made. (arguments: $invoice_id, $subscription_id)
* *subscription_expire* - A subscription expires. (arguments: $subscription_id)
* *subscription_expire_1_month* - A subscription will expire in 1 month. (arguments: $subscription_id)
* *subscription_expire_1_week* - A subscription will expire in 1 week. (arguments: $subscription_id)
* *subscription_new* - A subscription is created. (arguments: $subscription_id)
* *subscription_renew* - A subscription is renewed. (arguments: $subscription_id)
* *subscription_renew_1_month* - A subscription will renew in 1 month. (arguments: $subscription_id)
* *subscription_renew_1_week* - A subscription will renew in 1 week. (arguments: $subscription_id)
* *subscription_renewal_failure* - A subscription charge fails. (arguments: $subscription_id)
* *update_content* - A content item is edited (arguments: $content_id)
* *update_topic* - A topic is edited (arguments: $topic_id)
* *view_content* - A content item is viewed in the standard controller (arguments: $content_id)

## Creating New Hooks in Custom Modules

You can register a new hook in any of your custom module installation routines by calling this library's `register()` method documented below.

This hook can then be bound to like anything else.  Look at the `trigger()` method below for how to trigger your system hooks once they've been created.  This includes adding data to the hook which can be used in emails or passed to subsequently bound method/function calls.

## Initialization

This library is loaded automatically by both `Front_controller` and `Admincp_controller`.  So, if you are using a standard module or your new module is extending these classes (as it should!), you can access this library with:

```
$this->app_hooks->...();
```

Otherwise, you'll have to load the library:

```
$this->load->library('app_hooks');
```

## Method Reference

## `int bind (string $hook , string $class , string $method , string $filename)`

Bind a class/method or function to a hook.  These classes can be CodeIgniter standard models or libraries, but can also be outside classes located in other PHP files.  If you are only using a simple `function()` call that is not part of a class, set `$class` to FALSE.

The `$filename` should be relative to your root Hero folder.  For example, to bind a method in a library file in your new module, "my_new_module":

```
$this->app_hooks->bind('member_register', 'My_library', 'create_user_in_my_app', 'app/modules/my_new_module/libraries/my_library.php');
```

Now, whenever a user registers, that library will be loaded (if not already loaded) and a call will be made by App Hooks like so:

```
$this->load->library('my_new_module/my_library');
$this->my_library->create_user_in_my_app($user_id, $password);
```

Note that two arguments were sent to your method.  These arguments are specified when triggering the hook and differ from hook to hook.  In this case, for a member registration, it makes sense to pass the user's system ID and their password (something you cannot retrieve from a `user_model->get_user()` call).

## `array get_hooks ()`

Return an alphabetically sorted array of all hooks.

## `array get_hook (string $name)`

Return the configuration of one particular hook.  Configuration data:

* *id* (arbitrary ID in the hooks table, not important)
* *name*
* *description*
* **email_data** - An array of all potential data types available in emails (none, one, or more of "member", "product", "order", "invoice", "subscription", and "subscription_plan").
* **other_email_data** - One off additional variables available in the emails (e.g., 'new_password' during a password change).  These are not standard variables included in one of the data types above, but unique to specific system actions.

## `int register (string $name , string $description , array $email_data , array $other_email_data)`

Create a new hook in the system.  Configuration details are equivalent to the `get_hook()` method.  This method is likely called in your [module definition file](/docs/developers/modules.md) as part of an installation/upgrade routine.

```
// we are accessing the CI superobject via $this->CI because this example comes from a module definition file
$this->CI->app_hooks->register('subscription_renewal_failure','A subscription charge fails.',array('member','subscription','subscription_plan'));
```

## `boolean data (string $data , int $id)`

Set email data using a general datetype when calling a trigger.  This method is called potentially multiple times before a hook is triggered.  It is used to initiate the loading of standard data variables into the hook's email variable options.  For example, by simply specify `$this->app_hooks->data('member', $member_id);` (assuming that member ID is valid), we are making variables like *{$member.username}*, *{$member.email}*, *{$member.first_name}*, and *{$member.last_name}* available to emails that are sent with this hook.  For more information on emails, [check out the guide to configuring emails](/docs/configuration/emails.md).

```
$this->app_hooks->data('member', $user_id);
$this->app_hooks->data('invoice', $invoice_id);
$this->app_hooks->trigger('paid_invoice');
```

## `void data_var (string $name , string $value)`

Specify a single variable to add to the email data accumulating before a hook call with `trigger()`.  These are one-off variables unique to a hook, not standard data types like *member*, *invoice*, or *subscription*.

```
$this->app_hooks->data('member', $user_id);
$this->app_hooks->data_var('new_password', $new_password);
$this->app_hooks->trigger('change_password');
```

## `void trigger (string $name [, $arguments ])`

Trigger a hook.  This will send out all emails that match the hooks current data parameters, and also trigger the calling of methods/functions bound to this hook.  If additional arguments are passed, they will be passed along to the methods/functions bound to this hook.

This is an example of a real trigger call for the *member_change_password* hook:

```
// load the CI superobject
$CI =& get_instance();

// prep the hook with data
$CI->app_hooks->data('member', $user_id);
$CI->app_hooks->data_var('new_password', $new_password);

// trigger with 2 additional arguments
$CI->app_hooks->trigger('member_change_password', $user_id, $new_password);

// be kind, reset the hook's data so that the next hook trigger doesn't accidentally pass email data that doesn't exist
$CI->app_hooks->reset();
```

## `void reset ()`

Reset the current data being attached to a hook call.