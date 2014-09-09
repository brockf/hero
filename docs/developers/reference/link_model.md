# Link Model

The link model stores all non-automatic (i.e., non-module-based) URLs and maps them to modules, classes, methods, and sometimes even directly to templates.  Once you register a URL with `new_link()` successfully, you are securing a unique URL in the system.

On top of mapping a URL to your method/template/etc., you are also making your link available to be used in [the Menu Manager for drag and drop menu building](/docs/publishing/menus.md).

Most calls to this model will be internal, but new modules may use it.

## Initialization

```
$this->load->library('link_model');
// method call: $this->link_model->delete_link(44);
```

## Method Reference

## `int new_link (string $url_path , array|boolean $topics , string $title , string $type_name , string $module , string $controller , string $method [, string $parameter = ''])`

Register a link in the system.  It will automatically be prepped (all non-URL characters replaced/removed) and made unique by this model's methods.  For example, if you submit the URL path of "mickey_mouse" and there is another item of this type, it will automatically be saved as "mickey_mouse_2".

Thus, to know exactly what URL path was saved, you can either call `get_unique_url_path()` yourself prior to this method, or look up the link using the returned `$link_id` afterwards.

Standard URL's without a parameter are routed like so:

```
$module/$controller/$method/$url_path
```

URL's registered with a parameter are routed like:

```
$module/$controller/$method/$parameter
```

Examples:

```
$this->load->library('link_model');

// map a URL directly to my new module (and controller of same name)
$this->link_model->new_link('my_test_article', array(1001, 1002), 'My Test Article', 'Article', 'articles', 'articles', 'view');
// routes example.com/my_test_article to articles/articles/view/my_test_article

// map a URL directly to a template via the "theme" module (this is standard in Hero)
$this->link_model->new_link('straight_to_template', FALSE, 'My Test Template', 'Template', 'theme', 'template', 'view', 'my_template.thtml');
// routes example.com/my_test_article to articles/articles/view/my_test_article
```

## `string get_unique_url_path (string $url_path)`

Given a URL path, it will return a URL path that is definitely unique in the system, by sequentially adding "_2", "_3", "_4", etc. to the end of the path.

## `array get_links (array $filters = FALSE)`

Return an array of links matching the `$filters` criteria, if it exists.

Possible filters:

* string *url_path*
* string *parameter*
* string *sort*
* string *sort_dir*
* int *offset*
* int *limit*

## `boolean delete_link (int $link_id)`

Delete a link from the system.

## `boolean update_title (int $link_id , string $title)`

Update a title associated with a link.

## `boolean update_url (int $link_id , string $url_path)`

Update the url_path associated with a link (this is not checked for unique-ness).

## `boolean update_topics (int $link_id , array $topics)`

Update the topics array associated with a link.

## `string prep_url_path (string $url_path)`

Remove/replace all characters that are not appropriate for a URL path.

## `boolean is_unique (string $url_path)`

Is the URL path being passed unique or already in the system?

## `boolean gen_routes_file ()`

Generate the custom routes file that tells the platform how to route URL's.  This method is called automatically by this model's methods so likely does not need to be referenced directly.