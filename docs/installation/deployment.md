# Mass Deployment of Hero

Hero is built for mass deployment of a release across multiple web servers/installations, as is evident in the following features:

* `/app/config/config.php` will never be overwritten by a new release because this file does not exist in release packages.  The same goes for `/app/config/database.php`.
* All database schema modifications are done by tracking the database's current schema version and the version required by the current package.  Updates are made automatically to the database when the database is out-of-date.
* Upgrades are made by simply uploading new files over old files.

These features make it possible to push new releases out to multiple web servers.

If you are interested in mass deployment with Hero, you can check out [Parachute](http://www.parachutedrop.com), a web application that manages and version controls multiple installations of a piece of web software, such as Hero.