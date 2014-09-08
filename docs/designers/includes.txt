# JavaScript and CSS Stylesheets

Including JavaScript and CSS stylesheets in your website is as easy with Hero as it is with static HTML files or another website publishing platform.  This documentation simply gives some best practices to new designers.

> Instructions in this documentation may be irrelevant if you have built a theme from scratch, as your theme's structure may not match the structure of built-in themes.

## Global Includes

To include a JS/CSS file across every page your website, you only need to edit the `layout.thtml` and add the `<link>` element(s) for CSS stylesheets and `<script>` includes for JS files.

Assuming these files are located within your theme folder (at `/themes/yourtheme/`), you should always use the `{theme_url}` plugin to include these files with an absolute URL so that the includes don't break during transfers, or when your user visits unexpected paths.  By using `{theme_url}`, you also allow users to rename and share your theme without breaking the links or having any theme dependencies.

Example:

```
<head>
	<title>{$setting.site_name}</title>
	<link href="{theme_url path="css/universal.css"}" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="{theme_url path="js/jquery-1.4.2.min.js"}"></script>
</head>
```

You have just included your JS and CSS files.  However, if we're going to nitpick, there's one thing wrong:  You don't have to include jQuery releases in your theme folder.  Because jQuery is so popular and included in standard Hero themes, it is included in the `/themes/_common/` folder.

So, we'll rewrite it like so:

```
<head>
	<title>{$setting.site_name}</title>
	<link href="{theme_url path="css/universal.css"}" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="{url path="themes/_common/js/jquery-1.4.2.min.js"}"></script>
</head>
```

This time, we've used the `{url}` template plugin and given the full path from our root Hero installation folder to the jQuery library file.

## Template-Specific Includes

If you only want to include these files in one template and not across all of your site's pages, you can exploit the template inheritance of the standard Hero templates.

Each standard theme has all templates extending a `layout.thtml`.  This means that this file wraps around all of your site's pages and the subsequent template files simply modify certain "blocks" within the file when they are called (e.g., filling the "content" block with content from a checkout form if the user is checking out, or the homepage if the user is on the homepage).

We also include a block called "head_includes".  If you pass content to this block from within your template, it will be displayed in the `<head>` of your web page, but only when that specific template file is used.

So, if you want to load a JS or CSS file for a specific template, simply use code like the following in your template file:

```
{block name="head_includes"}
	<script type="text/javascript" src="{theme_url path="js/fancy_user_management.js"}"></script>
{/block}
```