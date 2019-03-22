<?php

/*
* Generates a unique URL string from a title/name
* 
* @param string $title The title/name to convert to a URL
* @param string $table The name of the table to check for uniqueness in
* @param string $field The fieldname to check for uniqueness in
* 
* @return string $urlstring
*/
function url_string ($title, $table = '', $field = '') {
	$urlstring = $title;
	
	// make "How does this look?" = "how-does-this-look"
	$urlstring = preg_replace("/[^a-zA-Z0-9\s]/", '', $urlstring);
	$urlstring = str_replace(' ','-',$urlstring);
	$urlstring = strtolower($urlstring);
	
	// uniqueness check
	if (!empty($table) and !empty($field)) {
		$CI =& get_instance();
		$CI->db->where($field,$urlstring);
		
		$count = 1;
		while ($CI->db->get($table)->num_rows() > 0) {
			$CI->db->where($field,$urlstring);
			
			$urlstring = preg_replace('/\-[0-9]*$/i','',$urlstring);
			$urlstring .= '-' . $count;
			
			$CI->db->where($field,$urlstring);
			
			$count++;
		}
	}
	
	return $urlstring;
}