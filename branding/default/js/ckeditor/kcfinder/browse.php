<?php

/** This file is part KCFinder project
  *
  *      @desc Browser calling script
  *   @package KCFinder
  *   @version 1.7
  *    @author Pavel Tzonkov <pavelc@users.sf.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

$browser = new browser();
$browser->action();

function __autoload($class) {
    if (file_exists("class/class_$class.php"))
        require "class/class_$class.php";
    elseif (file_exists("class/types/$class.php"))
        require "class/types/$class.php";
}

?>