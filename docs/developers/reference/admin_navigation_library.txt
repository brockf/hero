# Admin Navigation Library

This library builds the control panel navigation at the top and within modules during the runtime of the script.

## Initialization

This library is initialized automatically in the `Admincp_controller`.

```
// method call:
$this->navigation->child_link('publish', 50, 'Teams', site_url('admincp/teams'));
```

## Module Reference

[method]void parent_link (string $system_name , string $name)[/method]

Create a new parent link (alongside "Publish", "Storefront", "Members", etc.).

[method]void child_link (string $parent , int $weight , string $name , string $link)[/method]

Create a new child link of a parent link.  These, by default, drop down from the parent links.

For example, as part of the [module definition file](/docs/developers/modules)'s `admin_preload()` method:

```
function admin_preload () {
	$this->CI->admin_navigation->child_link('publish',44,'My New Module',site_url('admincp/my_new_module'));
}
```

`$weight` is an integer that is used to sort all children in ascending order.  You can see the weight of existing links by examining their `rel` attributes in the displayed HTML.

`$link` is a full URL (likely passed like `site_url('admincp/my/url')`).

[method]boolean delete_child (string $name)[/method]

Delete an existing child link from the navigation.  Just pass the link text (i.e., `$name`) for the link and it will be found and deleted.

This is useful for replacing an existing link in the control panel with your own custom module's link.

[method]void module_link (string $name , string $link)[/method]

Create a new module link that is displayed apart from the main menu, in the top right of the module window in the control panel.

[method]void clear_module_links ()[/method]

Clear all module links out of memory.

[method]void parent_active (string $system_name)[/method]

Set a parent item as "active".  The control panel theme will highlight this active parent link as an indicator of the user's current position in the control panel.

[method]string display ()[/method]

Retrieve the formatted HTML of the main navigation menu.

[method]string get_module_links ()[/method]

Retrieve the formatted HTML for module links.