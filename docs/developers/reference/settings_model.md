# Settings Model

The settings model does the obvious - creating, updating, deleting, and retrieving settings - but it also has a few unique operations.

First, it has a method which loads all of the settings from the database into the CodeIgniter standard configuration array.  This allows all settings to be accessible like below:

```
$my_setting = $this->config->item('my_setting');
// ... also via our helper ....
$my_setting = setting('my_setting');
```

Second, the settings model has a method which creates writeable folders on the server.  This is useful during module install routines where they need a unique folder to place uploaded or created files.

Hidden settings created with `new_setting()` are an excellent way for modules to store key => value data without creating a new table for one or two data items.

## Initialization

The settings model is loaded automatically by default.

## Method Reference

## `void make_writeable_folder (string $path , boolean $no_access)`

Create a writeable folder on the server.  The `$path` should be the full server path to the folder.  For example:

```
$path = FCPATH . 'writeable/my_new_folder';
// or, use our main writeable folder setting:
$path = $this->config->item('path_writeable') . 'my_new_folder';

$this->settings_model->make_writeable_folder ($path);
```

If you don't want users to be able to access the files in this folder directly, set `$no_access` to TRUE and a "deny all" `.htaccess` file will be placed in the folder.

## `boolean set_settings ()`

A helper method that loads all settings from the database into CodeIgniter's standard configuration array at runtime.

## `int new_setting (int $setting_group , string $setting_name , string $setting_value [, string $setting_help = '' [, string $setting_type = 'text' [, string $setting_options = '' [, date $setting_time = FALSE [, boolean $setting_hidden = FALSE]]]]])`

Create a new setting.

Possible types for `$setting_type`:

* text
* textarea
* toggle

If specifying toggle, you should pass a serialized array like `a:2:{i:0;s:3:"Off";i:1;s:2:"On";}`.  You can change the value of "Off" and "On" to whatever labels you would like, but there must be two keys in this serialized array: "1" and "0".

Setting `$setting_hidden` to TRUE will keep the setting from showing in the standard settings manager at *Configuration > Settings*.

## `void update_setting (string $name , string $value)`

Update an existing system.  Note that setting details can't be updated, only its value.

## `void update_setting (string $name)`

Delete a system setting.

## `string get_setting (string $name)`

Retrieve the value of a setting from the database.

## `array get_settings ( [array $filters = array()])`

Retrieve an array of settings based on optional filters.

Possible Filters: 

* int *group_id* - Setting group ID
* string *name* - The setting name
* boolean *show_hidden* - Show hidden settings?  Default: TRUE
* string *sort* - Field to sort by
* string *sort_dir* - ASC or DESC

Each setting returns the following data:

* *id*
* *name*
* *group_id*
* *group_name*
* *value*
* *help*
* *last_update*
* *type*
* *options*
* *toggle_value* - If it's a toggled setting, this will be the label of the current value, as specified in the serialized options array.

## `array get_setting_groups ( [array $filters = array()])`

Retrieve an array of settings groups based on optional filters.  These are hardcoded into the database and not customizable.

Possible Filters: 

* int *group_id* - Setting group ID
* string *name* - The setting name
* boolean *show_hidden* - Show hidden settings?  Default: TRUE
* string *sort* - Field to sort by
* string *sort_dir* - ASC or DESC
* string *sort* - Field to sort by
* string *sort_dir* - ASC or DESC

