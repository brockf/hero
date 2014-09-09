# Member Groups

Members of your site can be part of none, one, or many *member groups*.  The list of member groups can be modified at *Members > Member Groups* in the control panel.

Upon signup, the user will be placed in your *default* member group (customizable, of course).

You can always edit a member's member groups by editing their profile at *Members > Member Search > Edit Profile*.

## Subscriptions

Subscriptions have the option to promote a user to a member group upon subscription.  Members will automatically be removed from this member group upon subscription expiration, unless another active subscription promotes them to that group.

Subscriptions can also be configured to demote a user to a specific member group upon expiration.

For more information on subscriptions, [visit the subscriptions configuration guide](/docs/configuration/subscriptions.md).

## Content Access

Hero can limit access to site content based on the member's member group.  This may be useful to those who have content or areas of the site they want to keep semi-private.  It also lies at the heart of Hero's abilities as a subscription membership website platform.

By requiring membership to a particular group, you can essentially force users to be active subscribers to a subscription plan at your website.

In the default setup of Hero, members who attempt to read content of which they cannot access will hit the *Paywall*.  Despite the name "Paywall", this can also just be a template that prompts a user to login, or says anything you wish (if you aren't using it as a paywall - the name doesn't matter).

This paywall [is a configurable template](/docs/designers/reference/paywall_privileges.md) like any other part of the Hero.  It can also be *disabled* so that your templates are verifying the member's privileges themselves, and acting accordingly.  If you are a designer looking to customize the way access is restricted, [this guide to access privileges and the paywall will help you](/docs/designers/reference/paywall_privileges.md).

