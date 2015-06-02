<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

$files = array();
$files = listFolderFiles('/Users/Brock/Electric Function/Hero/codebase/docs');

foreach ($files as $file) {
	$content = file_get_contents($file);
	
		/**
	
	// normal link
	$content = preg_replace('#\[([^\[]*?)\]\((.*?)\)#i','[$1](/docs/$2.md)',$content);
	
	// fix relative links
	$content = preg_replace('#\[([^\[]*?)\]\(([^:]+?)\)#i','[/docs/$2$3](/docs/$1.md)',$content);

	// simple f and r
	$content = str_ireplace(array(
			'# ',
			'',
			'Hero',
			'## ',
			'',
			'### ',
			'',
			'',
			'',
			'`',
			'`',
			'`',
			'`',
			'> ',
			'',
			'<table>',
			'```',
			'```',
			'`',
			'`'
		),
		array (
			'# ',
			'',
			'Hero',
			'## ',
			'',
			'### ',
			'',
			'',
			'',
			'`',
			'`',
			'`',
			'`',
			'> ',
			'',
			'<table>',
			'```',
			'```',
			'`',
			'`'
		),
		$content);	
		
		*/
		
	$content = str_replace(array('## `','`'),array('## `','`'),$content);
	
	$content = preg_replace('#\[([^\[]*?)\]\(([^:]+?)\)#i','[$1]($2.md.md)',$content);
	
	file_put_contents($file, $content);
}


function listFolderFiles($dir){
    $ffs = scandir($dir);
    $i = 0;
    $list = array();
    foreach ( $ffs as $ff ){
        if ( $ff != '.' && $ff != '..' ){
        	if( is_dir($dir.'/'.$ff) ) {
                $list = array_merge($list, listFolderFiles($dir.'/'.$ff));
            } elseif (strpos($ff, '.') !== 0) {
                $list[] = $dir . '/' . $ff;
            }       
        }
    }
    return $list;
}