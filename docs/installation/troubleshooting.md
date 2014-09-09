# Troubleshooting Your Installation

The vast majority of issues with Hero can be resolved by following the troubleshooting tips below.

## Problem: I am receiving a 404 error or 500 Internal Server Error when accessing my Hero folder

This is likely a problem with your .htaccess file.  Do the following to make sure you're all setup properly:

* Verify your `.htaccess` exists.  Rename `1.htaccess` to `.htaccess` if it does not exist.
* Your `.htaccess` file must exist in the same folder as your root Hero files.  They should be alongside your `index.php`,
`system_info.php` and `app` and `system` folders.
* If you have installed Hero in a subfolder, add a `RewriteBase` line to your `.htaccess` file like so:

```
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase /subfolder/
	Options +FollowSymlinks
```

* Some servers have trouble with the `Options +FollowSymLinks` line.  Try removing it.

## Problem: The configuration file does not exist

If you see the error above, you have not renamed your configuration file so that you can access the installation wizard.  Please rename `/app/config/config.example.php` to `/app/config/config.php`.

## Problem: 404 File Not Found errors on anything but the frontpage

You are receiving 404 errors because the `/.htaccess` file that routes friendly URLs to the main `/index.php` file either does not exist or is not functioning properly.

* Verify that `/.htaccess` exists.  If not, rename `/1.htaccess` to `/.htaccess`.
* If the file exists, verify that your web server is configured with "mod_rewrite" enabled.
* If your web server has mod_rewrite enabled, verify that it is active for your domain.  This may be a setting in your `httpd.conf` setting.

## Problem: Errors about IonCube loaders being needed

The IonCube Loaders required to decode certain files in Hero is not installed properly.  Please [download the IonCube loaders](http://www.ioncube.com/loaders.php) and install them.

## Problem: A variety of PHP errors not pointing to any particular problem

* Verify that you have PHP 5.0+ installed.  PHP4 will throw errors.
* Verify that PHP is configured as per the [server requirements](/docs/installation/server_requirements.md).

## Problem: License not valid for this server

* Confirm that the license you entered when purchasing/downloading Hero matches your current server environment.
* Access `/system_info.php` in your browser and send us the output so that we can ensure your license validates the proper server.

## Problem: All I see are blank pages!

If you only see blank pages, the most common issue is that PHP is throwing errors but you can't see them for one or both of two reasons.  Follow the points below to display the errors and then debug from there:

* Edit `php.ini` so that `display_errors` is "On".
* Edit `php.ini` so that `error_reporting` is "E_ALL".

If you don't have access to the `php.ini` configuration file, you can make both of these changes in the main Hero `/index.php` file by pasting the following code right at the top of the file:

```
<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

// ... rest of code will follow
```

## Problem:  I don't know!

Try [turning on logging](/docs/developers/errors_logging.md) so that you can see exactly where the system is failing.