# Emails

Most websites need to communicate with their visitors via email at least infrequently.  For example, after registration, an order from the store, or a successful subscription payment.

Emails are managed at *Configuration > Emails* in the control panel.

Hero allows you to define and configure system emails based on any system action (i.e., [any one of the system hooks](/docs/developers/reference/app_hooks_library.md)).  At each hook point (e.g., "store_order", or "member_register"), different data is available.  This data can be included in the email subject/body dynamically by using tags like `{$member.first_name}`.

All standard emails are pre-configured with your installation of Hero.  However, you may want to add new emails or customize existing emails.  This guide will show you how.

## Email Template Example

```
{extends file="email_layout.thtml"}
		
{block name="body"}

<p>Hi {$member.first_name},</p>

<p>Thanks for your order from {$site_name}!  You were billed {$setting.currency_symbol}{$invoice.amount} on {$invoice.date|date_format:"%B %d, %Y"}.</p>

<p>Your shopping cart:</p>

<ul>
{foreach $products as $product}
     <li>({$product.quantity}) {$product.name}</li>
{/foreach}
{if $subscription_plan}
     <li>{$subscription_plan.name}</li>
{/if}
</ul>

{if $shipping_address}
<p>You have entered the following shipping address:</p>

<p>{$shipping_address}</p>

<p>* If this address is incorrect, please contact us immediately to change it *</p>
{/if}

<p>If you would like to view your invoices or update your account, please visit {url path="users/"}.</p>

{/block}
```

You should notice a few things right away:

* Default emails use simple HTML to ensure proper formatting for the end user.
* Default emails extend a global email template file, `email_layout.html`.
* Email templates follow the same syntax and have the same available features as the [Smarty template code](/docs/designers/smarty.md) used in the frontend of the website.

These observations are helpful in managing automated system emails.  We'll cover the email variables and global email template later in this guide, but first we'll look at important configuration parameters for emails.

## Specifying additional criteria for email sending

Because Hero binds emails to [system hooks](/docs/developers/reference/app_hooks_library.md) like *member_register*, *member_delete*, *subscription_new*, *subscription_charge*, *subscription_expire*, you aren't limited to blindly sending an email simply based on this hook.  You can also specify parameters that must be met before the email is sent.

For example, with hooks that involve a single product (such as *store_order_product*), you can send an email only if the Product matches the product you specify in the email manager.  This allows you to send a specific email such as "How to begin using your new product X" for product X but not product Y.

Potentially, emails can be configured with the following parameters:

* Product
* Member
* Invoice
* Collection

## Specifying email recipients

It is not always the case that you want to send an email to the member who performed the action.  For example, you may want to email the person in charge of shipping when someone makes an order, or email the administrator when a new user signs up.

Each email can be configured to send to *one or more* of the following:

* The *member* associated with the action, if applicable.
* The *administrator* (customizable at *Configuration > Settings > site_email*).
* Any other email address, entered as a comma-separated list of email addresses.

## Available variables at each email hook

When adding/editing emails at *Configuration > Emails* a list of all possible variables associated with that system hook is listed beside the email editor.  You can simply copy and paste the tags you want to use to the email subject or body.

```
... Your order has invoice #{$invoice.id} and was made on {$invoice.date}...
```

Of course, there's much more you can do than just displaying the data.  *These emails are dynamically generated, after all!*  You may want to loop through an array of data:

```
You purchased:

{foreach $order.products as $product}
{$product.name}
{/foreach}
```

... or use any Smarty template plugins and modifiers:

```
... Your order has invoice #{$invoice.id} and was made on {$invoice.date|date_format:"%b %e, %Y"}...
```

For an introduction to Smarty and all the possibilities of dynamic emails, [check out this guide](/docs/designers/smarty.md).

## Editing or removing the global email template

As you saw in the first example of a default system email, default emails extend the global email template.  This allows you, the end user, to make changes to all emails much more quickly (e.g., adding your logo at the top of each email, or a signature to the footer).

The global email template can be edited at *Configuration > Emails > Edit Global Email Layout*.  It is just a normal template file the email templates we've been discussing in this guide.

You can stop using the global email template by either editing it to look like the template below or remove all `{extends file="email_layout.thtml"}` references from the individual emails:

```
{block name="body"}
	
{/block}
```