# Shipping Rates

Shipping rates are available to your users in the following scenario:

* They are checking out from your website
* They have selected at least one product that requires a shipping address

However, before these rates appear during checkout, they must be configured in the control panel.

## Global Shipping Configuration

Before creating specific rates, you must generally configure the shipping for your website at Storefront > Shipping > General Configuration.

Here, you can specify which countries you ship to, as well as the default shipping type/rate.

## Shipping Rate Types

Each shipping rate, including the general shipping configuration, must be one of three types:

* *By Weight* - Each products weight is summed and multiplied by a figure to calculate a total shipping rate (e.g., $2.50 per pound, or $3.00 per kilogram).  The weight unit is the default weight unit for your site [configured in the settings](/docs/configuration/settings.md).
* *Per Product* - Charge a shipping fee per product, so that 3 shipped products costs 3 x [your rate] to ship.
* *Flat Rate* - Charge a flat rate for shipping any number of products to a user.

## Shipping Rates

After configuring the default shipping options, you should specify specific shipping rates.  For example, you can specify a rate for users in the United States, or users in California, or users in the United Kingdom.

Each rate is entirely unique and, if the user checking out is eligible for multiple rates, they will be able to select which rate they would like.  You can use this feature to allow for *tiered shipping* (premium and standard shipping rates).

Each rate has a unique name that you specify during customization.