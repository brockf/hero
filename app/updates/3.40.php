<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$sql = array();

/**
* Create some new core setting for modules
*/

$sql[] = 'INSERT INTO `settings` (`setting_group`, `setting_name`, `setting_value`, `setting_help`,
									`setting_update_date`, `setting_type`, `setting_options`, `setting_hidden`)
								VALUES (
								 \'1\',
								 \'modules_auto_install\',
								 \'1\',
								 \'Install modules automatically when their folder is dropped into /app/modules.\',
								 \'' . date('Y-m-d H:i:s') . '\',
								 \'toggle\',
								 \'a:2:{i:0;s:3:"Off";i:1;s:2:"On";}\',
								 \'1\'	
								);';							

foreach ($sql as $query) {
	$CI->db->query($query);
}