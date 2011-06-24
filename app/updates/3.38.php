<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$sql = array();

$sql[] = 'ALTER TABLE `modules` ADD COLUMN `module_installed` TINYINT(1) AFTER `module_version`';
$sql[] = 'ALTER TABLE `modules` ADD COLUMN `module_ignore` TINYINT(1) AFTER `module_installed`';
$sql[] = 'UPDATE `modules` SET `module_installed` = \'1\'';

foreach ($sql as $query) {
	$CI->db->query($query);
}