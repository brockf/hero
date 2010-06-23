<?php

/** This file is part KCFinder project
  *
  *      @desc Helper class
  *   @package KCFinder
  *   @version 1.7
  *    @author Pavel Tzonkov <pavelc@users.sf.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class helper {

    static function rrmdir($dir) {
        $files = glob("$dir/*");
        if ($files === false)
            return false;

        foreach ($files as $file) {
            if (is_dir($file))
                self::rrmdir($file);
            elseif (!unlink($file))
                return false;
        }

        return rmdir($dir);
    }

    static function rmkdir($dir, $mode=0755) {
        $dir = preg_replace('/\/{2,}/s', "/", $dir);
        $dirs = explode("/", $dir);

        $curr = "";
        foreach ($dirs as $i => $cdir) {
            if (($i == 0) && ($cdir == ""))
                $curr .= "/";
            elseif ($i == 0)
                $curr .= $cdir;
            else
                $curr .= "/$cdir";
            if (!is_dir($curr) &&
                !mkdir($curr, $mode)
            )
                return false;
        }

        return true;
    }

    static function html_value($string) {
        return str_replace('"', "&quot;", $string);
    }

    static function js_value($string) {
        return preg_replace('/\r?\n/', "\\n", str_replace('"', "\\\"", str_replace("'", "\\'", $string)));
    }

    static function clear_whitespaces($string) {
        return trim(preg_replace('/\s+/s', " ", $string));
    }

    static function compress_css($code) {
        $code = self::clear_whitespaces($code);
        $code = preg_replace('/ ?\{ ?/', "{", $code);
        $code = preg_replace('/ ?\} ?/', "}", $code);
        $code = preg_replace('/ ?\; ?/', ";", $code);
        $code = preg_replace('/ ?\> ?/', ">", $code);
        $code = preg_replace('/ ?\, ?/', ",", $code);
        $code = preg_replace('/ ?\: ?/', ":", $code);
        $code = str_replace(";}", "}", $code);
        return $code;
    }

    static function http_cache($content, $modified, $type=null, $expire=null, array $headers=null) {
        self::http_check_mtime($modified);

        if ($type === null) $type = "text/html";
        if ($expire === null) $expire = 604800;
        $size = strlen($content);
        $expires = gmdate("D, d M Y H:i:s", time() + $expire) . " GMT";

        header("Content-Type: $type");
        header("Expires: $expires");
        header("Cache-Control: max-age=$expire");
        header("Pragma: !invalid");
        header("Content-Length: $size");

        if ($headers !== null) foreach ($headers as $header) header($header);
        echo $content;
    }

    static function http_check_mtime($mtime) {
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $mtime) . " GMT");
        $all_headers = function_exists("getallheaders")
            ? getallheaders()
            : (function_exists("apache_request_headers")
                ? apache_request_headers()
                : false);

        if (is_array($all_headers) && isset($all_headers['If-Modified-Since'])) {
            $cache_modified = explode(';', $all_headers['If-Modified-Since']);
            $cache_modified = strtotime($cache_modified[0]);
            if ($mtime <= $cache_modified) {
                header('HTTP/1.1 304 Not Modified');
                die;
            }
        }
    }

    static function resolveRelativeUrl($url) {
        if (substr($url, 0, 1) == "/") return $url;
        $dir = self::transformWindowsPath(getcwd());

        if (!isset($_SERVER['DOCUMENT_ROOT']) || ($dir === false))
            return false;

        $doc_root = self::transformWindowsPath($_SERVER['DOCUMENT_ROOT']);

        if (substr($dir, 0, strlen($doc_root)) != $doc_root)
            return false;

        $return = self::normalize_path(substr($dir, strlen($doc_root)) . "/$url");
        if (substr($return, 0, 1) !== "/")
            $return = "/$return";

        return $return;
    }

    static function resolveDirByURL($url) {
        $url = self::normalize_path($url);

        $uri = isset($_SERVER['SCRIPT_NAME'])
            ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['PHP_SELF'])
            ? $_SERVER['PHP_SELF']
            : false);

        $uri = self::transformWindowsPath($uri);

        if (substr($url, 0, 1) !== "/") {
            if ($uri === false) return false;
            $url = dirname($uri) . "/$url";
        }

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $doc_root = self::transformWindowsPath($_SERVER['DOCUMENT_ROOT']);
            return self::normalize_path($doc_root . "/$url");

        } else {
            if ($uri === false) return false;

            if (isset($_SERVER['SCRIPT_FILENAME'])) {
                $scr_filename = self::transformWindowsPath($_SERVER['SCRIPT_FILENAME']);
                return self::normalize_path(
                    substr($scr_filename, 0, -strlen($uri)) . "/$url"
                );
            }

            $count = count(explode('/', $uri)) - 1;
            for ($i = 0, $chdir = ""; $i < $count; $i++)
                $chdir .= "../";
            $chdir = self::normalize_path($chdir);

            $dir = getcwd();
            if (($dir === false) || !@chdir($chdir))
                return false;
            $rdir = getcwd();
            chdir($dir);
            return ($rdir !== false) ? self::normalize_path($rdir . "/$url") : false;
        }
    }

    static function normalize_path($path) {
        $path = preg_replace('/\/+/s', "/", $path);

        $path = "/$path";
        if (substr($path, -1) != "/")
            $path .= "/";

        $expr = '/\/([^\/]{1}|[^\.\/]{2}|[^\/]{3,})\/\.\.\//s';
        while (preg_match($expr, $path))
            $path = preg_replace($expr, "/", $path);

        $path = substr($path, 0, -1);
        $path = substr($path, 1);
        return $path;
    }

    static function transformWindowsPath($path) {
        if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
            $path = preg_replace('/([^\\\])\\\([^\\\])/', "$1/$2", $path);
            if (substr($path, -1) == "\\") $path = substr($path, 0, -1) . "/";
        }
        return $path;
    }
}

?>