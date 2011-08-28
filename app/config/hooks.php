<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

/**
* Oneday, we'll compile all JS prior to viewing the control panel
*
$hook['display_override'] = array(
                                'class'    => 'Head_compile',
                                'function' => 'compile',
                                'filename' => 'head_compile.php',
                                'filepath' => 'libraries',
                                'params' => array()
                                );
*/
                                
/* End of file hooks.php */
/* Location: ./app/config/hooks.php */