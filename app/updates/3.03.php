<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$sql = array();

$sql[] = 'ALTER TABLE `links` ADD COLUMN `link_parameter` VARCHAR(250) AFTER `link_method`';

foreach ($sql as $query) {
	$CI->db->query($query);
}