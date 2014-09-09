# Advanced Configuration

Although not "advanced" per se, the following configurations can only be made by manually editing `/app/config/config.php`.

> The Hero configuration file is an extended version of the [CodeIgniter](/docs/developers/codeigniter.md) configuration file.  Many options remain unchanged and so, if you are familiar with CodeIgniter, this documentation will largely be a review for you.

### Base URL

Specify your site's domain (and subfolder, if applicable).  Always leave a trailing slash.

```
$config['base_url']	= "http://www.example.com/";
```

### Maximum Subscription Duration

Specify the maximum number of days to run a subscription.  Many payment gateways won't let you register a subscription for more than 1-2 years.

```
$config['max_recurring_days_from_today'] = 1095;
```

### Security Keys

Specify the `cron_key` needed for cronjob requests in order to ensure cronjobs are authentic, and the `encryption_key` used in various cryptographic activities.

> These are dynamically/randomly generated upon install.  The data below does not represent what will be in your configuration file.

```
$config['encryption_key'] = "10101010101010101010101010";
$config['cron_key'] = '0000000000000000';
```

### Filepaths

Specify atypical writable folders for file uploads, images, and other dynamically-generated files.

```
$config['path_writeable'] = FCPATH . 'writeable/';
$config['path_product_files'] = $config['path_writeable'] . 'product_files/';
$config['path_product_images'] = $config['path_writeable'] . 'product_images/';
$config['path_editor_uploads'] = $config['path_writeable'] . 'editor_uploads/';
$config['path_custom_field_uploads'] = $config['path_writeable'] . 'custom_uploads/';
$config['path_image_thumbs'] = $config['path_writeable'] . 'image_thumbs/';
$config['path_email_templates'] = $config['path_writeable'] . 'email_templates/';
```

### Write Mode

Specify your server's optimal "writable" mode, where folders are writable.

```
$config['write_mode'] = 0777;
```

### Image Library

If you are using ImageMagick (recommended) as your image processor, you'll have to specify it's location here.  You can also hardcode an image processor so that Hero won't try to autodetect your image processor.

```
// specify an image library to use.  leave blank to autodetect.
// options: GD, GD2, ImageMagick
$config['image_library'] = '';

// if you specified ImageMagick or NetPBM, you must specify the path
$config['image_library_path'] = '';
```

### Duplicate Login Checker

Set to "no" if you want to allow users to be logged in from multiple devices at the same time.  Set it to "yes" to prevent multiple simultaneous logins to the same account.

```
// should we make sure that only one user is logged into an account at the same time?
$config['duplicate_login_check'] = 'yes';
```

### Allow Special Characters in Usernames

If you want to allow emails as usernames, or usernames with special characters, you will need to set this setting to `TRUE`.

```
// allow special characters in usernames (e.g., for email addresses)?
$config['username_allow_special_characters'] = TRUE;
```

### Downloadable Products

Specify how many times a downloadable product can be download.

```
$config['maximum_downloads_per_purchase'] = 2;
```

### Secure Modules

Specify which modules to secure with HTTPS/SSL connections (if an SSL certificate is installed and the *force_https* setting is On).

```
// secure routes (these will be redirected to HTTPS if you have an SSL certificate)
$config['secure_modules'] = array('users','checkout');
```

### Enable the Profiler

When set to TRUE, the profiler will display a list of all database queries, memory usage, execution time, etc.  Very useful for debugging and performance checking for developers.  [Click here for more information on the debug/profile mode](/docs/developers/profiling.md).

```
// if enabled, the debugger will display a profile of all queries, memory usage, and other
// useful information upon each page load
$config['debug_profiler'] = FALSE;
```

### URI Protocol

If you are having trouble with URL's not routing properly, try changing this setting.

```
$config['uri_protocol']	= "AUTO";
```

### Logging

Specify which messages, errors, and debug notes should be logged at `/app/logs`.  If `/app/logs` does not
exist or is not writeable by the webserver, you must correct this or the logs will not be created.

```
/*
|--------------------------------------------------------------------------
| Error Logging Threshold
|--------------------------------------------------------------------------
|
| If you have enabled error logging, you can set an error threshold to 
| determine what gets logged. Threshold options are:
| You can enable error logging by setting a threshold over zero. The
| threshold determines what gets logged. Threshold options are:
|
|	0 = Disables logging, Error logging TURNED OFF
|	1 = Error Messages (including PHP errors)
|	2 = Debug Messages
|	3 = Informational Messages
|	4 = All Messages
|
| For a live site you'll usually only enable Errors (1) to be logged otherwise
| your log files will fill up very fast.
|
*/
$config['log_threshold'] = 0;
```