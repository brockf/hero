<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$sql = array();

$sql[] = 'ALTER TABLE `settings` MODIFY `setting_help` TEXT';

foreach ($sql as $query) {
	$CI->db->query($query);
}