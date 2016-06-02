Hero Framework
====

[Hero](http://www.heroframework.com) is a powerful Content Management System (CMS) and eCommerce framework built on CodeIgniter.

## Documentation

All documentation is now is [included in this release](/docs/) in Markdown format.

## Server Requirements

Please ensure that your hosting environment meets the following specifications:

* PHP 5.1+
* One available MySQL 3.23+ database.
* Apache or Apache-like server that can parse .htaccess files with mod_rewrite rules.
* Ability to create one cronjob or scheduled process.

If you experience issues, please review the full Server Requirements article in the User Guide.

## Quick Install Guide

* Upload all files to a folder on your web server.
* Rename `/app/config/config.example.php` to `config.php`.
* Verify `/.htaccess` file exists. If not, rename `1.htaccess`.
* Access your site directory in your browser. You will find the Hero Installation Wizard at `/install`.

## Documentation

* There is a full user guide with designer, developer, and user documentation include in the repo in `/docs`.

## eCommerce

[Hero eCommerce](http://www.github.com/electricfunction/hero-ecommerce) is a free, open-source collection of modules that drop right into your
Hero installation and provide you with online store, subscription billing, paywall, and coupon functionality.

## History

Hero was developed by [Brock Ferguson](http://www.brockferguson.com) as founder of Electric Function, Inc. Initially, the main Hero app
was released open source but Hero eCommerce required a paid license. Since Electric Function was acquired in 2012, Hero and its
eCommerce upgrade have been released as free, open source software.
