<?php

$gen = new Userguide;
$gen->generate();

/**
* User Guide Generation
*
* Generates the user_guide HTML files from the marked up TXT files in /docs/contents/.
*
*/
class Userguide {
	private $app_name = 'Caribou CMS';
	
	private $output = './output';
	private $link_prefix = '/docs/output/';
	private $extension = '.html';

	function generate () {
		$this->clear_output();
	
		$files = $this->gather_files();
		
		$this->parse_file_array($files);
		
		$this->copy_assets();
	}
	
	function wrap_layout ($file) {
		preg_match('#\<h1\>(.*?)\<\/h1\>#i',$file, $matches);
		$title = $matches[1];
		
		$return = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>' . $title . '</title>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	<link href="' . $this->link_prefix . 'css/user_guide.css" media="screen" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="' . $this->link_prefix . 'js/shCore.js"></script>
	<script type="text/javascript" src="' . $this->link_prefix . 'js/shBrushPhp.js"></script>
	<script type="text/javascript" src="' . $this->link_prefix . 'js/shBrushXml.js"></script>
	<link href="' . $this->link_prefix . 'css/shCore.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="' . $this->link_prefix . 'css/shThemeDefault.css" media="screen" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="' . $this->link_prefix . 'js/user_guide.js"></script>
</head>
<body>
	' . $file . '
</body>
</html>';

		return $return;
	}
	
	function copy_assets () {
		$assets = $this->gather_files('./assets');
		
		$this->parse_file_array($assets, '', 'copy_asset');
	}
	
	function copy_asset($filename, $path) {
		if (!file_exists($this->output . '/' . $path)) {
			mkdir($this->output . '/' . $path, 0755, TRUE);
		}
		
		// get file contents
		$file = file_get_contents('./assets/' . $path . '/' . $filename);
		
		// export the file
		$handle = fopen($this->output . $path . '/' . $filename, 'w');
		fwrite($handle, $file);
		fclose($handle);
	}
	
	function simple_regex ($file) {
		// bold, italics
		$file = preg_replace('#\*\*(.*?)\*\*#i','<i>$1</i>',$file);
		
		// bold
		$file = preg_replace('#\*([a-zA-Z0-9])(.*?)\*#i','<strong>$1$2</strong>',$file);
		
		// normal link
		$file = preg_replace('#\[http[s]?\://(.*?)\]\((.*?)\)#i','<a href="http://$1">$2</a>',$file);
		
		// internal link
		$file = preg_replace('#\[(.*?)\]\((.*?)\)#i','<a href="' . $this->link_prefix . '$1' . $this->extension . '">$2</a>',$file);
		
		return $file;
	}
	
	function simple_replace ($file) {
		$file = str_ireplace(array(
								'[h1]',
								'[/h1]',
								'[appname]',
								'[h2]',
								'[/h2]',
								'[h3]',
								'[/h3]',
								'[span_code]',
								'[/span_code]',
								'[filename]',
								'[/filename]',
								'[url]',
								'[/url]',
								'[note]',
								'[/note]'
							),
							array (
								'<h1>',
								'</h1>',
								$this->app_name,
								'<h2>',
								'</h2>',
								'<h3>',
								'</h3>',
								'<span class="code">',
								'</span>',
								'<span class="filename">',
								'</span>',
								'<span class="url">',
								'</span>',
								'<div class="note">',
								'</div>'
							),
							$file);
							
		return $file;
	}
	
	function parse_paragraphs ($file) {
		$copy = $file;
		
		// remove all code blocks
		$copy = preg_replace('#\[code\](.*?)\[\/code\]#si', '', $copy);	
		
		// match all potential paragraphs
		preg_match_all('#\n([A-Z].*?)\n#si',$copy,$matches);
		$paragraphs = $matches[1];
		
		foreach ($paragraphs as $paragraph) {
			$file = str_replace($paragraph, '<p>' . $paragraph . '</p>', $file);
		}
		
		// sometimes, with duplicate text, we'll get multiple <p> tags
		$file = preg_replace('#\<[p\<\>]*\>#i','<p>',$file);
		$file = preg_replace('#\<\/[p\<\>\/]*\>#i','</p>',$file);
		
		return $file;
	}
	
