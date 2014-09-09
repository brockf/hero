# The Smarty Template Engine

Hero is powered by the [Smarty Template Engine](http://www.smarty.net).  This means that its templates follow the same syntax and structure of Smarty templates, that all standard Smarty template plugins and modifiers are available in our templates, and that your site's templates are parsed, cached, and displayed by the latest release of Smarty.

Smarty is open source software, written by the founding developers of the PHP programming language.  Hero uses the latest release of its software, version 3.  This version is faster than ever before and optimized for web servers that use PHP5.

## What You Need to Know

You don't need to know much about Smarty to begin using it in your Hero templates.  It's just like any templating language.  Instead of exporting HTML directly from the software, we make a bunch of variables and plugins available to designers to use in their HTML/Smarty template files.  This gives designers absolute control over what is displayed to the end user.

In order to use Smarty, you should understand the following:

* You don't need to install anything extra to use Smarty.  Smarty files can be edited in any text editor.  Smarty files are basic HTML files and you can link to JavaScript, CSS stylesheets, and other files just like you would in any other web design.

* You have 100% control over the HTML displayed to the end user, because you can customize any template and customize which template is displayed for specific content, etc.

* *Template variables* are placed in template files so that they can be dynamically replaced by data when the web page loads.  For example, we can use one template to display 1000's of blog posts because, instead of writing the blog posts' titles in the template, we use a tag like `{$title}` in place of the title.  ([official documentation](http://www.smarty.net/docs/en/language.syntax.variables.tpl)).

* *Template plugins* ([more information here](/docs/designers/template_plugins.md)) bring advanced functionality to templates, whether you are loading content from your database with the `{$content}` plugin ([documentation here](/docs/designers/reference/publish.md)) or displaying an absolute URL (e.g., `http://www.example.com/user/register`) from a relative path with `{url path="user/register"}` ([documentation here](/docs/designers/reference/global_plugins.md)).  Official documentation on Smarty functions [begins here](http://www.smarty.net/docs/en/language.syntax.functions.tpl) and [here](http://www.smarty.net/docs/en/language.syntax.attributes.tpl).

* *Comments* are denoted like so, and are completely ignored in your template file: `{* This is a comment *}` ([official documentation](http://www.smarty.net/docs/en/language.basic.syntax.tpl)).

* Sets of data are *looped* through with the `{foreach}` syntax ([official documentation](http://www.smarty.net/docs/en/language.function.foreach.tpl)).

* *Conditionals* follow typical if/else structure: `{if $test == "1"}Yes!{else}No!{/if}`.  Can also use elseif: `{if $person == "Mike"}You are Mike{elseif $person == "Paul"}You are Paul{else}You are neither Mike nor Paul.  Who are you?{/if}`.

* You can *assign values* to new template variables with the `{assign}` function ([official documentation](http://www.smarty.net/docs/en/language.function.assign.tpl)).

* If you are really digging into the templates and want to restructure your theme's template directory, you will need to have a basic knowledge of *template inheritance* ([official documentation](http://www.smarty.net/inheritance)).

## How to Learn More

If you are confused about Smarty, want to learn the syntax, or want to know more about all the *default Smarty plugins, functions, and modifiers*, the best place to start is at their comprehensive [documentation site](http://www.smarty.net/docs/en/).

All template plugins that are not part of Smarty but come as part of Hero are documented in this documentation for designers, in the Reference section.