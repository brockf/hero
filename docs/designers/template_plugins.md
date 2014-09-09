# Template Plugins

Template plugins are tags that can be used in your templates to bring advanced functionality.  There are three types of template plugins and four places these plugins are defined.

## The Three Types of Template Plugins

There are three types of template plugins.  Each share a similar structure both internally and in their use in your templates, but understanding the differences between them is key to using them properly.

### Block Functions

Block functions are easily recognized because they have an opening tag, closing tag, and some content in the middle.

Example:

```
{block_function}Content in the middle.{/block_function}
```

The content in the middle is passed to the function and, depending on the function, is returned/processed in some manner or another.

For example, Hero makes available to you many functions which retrieve content/products/data from the system for use in your templates.  These are all documented in the Reference area of this documentation.  With these functions, the content between the two tags is parsed as a template in itself and is used to display the content retrieved with the tags.

Example (retrieving all store products in a collection): 

```
{products var="product" collection="5"}<p>{$product.name} - Price: ${$product.price}{/products}
```

In the call to the template function, we specify two *attributes* (sometimes called parameters, or arguments): "var" and "collection".  In standard Hero template plugins, "var" is always used to define the array name for the returned data.  If we had specified var as "prod" (e.g., `{products var="prod"}`), we would have accessed the product name with a different variable: `{$prod.name}`.  The second parameter is specific to the `{products}` tag and specifies that we only want products that are part of collection #5.  Each product exports the HTML between the two tags to the template so, if we have 14 products, there will be 14 `<p>` elements attributed with this little snippet of template code.

Each template function's attributes and returned variables are documented in the Reference section of this documentation.

### Functions

Functions are very similar to block functions, except that there is only one tag called and no data passed between tags.

Example:

```<a href="{url path="subscriptions"}">Click here to subscribe!</a>```

The `{url}` tag in this call doesn't have an opening/closing tag - it's just one call.  It still takes attributes in the tag and almost always returns data in place of the tag.  In this case, we are returning the absolute URL for the relative path passed to the `{url}` tag.  This saves time in writing absolute URL's and also allows us to move the site from one domain or subfolder to another without breaking any links.

Smarty includes many standard functions documented [here](http://www.smarty.net/docs/en/) and all functions unique to Hero are documented in the Reference section of this guide.

### Modifiers

Modifiers are even simpler than functions, though very similar.  Modifiers act directly on template variables and usually perform very limited, specific processing.

For example, to modify the way a date variable is displayed:

```
This article was posted on {$article.date|date_format:"%b %e, %y"}.
```

Here, we have `{$article.date}` holding a date in any standard date format, but we want it to look like "Dec 30, 2010" (if that's the date of publishing, of course).  So, we use the "date_format" modifier to format the variable when it is being displayed.

The only modifiers available to your in your Hero templates are the standard Smarty modifiers, and they are all documented [here in Smarty's documentation](http://www.smarty.net/docs/en/).

## The Four Places Template Plugins are Defined

As mentioned previously in the [section on Smarty](/docs/designers/smarty.md) and the [introduction for designers](/docs/designers/index.md), *template plugins* are PHP functions in PHP files.  But where are these files?  If you are the average designer using Hero, it doesn't really matter.  But if you want to see the inner workings of a template function you are using or plan on [writing your own template plugins as a developer](/docs/developers/template_plugins.md), this is important.

### Smarty's Standard Plugins

Smarty includes a whole host of block functions, functions, and modifiers that can be used in any template.  They are part of any Smarty download and thus included with Hero itself.  These plugins are all defined at `/app/libraries/smarty/plugins/`.

### Global Plugins

Certain plugins are unique to Hero (i.e., not included with Smarty) but so general in their use that they are defined as global plugins, available in any template at anytime.  These are located at `/themes/_plugins/`.

### Modules

Modules have the option of defining their own template plugins within their module definition file.  They are typically located in the `/template_plugins/` folder of the module's folder at `/app/modules/`.  For more information on defining your own template plugins in your module, check out the documentation on [module development](/docs/developers/modules.md) and [writing template plugins](/docs/developers/template_plugins.md).

### Themes

Sometimes, designers require functionality unique to their theme (i.e., their website's design).  These template plugins can be placed in your theme folder at `/themes/yourtheme/plugins/` and will automatically become available to you when that theme is active for your site.