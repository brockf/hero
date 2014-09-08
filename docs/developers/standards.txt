# Developer Standards and Best Practices

When developing for Hero, it is highly suggested that you follow the practices and standards in this guide.  Consistency within syntax and architecture is a major point of concern for us, the developers, and it helps the community to have all developers subscribing to the same practices so that developers' expectations with code are met and code is easier to understand.

## Simple syntax standards

### Capitalize boolean values

Use `TRUE` and `FALSE`, not their lowercased counterparts.

### Limit functionality crammed into one line of code

Spread out your `if` statements and let them breathe:

```
if ($test == "123") {
	echo 'It's true!';
}
else {
	echo 'Better luck next time.';
}
```

Don't get carried away with function bundling:

```
// hard to understand
$text = preg_replace('/[^a-z]/i',strtoupper(substr($text, 0, 10)));

// easier to understand (though still very odd code...)
$text = substr($text, 0, 10);
$text = strtoupper($text);
$text = preg_replace('/[^a-z]/i');
```

### Use verbose naming styles

Take the time to descriptively name variables and methods:

```
// who's going to remember what this means in a month?  or even further down the script?
$a = get_content(32);
$b = strtolower($a['title']);

// we'll always know what this is
$article = get_content(32);
$lowercase_title = strtolower($a['title']);
```

### Use this_naming_style_for_variables_and_methods

Forget CamelCase, pleaseuseunderscores, and don't bother Capitalizing except on models (and that's only because CodeIgniter standards say to).

```
$variable_name;
My_class->method();
```

## Best practices

### Model structure

For most models involving the creation, updating, deletion, and retrieval of an object type, the following class structure is encouraged (where "widget" is the type of object):

* `int new_widget(...)` - With function parameters for each of widget's characteristics.  Returns the ID of the newly-created widget, else FALSE if failure.
* `boolean update_widget($widget_id, ...)` - The same function parameters as `new_widget()`, with a leading parameter of `$widget_id`
* `boolean delete_widget($widget_id)`
* `array get_widget($widget_id)` - Wraps `get_widgets()` by preparing a `$filters` array like `array('id' => $widget_id)`.  Returns the array of the object, else FALSE if no match.
* `array get_widgets($filters = array())` - `$filters` is an array of various comparative/search filters used in modifying the retrieval query.  Minimum filters: id, sort, sort_dir, limit, offset. Returns the multi-dimensional array of all matching objects, else FALSE if no matches.

### Documentation blocks

Each method in Hero shall be documented, at the mimimum, like so:

```
/**
* Get Widgets
*
* Search and retrieve widgets from the database.
*
* @param $filters['id'] The ID of the widget
* @param $filters['name'] The name of the widget
* @param string $filters['sort'] The field to sort by (default: widgets.widget_id)
* @param string $filters['sort_dir'] The direction of the sort (default: "DESC")
* @param int $filters['limit'] The number of items to return
* @param int $filters['offset'] The records offset in retrieval
*
* @return array Widgets
*/
function get_widgets ($filters) {
	// ... code here ...
}
```

Of course, any [PHPDoc](http://www.phpdoc.org/)-standard documentation in docblocks is accepted.

### No core file edits

Don't edit core files!  Unless you want to break your upgrade path, that is.

### No direct access to PHP scripts

Just to make sure people are accessing your PHP files through the main `index.php` file, you should place the following at the top of each PHP file along with the main `<?php` declaration:

```
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
```

## CodeIgniter Standards

[Click here](http://www.codeigniter.com/user_guide/general/styleguide.html) a list of the standards suggested for anyone using CodeIgniter.