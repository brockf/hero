# Server Requirements

If you are installing Hero on your own web server (or in a local development environment), your web server must have the following components:

* Apache or comparable web server
* PHP 5.0+
* MySQL 3.23+ (either as "localhost" or remotely hosted)
* Crontab (ability to add/edit cronjobs)

More information for configuration is below.

## Web Server

Most server software will work fine with Hero, including Apache, lighttpd, and Microsoft IIS.

Please note the following:

* Your server should support `.htaccess` configurations and `mod_rewrite` or equivalent software which can rewrite URLs such as `/articles/edit/134` to `/index.php?articles/edit/134`.  Hero includes a `.htaccess` file that does all URL rewriting automatically, but it will not work if your server does not support this.
* An SSL Certificate is *highly recommended* for any site that has members and/or ecommerce activity.  Once an SSL certificate is installed for your site on your web server, you can set the "force_https" setting to "On" to make sure that a secure HTTPS connection is used during account management and checkout.
* You will need FTP access to your web server to upload the files for Hero and also to CHMOD (change permissions) 2 folders and 1 file so that they are writable by the web server.

## PHP

Your PHP should be configured like so:

* XML support with SimpleXML (installed by default as of PHP 5.13)
* cURL (with SSL support) must be compiled with PHP to use most payment gateways.
* `fopen_allow_url` should be "On".
* `error_reporting` can be as high or low as you would like.  Hero is properly programmed and will not throw `E_NOTICES`.
* `enable_short_tags` should be set to "On", although they will be rewritten on the fly if not.
* `safe_mode` should be "Off".
* GD2 Image Library support.  Optional:  ImageMagick for more efficient image resizing and processing.

## Hero eCommerce Requirements

This add-on has a commercial license and includes license protection software to protect this.  For this reason, there is an additional license requirement:

* IonCube Loaders must be installed (free - [download here](http://www.ioncube.com/loaders.php))