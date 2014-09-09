# Subscription Plans

Beneath Hero lies a powerful recurring billing engine, capable of handling auto-recurring payment subscriptions on a number of [payment gateways](/docs/configuration/payment_gateways.md).  These recurring transactions are guided by subscription products (called "subscription plans" in Hero) and are purchased by your users through the standard checkout process.

Subscription plans are managed at *Storefront > Subscription Plans*.

Creating subscription plans is very simple; they will automatically be listed and purchasable by visitors to your website (unless you hide them in your theme or de-activate them, of course).  This guide will review the elements of a subscription plan and some common questions regarding subscription plans in Hero.

## Subscription Plan Configuration

Each subscription plan has the following configurable options (most are optional as they all have default settings):

* *Plan Name* - The name of the subscription plan as it should be listed in various areas of your Hero install.
* *Description* - A brief description of the subscription plan.  This may be used in your frontend in a subscription listing, or elsewhere.
* *Recurring Charge* - You can specify if the subscription is free or if there should be a recurring subscription payment every X days.
* *Initial Charge* - Sometimes, the initial charge for a subscription (on the day of purchase) is different from the recurring charge.  For example, if you have a setup fee for your product, or a low one-time fee to entice customers.
* *Taxes Apply?* - Should taxes apply to this subscription plan's prices?  Of course, if there are no [tax rules configured](/docs/configuration/taxes.md), or the user does not match the tax rules specified, no tax will be charged.
* *Charge Interval* - Specify how many days Hero should wait before autocharging the subscriber the recurring charge for this subscription.  Currently, this can only be specified in "days" (not months, weeks, etc.).
* *Total Occurrences* - While many subscriptions run for as long as the member pays, other subscriptions should expire after X payments.  Here, you have the option to specify how many payments (including the first payment) should be charged for the duration of the subscription.
* *Free Trial Period* - You can offer a free trial (number of days before the first recurring charge) for your subscription.  If you do use a free trial, you have the option of *requiring or not requiring billing information upon signup*.  If you do require billing information, the member's subscription will be autocharged after the trial period unless they cancel beforehand.
* *Promotion* - Specify the member group that active subscribers should be promoted to when they subscribe.  They will automatically be removed from this group upon subscription end (unless they have another active subscription which promotes to this group).
* *Demotion* - Similar to the promotion option, except that the member is added to this member group when their subscription expires.

> Subscription plans with free trials cannot have a unique first charge.  The first charge to the user will be the recurring charge rate.

## Using Subscriptions to Protect Content

The key to making some of your content "subscribers-only" is in the promotion/demotion groups.

### One Subscription Plan

If you have a single subscription plan for full access to your site, you should create a member group for users who purchase this subscription plan to be promoted to.  Then, by specifying (during [publishing](/docs/publishing/content.md)) that users must be in this member group to view the content, you are essentially creating a paywall that will force users to subscribe in order to see the content.  Users who do not have subscriptions will, by default, see a "Login/Register/Subscribe" screen.  However, with [theme customization](/docs/designers/reference/paywall_privileges.md), you can show them anything you would like.  This could be a snippet from the hidden content, an advertisement, or a "get out now!" screen.

### Multiple Subscription Plans

If you have varying levels of subscriptions, or different subscriptions for different site areas, you would simply create more member groups and assign content to be limited to one or more of these member groups upon access.  This allows you to have subscription tiers (e.g., Gold subscribers see all content but Silver subscribers only see some) as well as multiple content areas of the site (e.g., a Golf subscriber sees golf content, but a Ski subscriber sees ski content, and someone subscribed to both sees all of your content).

## Frequently Asked Questions

### Why can I only specify the recurring charge interval in days?

For compatibility across all gateways, intervals are only allowed to be days.  Gateways have different ways of calculating weeks, months, and years, and some don't allow these as intervals at all.  Rather than have poor subscription records, Hero uses day intervals for precision recurring charges.

### What happens when a member cancels their subscription?

If a member cancels their subscription, their subscription will be set to expire on their next charge date.  They will have access until this date, unless you manually remove them from whatever member groups to which they were promoted.

### What happens to my existing subscribers if I modify a subscription plan?

If you modify a subscription plan, your existing subscriptions will be unaffected.  Even if you cancel an existing subscription plan, ongoing subscriptions will continue until they cancel or expire, themselves.

### Can coupons be used with subscriptions?

Coupons can be used with subscriptions.  They can be configured to reduce the price of the subscription (both initial charge and recurring charge) or create/extend a free trial on a subscription.  [Click here to learn more about creating and configuring coupon codes](/docs/configuration/coupon_codes.md).

### How do you handle the user's billing data for subscription payments?

*No credit card information is stored locally*.  All [integrated payment gateways](/docs/configuration/payment_gateways.md) use the gateway's native recurring billing features to store the credit cards securely and without risk to you, the website administrator.

