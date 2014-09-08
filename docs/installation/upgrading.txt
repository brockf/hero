# Upgrading Hero

## Version Upgrade

To upgrade Hero to the latest version, you simply upload the files/folders of the new release directly over your old release.  Of course, it is highly recommend that you backup your existing files/folders and database prior to doing so, as an added precautionary measure.

If you do delete the files on your web server prior to uploading, you will lose important files which will need to be replaced.  These are:

* `/app/config/config.php`
* `/app/config/database.php`
* `/app/config/installed.php`
* All files/folders in your `/writeable/` folder.

## Upgrading from Hero Open Source to Hero Professional

While you can upgrade to Hero Professional using the same instructions as above, you can also perform a simpler upgrade by just uploading the following module folders:

* `/app/modules/billing`
* `/app/modules/coupons`
* `/app/modules/store`

At this time, you will be required to place a license at `/app/config/license.txt` that will validate your installation.

Once this is complete, you're all set!