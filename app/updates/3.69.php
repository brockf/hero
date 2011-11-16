<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

$sql = array();

/**
* Create some new core setting for modules
*/

$sql[] = 'INSERT INTO `countries` (`country_id`, `iso2`, `iso3`, `name`)
				VALUES
					(895, \'CW\', \'CW\', \'Curaçao\'),
					(896, \'SX\', \'SX\', \'Sint Maarten\');';							

foreach ($sql as $query) {
	$CI->db->query($query);
}					