<?php

/** This file is part KCFinder project
  *
  *      @desc Join all JavaScript files in current directory
  *   @package KCFinder
  *   @version 1.7
  *    @author Pavel Tzonkov <pavelc@users.sf.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

require "../../class/class_helper.php";

$files = glob("*.js");

foreach ($files as $file) {
    $fmtime = filemtime($file);
    if (!isset($mtime) || ($fmtime > $mtime))
        $mtime = $fmtime;
}

helper::http_check_mtime($mtime);

header("Content-Type: text/javascript");
foreach ($files as $file)
    require $file;

?>