	function parse_code ($file) {
		$file = preg_replace_callback('#\[code\](.*?)\[\/code\]#si', array($this, 'parse_code_callback'), $file);	

		return $file;
	}
	
	function parse_code_callback ($string) {
		$string = $string[1];
		
		// simple check for PHP
		if (strpos($string, '{$') === FALSE) {
			// it's likely PHP
			$brush = 'php';
		}
		else {
			$brush = 'xml';
		}
		
		$string = htmlspecialchars($string);
		
		return '<pre class="code brush: ' . $brush . '">' . "\n" . $string . "\n" . '</pre>';
	}
	
	function parse_lists ($file) {
		$file = preg_replace('#^\*\s(.*?)$#mi','<li>$1</li>',$file);
		
		// create <ul> and </ul> based on double breaks
		$file = preg_replace('#\n\n\<li\>#i',"\n\n<ul>\n<li>",$file);
		$file = preg_replace('#\<\/li\>\n\n#i',"</li>\n</ul>\n\n",$file);
		
		// ol lists
		// replace 1)
		$file = preg_replace('#^1\)\s(.*?)$#mi',"<ol><li>$1</li>",$file);
		// replace all other numbers
		$file = preg_replace('#^[0-9]*?\)\s(.*?)$#mi',"<li>$1</li>",$file);
		// place final </ol>
		$file = preg_replace('#\<\/li\>\n\n#i',"</li>\n</ol>\n\n",$file);
		
		return $file;
	}
	
	function parse_file($filename, $path) {
		// output directory exists?
		if (!file_exists($this->output)) {
			mkdir($this->output, 0755, TRUE);
		}
	
		// directory exists?
		if (!file_exists($this->output . $path)) {
			mkdir($this->output . $path, 0755, TRUE);
		}
		
		// get file contents
		$file = file_get_contents('./contents/' . $path . '/' . $filename);
		
		// simple find-and-replace
		$file = $this->simple_replace($file);
		
		// simple regex operations
		$file = $this->simple_regex($file);
		
		// paragraphs
		$file = $this->parse_paragraphs($file);
		
		// code
		$file = $this->parse_code($file);
		
		// lists
		$file = $this->parse_lists($file);
		
		// wrap in layout
		$file = $this->wrap_layout($file);
		
		// change filename
		$filename = str_replace('.txt',$this->extension,$filename);
		
		// export the file
		$handle = fopen($this->output . $path . '/' . $filename, 'w');
		fwrite($handle, $file);
		fclose($handle);
	}
	
	function clear_output ($dir = FALSE) {
		if ($dir === FALSE) {
			$dir = $this->output;
		}
	
		if (is_dir($dir)) {
			$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
			 		if (filetype($dir."/".$object) == "dir") {
			 			$this->clear_output($dir."/".$object);
			 		}
			 		else {
			 			unlink($dir."/".$object);
			 		}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
	
	function parse_file_array($files, $path = '', $method = 'parse_file') {
		foreach ($files as $name => $value) {
			if (is_array($value)) {
				// this is a directory
				$this->parse_file_array($value, $path . '/' . $name, $method);
			}
			else {
				$this->$method($value, $path);
			}
		}
	}
	
	function gather_files ($dir = FALSE) {
		if ($dir === FALSE) {
			$dir = dirname(__FILE__) . '/contents/';
		}
		
		$dir = rtrim($dir, '/') . '/';
	
		$files = array();
		
		if ($handle = opendir($dir)) {
		    while (false !== ($file = readdir($handle))) {
		    	if ($file == '.' or $file == '..') {
		    		// do nothing
		        } elseif (is_dir($dir . $file)) {
		        	$files[$file] = $this->gather_files($dir . $file);
		        }
		        else {
		        	$files[] = $file;
		        }
		    }
		}
		    
		return $files;
	}
}