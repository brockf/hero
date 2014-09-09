# Content

At the heart of any website is its content - the blog posts, static pages of text/images, perhaps tables of statistics, lists, etc.  However, not every websites content is the same.  So, Hero allows you to define custom content types and build each content type's database with custom fields.  For example, a site about hiking in Canada may have content types like "regions", "trails" (linked to regions), "reviews" (linked to trails), "blog posts", and "static pages".

> Developers: This is the same custom field engine that powers all other custom fields in Hero.  If you require a fieldtype for your content that is not already in the system, [create a new custom fieldtype](/docs/developers/forms.md).

## Defining Content Types

It's very easy to define new content types.  You do it right within the control panel, at Publish > Content Types.

Before you specify your content fields, you must configure the following:

* *Content Type Name* - Plural, create the name for your content (e.g., "Blog Posts").
* *Uses Standard Page Fields?* - By enabling this, your content of this type will immediately have a *Title* field, a publish date, be able to be linked to topics, and be able to be bound to a specific website URL.
* *Restricted to Member Groups?* - By enabling this, you can specify that some content can only be seen by certain member groups.  If a user outside of this group or groups tries to load the content, you can [configure to show a paywall, or only show part of the content, etc.](/docs/configuration/member_groups.md).
* *Output Template* - Specify the template file which will be used to display this content.
* *Base URL Path* - If specified, all URLs for this content will (by default) be prefixed with this path.  For example, for a Blog Posts type, you may want each URL prefixed with "blog/".  The author can choose to change or get rid of this prefix at publish time.

After the main details of the type are configured, you can add an unlimited number of custom fields to the content type.  These fields can be textareas, WYSIWYG HTML editors, text fields, select dropdowns, content relationships, file uploads, checkboxes, and any other fieldtype (there are tons!) [configured in the custom fieldtype engine](/docs/developers/forms.md).

## Publishing Content

Each content type, once created, creates a "Manage X" linked under the Publish tab in the control panel (where X is your content type).  Here, you can view all content of this type, or publish new content.

Content can always be easily edited or deleted after publishing.

## Delayed Content Publishing

By default, the content sets its publish date/time as the current date when you go to publish a piece of content.  However, you have the option of specifying a date/time in the future by simple configuring this date and time in the publish window.  If you do so, the content will not appear anywhere on the website (except the control panel, of course), until that date/time is reached.

## Content URLs

When publishing *standard* content, you can specify a URL path to map this piece of content to.  For example, this allows you to put an About Us page at `yourdomain.com/about_us`, or a blog post at `yourdomain.com/blog/why-my-site-is-awesome.html`.

> All URL conflicts with other content will be avoided by appending numbers to the end of the URL (e.g., "_1", "_2", etc.).  However, if you specify a URL that conflicts with another module (e.g., "/user", or "/store"), this will override and hide that URL.

## WYSIWYG Editor

As mentioned above, one of the fieldtypes you can use when specifying the custom fields for a content type is the WYSIWYG editor.  The WYSWIYG editor gives you total control over the HTML for your content without the need for you to know any HTML at all.  You can upload and place images, videos, tables, and formatted text in the content box with ease.

## Displaying Content

There are a variety of ways for displaying your site's content.  First, it can be loaded into [blogs](/docs/publishing/blogs.md) and [RSS feeds](/docs/publishing/rss_feeds.md).  Second, it can be dynamically loaded into any template by [using a variety of standard template tags](/docs/designers/reference/publish.md).