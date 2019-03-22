<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Core Modules
*
* Define the modules which cannot be uninstalled.
* You likely will not touch this unless you create a new
* module that you want to protect from uninstallation.
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/

$config['core_modules'] = array(
							'blogs',
							'custom_fields',
							'emails',
							'forms',
							'menu_manager',
							'modules',
							'paywall',
							'publish',
							'reports',
							'rss',
							'search',
							'settings',
							'theme',
							'users'
						);