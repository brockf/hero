<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
| 	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['scaffolding_trigger'] = 'scaffolding';
|
| This route lets you set a "secret" word that will trigger the
| scaffolding feature for added security. Note: Scaffolding must be
| enabled in the controller in which you intend to use it.   The reserved 
| routes must come before any wildcard or regular expression routes.
|
*/

$route['default_controller'] = "frontpage";
$route['scaffolding_trigger'] = "";

// reroute all callback/X calls to the main callback controller
$route['callback:any'] = 'billing/callback/process';

// admin specific routes
$route['admincp'] = 'admincp/dashboard';
$route['admincp/dataset/([a-zA-Z_-]+)'] = 'admincp/dataset/$1';
$route['admincp/login'] = 'admincp/login';
$route['admincp/login/go'] = 'admincp/login/go';
$route['admincp/logout'] = 'admincp/login/logout';
$route['admincp/([a-zA-Z_-]+)/(:any)'] = "$1/admincp/$2";
$route['admincp/([a-zA-Z_-]+)'] = "$1/admincp/index";

// miscellaneous routes
$route['checkout'] = 'billing/checkout';
$route['checkout/([a-zA-Z_-]+)'] = 'billing/checkout/$1';

if (file_exists(FCPATH . 'writeable/routes.php')) {
	include(FCPATH . 'writeable/routes.php');
}

/* End of file routes.php */
/* Location: ./system/opengateway/config/routes.php */