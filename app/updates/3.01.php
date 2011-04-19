<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$sql = array();

$sql[] = 'CREATE TABLE `hooks` (
			  `hook_id` int(11) NOT NULL auto_increment,
			  `hook_name` varchar(125) NOT NULL,
			  `hook_email_data` TEXT NOT NULL,
			  `hook_other_email_data` TEXT NOT NULL,
			  `hook_description` TEXT NOT NULL,
			  `hook_created` DATETIME NOT NULL,
			  PRIMARY KEY  (`hook_id`),
			  INDEX (`hook_name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
	$CI->db->query($query);
}