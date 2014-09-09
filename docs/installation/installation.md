# Installing Hero

Installation is a simple process largely comprised of uploading the files from your Hero download and accessing the 2-step installation wizard.

## Step 1: Upload your Files

Upload all of the files/folders from your package as-is to your web server.  You can upload them to a sub-folder if you are not running Hero in the root folder of your domain.

## Step 2: Upload your License (if using a Commercial License)

If you have been given a license, upload your license file to `/app/config/license.txt`.

License files are only required for the Hero eCommerce commercial add-on.

## Step 3: Rename your configuration file

The included configuration file must be renamed so that, when you upgrade, you don't risk overwriting your existing `config.php` file.

Rename `/app/config/config.example.php` to `/app/config/config.php`.

## Step 4: Verify .htaccess exists

Sometimes, the `/.htaccess` file gets lost in the shuffle.  If this file does not exist, rename `/1.htaccess` to `/.htaccess`.

## Step 5: Access the installation wizard

Access `/install` in the root directory or sub-directory of your website (wherever you uploaded Hero).

> This directory does not actually exist but the URL routing system in Hero will direct you automatically. If you uploaded your files to your root domain folder at `example.com`, you would access `example.com/install`. If you uploaded your files to a sub-directory, you would access `example.com/subdirectory/install`. The same applies to subdomains.

Follow the 2-step installation wizard to complete the install!  You will be instructed to create a cronjob and your first administrator account during this process.

Now, *you're ready to begin using Hero!*

## Site not working?

If your install did not seem to work and you are having troubles viewing your site, check out the [troubleshooting guide](/docs/installation/troubleshooting.md) for tips on the causes/solutions for 99% of installation issues.