# Storefront & Products

The storefront is one of the tools that Hero provides you to generate revenue for your online business.

The storefront manager is an easy way to create and manage your online store by maintaing a database of store products.  *Products* can be linked to *collections* that group products into meaningful categories for your website's visitors.  However, products can also exist without any collections.

## Collections

Collections are groups of products.  They differ from simple "categories" in that a product can appear in multiple collections.  However, collections can be tiered so that there are parent collections, child collections, etc. (e.g., Shoes > Adidas and Shoes > Nike).

By default, most Hero themes will display your products by collections in your store.  However, single products and product lists can be displayed however your designer chooses to do so by [customizing your website's theme](/docs/designers/reference/store.md).

When configuring collections, you need only specify the *name*, *parent collection* (if applicable), and (optionally) the *description* of the collection.

## Products

Products have a vast array of configurable options that you can take advantage of.  However, if you forego these options (like *SKU Numbers*, *inventory tracking*, *member group pricing*, etc.), adding and editing products can be done in seconds.

Products can also be customized to have custom fields unique to your business.  For example, you may want to specify "Frame Size" for each painting if you are an art store.  [Click here for more information on how to configure custom fields for products](/docs/configuration/custom_fields.md).

Product options are described below:

* *Name*
* *Description* - This will appear, by default, on the product's individual listing page
* *Collections* - What collections, if any, should the product belong to?
* *Price*
* *Weight* - If you have [configured shipping rates to be based on weight](/docs/configuration/shipping.md), you will want to specify the weight of our products.
* *Tax rules apply to this product* - If checked, this product will be eligible for tax (providing the visitor meets your [configured tax rules](/docs/configuration/taxes.md))
* *Require shipping address for this product* - If checked, the user will be forced to enter a shipping address during checkout.  This is useful for any physically shipped items, of course, as this shipping address will be available in the control panel later.
* *SKU Number* - Optionally enter a SKU number for the product database
* *Track inventory for this product?* - If checked, the product inventory will be decreased by one after each purchase.  You can optionally set Hero to *stop selling the product at zero inventory*, if this option is checked.
* *Product Options* - Specify any number of product options (described below) which can be configured for this product, and optionally alter the price.
* *Downloadable Product* - Specify a file to send users who purchase this product.
* *Upon purchase, add the user into a member group* - Add the purchasers of this product to one of your configured [member groups](/docs/configuration/member_groups.md).
* *Specify pricing for specific member groups* - Give users in a member group a discount (or charge them more).
* *Product images* - Upload product images to be displayed for the product.  You can also specify the "feature image", or the main product image.

## Product Options

Product options are configured by the user upon adding the product to their shopping cart.  They are options like "Size" or "Color", where the user is expected to select one of many options for their product.  The product's price can be adjusted up or down depending on their selected option(s).

When specifying product options for a specific product, you can choose to *save* the product option for use in other products, so that you don't have to keep reconfiguring each product option for other products if it has the same options.

## How to Sell Downloadable Products

Downloadable products, for the most part, work like any other product in your store.  However, there are two important steps in selling downloadable products:

1) When adding/editing your product at Storefront > Products, you must check the "Downloadable Product" box and specify a download file for the product.  The file can be uploaded via FTP or uploaded right in the product editing form.
2) When users purchase a downloadable product, they will automatically be emailed their purchase from the [store_order_product_downloadable](/docs/developers/reference/app_hooks_library.md) hook.  To configure this email, go to Configuration > Emails in your control panel and find the email associated with this hook.  You will have tags like `{$download_link}` which will automatically insert the unique download link for this user's purchase.