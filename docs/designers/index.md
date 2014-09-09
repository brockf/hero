# Designers' Guide

While the average end user of Hero should be impressed by its power, only the designers (and [developers](/docs/developers/index.md)) can truly appreciate the ease of building websites with this platform.  There are no design limitations and, more importantly, there is a toolbox of features that allow you to build websites in fractions of the time.

But talk is cheap!  Let's take a look in this toolbox to see what you really have to work with:

## The Designer's Toolbox

### Templates

*Templates* are HTML files that contain [Smarty Template Engine](/docs/designers/smarty.md) code to load dynamic content from Hero (content, products, user information, etc.) into your web page upon each page load.  For example, a template for displaying a blog post (e.g., `blog_post.thtml`) may use Smarty template variables like `{$title}` and `{$post_body}` in place of where the Title and Post Body fields should be displayed on the web page.  These variables aren't random - they are defined by the module displaying the page, the content type being displayed (e.g., blog posts might have different variables than a content type holding data about park trails), and the template plugins available to you.  We'll read more about template plugins and their immense power in a minute, though.

> All templates have a `.thtml` extension.  This stands for Template HTML.  Using this extension allows us to protect these files from ever being accessed directly by malicious users.

Here's an example template, below.  It displays a series of blog posts.  Just as a reminder, we're talking a lot about blog posts but your site may not have this content type at all - it's all about the [content types you define](/docs/publishing/content.md).

```
{extends file="layout.thtml"}
{block name="title"}
{$title} - {$smarty.block.parent}
{/block}
{block name="content"}
	<h1>{$title}</h1>
	
	<ul class="blog">
	{foreach $content as $item}
		<li>
			<h4 class="date">{$item.date|date_format:"%A, %B %e, %Y"}</h4>
			<a href="{$item.url}">{$item.title}</a>
		</li>
	{/foreach}
	</ul>
	
	{$pagination}
	
{/block}
```

This template extends our main layout template, passes a page title, and then iterates through the content items to display each post with a formatted date, and title.  At the bottom, it displays pagination links.  For more information on displaying blogs/archives, check out the [designer reference page for blogs/archives](/docs/designers/reference/blogs.md).

### Themes

Templates are grouped into packages called *themes*.  This allows designers to easily save, backup, and share groups of template files that are entirely self-contained.  Themes don't just hold template files though, because site designs aren't entirely comprised of HTML.  A complete site design has JavaScript files, CSS Stylesheets, and, for Hero designs, may contain *template plugins*.  All of these files exist in a theme folder at `/themes/`.

### Template Plugins

By using the [Smarty Template Engine](/docs/designers/smarty.md) to power the themes in Hero, we make available a plethora of *template plugins*.  Template plugins are written in PHP, but called with simple template tags in your templates like `{content var="article" type="2" limit="5"}{$article.title}{/content}`.  In that example, we will print out the titles of the last 5 content items from content type #2 (presumably storing "articles").  This plugin is a *block function* because it has an opening and closing tag and does something with the content between the two tags.  Template plugins can be called throughout any template and give you 100% control over the data you display and how it is displayed.

Of course, template plugins do more than just pull data.  The "url" plugin, a *template function* to be precise, is a [global plugin](/docs/designers/reference/global_plugins.md) that exports complete URLs for your site when given a path.  Example: `<a href="{url path="user/register"}">Click here to register</a>` will print `<a href="http://www.yourdomain.com/user/register">Click here to register</a>`.

Finally, there are *modifiers*, the third type of template plugin.  Modifiers are built into Smarty and perform specific processing on a single template variable.  For example, whenever you have a date variable, you can use the "date_format" modifier to format the date into the format you would prefer (e.g, "2010-Dec-31", "Dec 31, 2010", or "December 31/10"): `Article print date: {$article.date|date_format:"%b %e, %Y"}`.

Modules in Hero include their own template plugins and, if you want custom functionality in Hero, you can always [write your own template plugins](/docs/developers/template_plugins.md).  Template plugins are PHP files that can be placed within a theme folder, in the `/themes/_plugins/` folder, or within a module's `/template_plugins/` directory.

More information on how to use block functions, functions, and modifiers is available [in the documentation specifically about template plugins](/docs/designers/template_plugins.md).

### Customizable Content

All content, whether it be custom content types created by yourself, or RSS feeds, blogs, etc., can be displayed using the template of your choice.  So, if you aren't happy with the standard display of your blog, create your own template and set the Output Template to your new template to have complete control over the look and feel of the page.

### Theme Editor

The Theme Editor is an online template file editor that allows you to begin customizing your theme from directly within your control panel (Design > Theme Editor).  You can edit files, specify which template to use for your site's homepage, and even map URL's directly to a template.

### Menu Manager

Don't worry about hard-coding menus in your templates and having your clients mess around with that, use the [Menu Manager](/docs/publishing/menus.md) and the including [{menu} template plugin](/docs/designers/reference/menus.md) to dynamically build and load menus into your templates as standard `<ul>` formatted lists.

### Standard Included Themes

Not sure where to start with Hero?  You don't have to design from scratch.  Take a standard theme folder, rename it, and begin tweaking while you learn the ins and outs of the system.  All themes are built for a global audience and you would be amazed at how quickly some CSS edits and tweaks to the main `layout.thtml` file make your site look brand new.  Having a set of themes to choose and learn from is one of the biggest design resources of Hero.

> Each standard theme is built around one main `layout.thtml` file.  This file wraps around all other pages of the site and includes your header, navigation, footer, etc.  So start here when modifying a standard theme!

## Important notes when customizing Hero themes

* Whenever you are customizing a standard theme, begin your work by renaming the theme folder in `/themes/` so that it won't be overwritten when you update to a later release of Hero.  All internal links in the theme will remain intact (providing they used the [{theme_url} template plugin](/docs/designers/reference/global_plugins.md)).  After you rename your theme folder, simply set your site to use the new theme folder in the control panel at Design > Themes.

* You should always use absolute paths in your links by using the `{url}` ([documentation here](/docs/designers/reference/global_plugins.md)).  This prevents any links from breaking if the site is transferred.