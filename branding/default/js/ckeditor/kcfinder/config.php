<?php

/** This file is part of KCFinder project
  *
  *      @desc Base configuration file
  *   @package KCFinder
  *   @version {version}
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

// IMPORTANT!!! Do not remove uncommented settings in this file even if
// you are using session configuration.
// See http://kcfinder.sunhater.com/install for setting descriptions

$_CONFIG = array(

    'disabled' => true,
    'readonly' => false,
    'denyZipDownload' => false,

    'theme' => "oxygen",

    'uploadURL' => "/upload",
    'uploadDir' => "",

    'dirPerms' => 0777,
    'filePerms' => 0755,

    'deniedExts' => "exe com msi bat php cgi pl",

    'types' => array(

        // CKEditor & FCKEditor types
        'files'   =>  "*mime ! application/octet-stream application/x-bzip2 application/x-gzip application/zip",
        'flash'   =>  "swf",
        'images'  =>  "*img",

        // TinyMCE types
        'file'    =>  "*mime ! application/octet-stream application/x-bzip2 application/x-gzip application/zip",
        'media'   =>  "swf flv avi mpg mpeg qt mov wmv asf rm",
        'image'   =>  "*img",
    ),

    'mime_magic' => "",

    'maxImageWidth' => 0,
    'maxImageHeight' => 0,

    'thumbWidth' => 250,
    'thumbHeight' => 250,

    'thumbsDir' => ".thumbs",

    'jpegQuality' => 100,

    'cookieDomain' => '',
    'cookiePath' => "/",
    'cookiePrefix' => 'KCFINDER_',

    // THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION CONFIGURATION

    '_check4htaccess' => true,
    '_tinyMCEPath' => "/demo/tiny_mce",

    '_sessionVar' => &$_SESSION['KCFINDER'],
    //'_sessionLifetime' => 30,
    //'_sessionDir' => "/var/www/sunhater.com/subdomains/kcfinder/sessions",

    //'_sessionDomain' => "kcfinder.sunhater.com",
    //'_sessionPath' => "/",
);

