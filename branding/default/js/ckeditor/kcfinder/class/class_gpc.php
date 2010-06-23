<?php

/** This file is part KCFinder project
  *
  *      @desc GET, POST and COOKIE requests class
  *   @package KCFinder
  *   @version 1.7
  *    @author Pavel Tzonkov <pavelc@users.sf.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class gpc {
    public $get;
    public $post;
    public $cookie;

    public function __construct() {
        $_GET = self::clear_slashes($_GET);
        $_POST = self::clear_slashes($_POST);
        $_COOKIE = self::clear_slashes($_COOKIE);
        $this->get = @$_GET;
        $this->post = @$_POST;
        $this->cookie = @$_COOKIE;
    }

    static function clear_slashes($sbj) {
        if (ini_get('magic_quotes_gpc')) {
            if (is_array($sbj))
                foreach ($sbj as $key => $val)
                    $sbj[$key] = self::clear_slashes($val);
            elseif (is_scalar($sbj))
                $sbj = stripslashes($sbj);
        }
        return $sbj;
    }

}

?>