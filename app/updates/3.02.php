<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$sql = array();

$sql[] = 'CREATE TABLE `binds` (
			  `bind_id` int(11) NOT NULL auto_increment,
			  `hook_name` varchar(125) NOT NULL,
			  `bind_class` VARCHAR(100) NOT NULL,
			  `bind_method` VARCHAR(100) NOT NULL,
			  `bind_filename` TEXT NOT NULL,
			  `bind_created` DATETIME NOT NULL,
			  PRIMARY KEY  (`bind_id`),
			  INDEX (`hook_name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
	$CI->db->query($query);
}