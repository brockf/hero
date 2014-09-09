# Setting Helper

Retrieve the value of a setting stored either in a configuration file or the `settings` table/model.

## Initialization

This helper is autoloaded by default and does not require initialization.

## Reference

## `string|boolean setting (string $setting_name)`

Return the value of a setting.  The value may be boolean if that is how it is set.

```
if (setting('enable_twitter') == TRUE) {
	// post to twitter
}
```