<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$sql = array();

/**
* As we move to an uninstall/install module scheme, we need to clean some things up
*/

$sql[] = 'UPDATE `modules` SET `module_name`=\'coupons\' WHERE `module_name`=\'Coupons\'';
$sql[] = 'UPDATE `modules` SET `module_name`=\'import\' WHERE `module_name`=\'Member Import\'';

foreach ($sql as $query) {
	$CI->db->query($query);
}