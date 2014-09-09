# Migration

To migrate your Hero installation from one server to another, simply do the following:

> If you are changing your domain, submit a support ticket with your license # and new domain so that it can be updated.

> Make sure your new server meets all the [Server Requirements](/docs/installation/server_requirements.md).

1) Copy all files and folders to the new server.  Note that certain files like those in `/writeable` and `/app/config` will only exist on your server unless
you have downloaded them locally.  Be sure to copy `.htaccess` to the new server as well.
2) Set the permissions of all files/folders in `/writeable` as writeable (e.g., CHMOD 0755, or CHMOD 0777 for some servers).
3) Modify `/app/config/config.php` with your new `base_url`.
4) Migrate your database and make note of your new database information.
5) Modify `/app/config/database.php` with your new database information.