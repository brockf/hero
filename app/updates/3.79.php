<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$sql = array();

/**
* Add a modules field to the order_details table
*/

$sql[] = 'ALTER TABLE `order_details` ADD COLUMN `module` VARCHAR(255) NULL';

foreach ($sql as $query) {
	$CI->db->query($query);
}