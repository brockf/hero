# Welcome to Hero

Hero is an all-in-one website publishing system and ecommerce framework designed to make building websites and web applications easy, fast, and profitable.  It is specifically built to power subscription websites, large content sites, shopping carts, and membership websites with ease right out-of-the-box.

Hero will save you thousands of hours in programming and architecture design and prides itself on being a website platform for the future - one that can be maintained, extended, and customized by developers of all skill levels.

## Installation

This software is installed directly on your web server or as a client on a multi-tenant web server.  If you are installing the software yourself, it installs via a 2-minute installation wizard and auto-configures with a myriad of default settings.  These are easily customizable after installation.  For more information regarding installation, check out the [/docs/installation/server_requirements](/docs/Installation section of the User Guide.md).

## Application Structure

Hero can be conceptualized as having four complementary units:

* The *control panel* where you will manage your website as an administrator on a day-to-day basis,
* The *frontend* that the public and your website's members will see when they visit your site,
* The *theme* that controls the web design ("look and feel") of your frontend,
* And the *database* that holds all of the data from your website and feeds it to the PHP software files.

The Hero software is built on an industry-standard Model-View-Controller (MVC) framework, [/docs/developers/codeigniter](/docs/CodeIgniter.md).  By using an open framework, Hero makes itself *extremely conducive to customizations and extensions by developers*.  This may include adding new modules, customizing existing modules, adding new fieldtypes for adding custom fields to the system, or performing many other configurations.

## For Everyone

Anyone can use this software.  Right away, just minutes after install, you are at the helm of the most complete and powerful web publishing and ecommerce system on the market.  The control panel is easy to use and provides a simple way for you to learn to use the platform from day one.

Here's an idea of the things you can do with Hero *without any technical knowledge*:

* Create a new [/docs/publishing/content](/docs/content type.md) to manage your list of Sports Teams.  Easily add text fields like "Team Name", "Home Field/Arena", "Coach", and "Ownership".  Then, add some file upload fields for "Logo" and "Jersey".  Then, a checkbox field for whether the team is "In-Season" or not.  And, finally, a relationship field that links this team to another one of your content types, "Sports Leagues".  [/docs/publishing/content](/docs/Learn more about publishing content.md).
* Create an online storefront with different product "collections" and products belong to each collection.  You can even sell some of these products as *downloadable products*, automatically delivered to your user in a secure link after purchase. [/docs/publishing/store](/docs/Learn more about products and store collections.md).
* Create subscription packages for your members to subscribe to.  Then, restrict access to some of your content so that members must have an active subscription of a specific type to view this content.  A subscription membership website in minutes. [/docs/configuration/subscriptions](/docs/Learn more about configuring subscriptions.md).
* Integrate Hero with your payment gateway of choice.  Hero integrates with many popular payment gateways and merchant accounts such as Authorize.net, PayPal, SagePay, and eWAY simply by entering your login/API credentials in the control panel.  [/docs/configuration/payment_gateways](/docs/Learn more about payment gateways.md).
* Customize your website's automated emails sent to users.  Want an email sent every time someone purchases "product X"?  That's no problem.  You can even include data about their order or the product with simple data tags like `{$product.name}`.  [/docs/configuration/emails](/docs/Learn more about configuring emails..md)
* Generate membership and ecommerce reports based on a number of criteria.  Export these reports to CSV files for import into Microsoft Excel or similar software.  [/docs/configuration/reports](/docs/Learn more about reporting.md).

## For Designers

This User Guide has a specific section built for Designers.  In this context, "Designers" refers to people who have at least basic knowledge of HTML and CSS and are looking to change the way their website looks or functions in the frontend.  This involves modifying templates powered by the [/docs/designers/smarty](/docs/Smarty Template Engine.md), modifying CSS stylesheets and JavaScript includes, and harnessing the immense power of Hero's global and module-specific [/docs/designers/template_plugins](/docs/template plugins.md).

The following is a sample of Hero template code.  It is used to display a list of the five latest news headlines on a news site:

```
<ul>
{content type="headlines" var="headline" limit="5" sort="date" sort_dir="desc"}
	<li>
		<a href="{$headline.link}">{$headline.headline}</a> ({$headline.date|date_format: "%m %d, %Y"})
	</li>
{/content}
</ul>
```

See how easy it can be to take the content stored in your database and display it exactly as you desire?  This code can be dropped into any template for a standard Hero module, or even dropped into a new template and mapped to a URL of your choice.

Identical procedures are used to display products, forms, registration forms, your website's checkout, members list, and anything else stored in the system.

The most important principle for modifying Hero is that *there are no frontend design limitations imposed by this platform - anything goes!*.  You have complete control over what HTML is sent to the user's browser.

## For Developers

Although the distinction may be unimportant to some, this User Guide will specifically refer to "Developers".  These are people who likely have the technical skills of designers (though perhaps with less artistic flair) but also who have at least an intermediate knowledge of the [http://www.php.net](/docs/PHP programming language.md) and object-oriented programming.  It is highly suggested that these users have experience with [/docs/developers/codeigniter](/docs/CodeIgniter.md), as this will allow them to understand the basic URL routing and MVC structural principles of this software from the beginning.

It cannot be stressed enough that this platform is specifically built for developers to modify, extend, and configure it.  Here are some specific examples of what a developer can do just by sitting down with the platform for a few hours:

* Create a new fieldtype which takes an uploaded file, pushes it to a cloud-hosting service such as Amazon's S3, and then stores the file reference in the database.  Then, a custom field could be added to the system with this fieldtype for a member avatar to be uploaded to a member profile, or a movie to be posted along with new content, or for an instructional video along with a store product, or anything else you can imagine.  [/docs/developers/forms](/docs/Learn more about custom fields and fieldtypes.md).
* Develop a new module that [/docs/developers/reference/app_hooks_library](/docs/binds to system hooks.md) and uses Hero to power the user management and billing for their web application.  Each profile creation, new subscription, successful payment, subscription expiration, and account deletion is bound to a class/method in this module which performs the necessary actions in the developer's web app (such as granting/denying the user access).  It's a full account management and billing system for a web app in just a couple of hours!  [/docs/developers/reference/app_hooks_library](/docs/Learn more about hooks and bindings.md).
* Create a custom template plugin for a theme that pulls each of the logged-in user's payments made on a Tuesday and displays them by tapping into the [/docs/developers/reference/invoice_model](/docs/Invoice Model.md).  One simple API call and it's done.

## Getting Started

This guide has all the information you need to get started.  If you haven't installed Hero and plan to download and run it on your web hosting server, head over to the [/docs/installation/server_requirements](/docs/server requirements.md) documentation to make sure your environment is alright.  Otherwise, just begin exploring Hero using this User Guide to help you along the way!
		