<?php

/**
* Format Size
*
* Takes a filesize in bytes (e.g., 243434) and returns the proper
* formatted filesize (e.g., 24MB).
*
* @param int $filesize Filesize in bytes
* @return string Formatted filesize
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function format_size ($filesize)
{
    $a = array("B", "KB", "MB", "GB", "TB", "PB");
    $pos = 0;
    while ($filesize >= 1024)
    {
    	$filesize /= 1024;
        $pos++;
    }
    return round($filesize,2) . " " . $a[$pos];
}