<?php

/*
* Parse as Template Modifier
*
* Parses the passed string as if it were template!  Useful for parsing tags in content fields.
*
* @param string $string
*
* @return template
*/

function smarty_modifier_parse_as_template ($string)
{
	// perform a simple check - is this variable worth parsing?
	if (strpos($string,'{') === FALSE) {
		return $string;
	}

    // create temporary template file
    $filename = time() . '-' . rand(1000,10000) . '.thtml';
    
	$CI =& get_instance();
	$folder = $CI->config->item('path_writeable');
	
	$file = $folder . $filename;
	
	$handle = fopen($file, 'w');
	fwrite($handle, $string);
	fclose($handle);
	
	// parse the file via Smarty
	$parsed = $CI->smarty->fetch($file);
	
	// delete the file
	@unlink($file);
	
	// return
	return $parsed;
}