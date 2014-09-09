# URL Helper

This helper is almost identical to the [standard CodeIgniter URL helper](http://codeigniter.com/user_guide/helpers/url_helper.html), except for the `current_url()` function documented below.  This method has been modified to include potential query strings (which CodeIgniter does not support).

For all other methods, such as `base_url()` and `site_url()`, visit [the CodeIgniter URL helper documentation](http://codeigniter.com/user_guide/helpers/url_helper.html).

## Initialization

This helper is autoloaded by default and does not require initialization.

## Reference

## `string current_url ()`

Return the current URL, *including a query string, if applicable*.

```
$url = current_url();
```