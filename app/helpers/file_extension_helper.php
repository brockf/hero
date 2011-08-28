<?php

/**
* File Extension
*
* Retrieve the file extension from a filename.
*
* @param string $file
* @return string extension
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function file_extension ($file) {
	return strtolower(end(explode(".", $file)));
}