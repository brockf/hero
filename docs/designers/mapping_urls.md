# URL's, Links, and Templates

When your site is powered by Hero, all URL's within your site's domain (and subfolder, if you use one), become controlled and filtered by Hero.  This is done through a combination of a .htaccess file which passes the application all URL requests that don't point to a real file or folder on your web server, and a powerful internal routing algorithm which routes URL's to specific web pages once passed to the application.

This document outlines the ways URL's map to pages in Hero to give designers, developers, and end users insight into how to get the most out of Hero and how to troubleshoot potential issues.

## Module Links

Module links do not need to be defined anywhere.  Certain links are automatically mapped between module controllers and URL's, based on the existence of a module and the infinite possible controllers it can have in its `/controllers/` folder.

Module controllers map to URL's similarly to how any [CodeIgniter](/docs/developers/codeigniter.md) controllers map to URL's.  However, each controller name is prefaced by the module name.  This is best shown with examples:

> All of these example URL's assume that a module exists called "blogs".  If this module doesn't exist, these would either be met with 404 File not Found errors or some content would need to be mapped to the URL's given in the example.

```
URL: /blogs
Maps to: /app/modules/blogs/controllers/blogs.php
Triggers: The "index()" method of the "Blogs" controller in the "blogs" module.
```

```
URL: /blogs/view
Maps to: /app/modules/blogs/controllers/view.php
Triggers: The "index()" method of the "View" controller in the "blogs" module.
```

```
URL: /blogs/view/page/2
Maps to: /app/modules/blogs/controllers/view.php
Triggers: The "page()" method of the "View" controller in the "blogs" module.
Arguments: "page()" is passed "2" as its first and only argument.
```

This is all the basic structure of [CodeIgniter](/docs/developers/codeigniter.md) URL's and so [its documentation](http://www.codeigniter.com) may provide some more insight.

Many URL's work out of the box like this in Hero, include the search function (at `/search`), the store (at `/store/`), and the user management, login, and registration functions (at `/user/`).

Each important system URL is listed in the Reference section of this designer's guide, under each related module's name (e.g., [the members module](/docs/designers/reference/members.md)).

### Control Panel Links

Many modules have a control panel component as well.  In fact, this control panel part of the module is just a controller called `admincp.php` in the modules controllers directory.

This controller can be triggered by going to `/module/admincp/` like the standard module links.  However, in keeping with creating an "admincp" folder for the control panel, the standard way for module control panels to be accessed is via `/admincp/modulename`.  This will automatically trigger the "admincp" controller for the module.

For more information on module development, [click here](/docs/developers/modules.md).

## Content Links

All standard content types ([see how to create content types here](/docs/publishing/content.md)) have a URL path field when adding/editing content.  This URL path is an easy way to map specific URL directly to that content.  URL's that are taken by other content will automatically be modified so that they are unique.

> If you map a content URL so that it conflicts with a module URL, the content URL will override the module.  These conflicts should be avoided in all cases, unless you intend to hide a module.

## Universal Links Database

The content links described above don't function in isolation.  Hero actually taps into its own universal link database.  (In actual fact, this is a table within the database called "links".)  The universal link database maps URL's to modules, controllers, and templates.

Developers can programmatically map URL's to their controllers/modules/templates/etc. by utilizing the [link model](/docs/developers/reference/link_model.md).

> The Universal Links database makes it easy to map URL's to content, templates, and module controllers without any restrictions on your URL's.  However, it also enables the Menu Manager to list **all** of your site's content so that it can be dragged and dropped into a customizable menu.

## Mapping URL's to Templates

For those who aren't developers, but still want complete control over mapping a URL directly to a template, they can use the Map URL to Template function in the Theme Editor.  When accessing the theme editor, you can simply select a template from the left side.  When that template is loaded in the editor, you can click the "Map URL to Template" button and map a URL directly to this template.  In the background, a link is being added to the universal links database.

When the URL is accessed, the template is displayed after being parsed.  This gives even designers without any programming knowledge the ability to do amazing things with Hero with a combination of this custom URL mapping, templates, and template plugins.  For example, you could create a listing of all your sites members by mapping a `/member_list` URL to a `members_list.thtml` template and retrieving all member data with a `{members}` template plugin call ([documented here](/docs/designers/reference/members.md)).