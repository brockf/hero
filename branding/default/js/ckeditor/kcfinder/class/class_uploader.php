<?php

/** This file is part KCFinder project
  *
  *      @desc Uploader class
  *   @package KCFinder
  *   @version 1.7
  *    @author Pavel Tzonkov <pavelc@users.sf.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class uploader {
    protected $config = array();
    protected $CKfuncNum = false;
    protected $lang = "en";
    protected $type;
    protected $file;
    protected $dateTimeFull;
    protected $dateTimeMid;    // Currently not used
    protected $dateTimeSmall;  // Currently not used
    protected $labels = array();

    protected $types = array();

    public function __construct() {

        // LOAD DEFAULT CONFIGURATION
        require "config.php";
        if (!strlen($_CONFIG['cookieDomain']))
            $_CONFIG['cookieDomain'] = $_SERVER['HTTP_HOST'];
        if (!strlen($_CONFIG['cookiePath']))
            $_CONFIG['cookiePath'] = "/";
        $this->config = $_CONFIG;

        // SETTING UP SESSION
        if (isset($_CONFIG['_sessionLifetime']))
            ini_set('session.gc_maxlifetime', $_CONFIG['_sessionLifetime'] * 60);
        if (isset($_CONFIG['_sessionDir']))
            ini_set('session.save_path', $_CONFIG['_sessionDir']);
        if (isset($_CONFIG['_sessionDomain']))
            ini_set('session.cookie_domain', $_CONFIG['_sessionDomain']);
        session_start();

        // RELOAD CONFIG
        require "config.php";

        // LOAD SESSION CONFIGURATION IF EXISTS
        if (isset($_CONFIG['_sessionVar']) &&
            is_array($_CONFIG['_sessionVar']) &&
            count($_CONFIG['_sessionVar'])
        )
            foreach ($_CONFIG['_sessionVar'] as $key => $val)
                if ((substr($key, 0, 1) != "_") && isset($_CONFIG[$key]))
                    $this->config[$key] = $val;

        $this->types = &$this->config['types'];

        if (substr($this->config['uploadURL'], 0, 1) !== "/") {
            $this->config['uploadURL'] = helper::resolveRelativeURL($this->config['uploadURL']);
        }
        if (!strlen($this->config['uploadDir']))
            $this->config['uploadDir'] = helper::resolveDirByURL($this->config['uploadURL']);

        // CKEDITOR INPUT
        if (isset($_GET['CKEditorFuncNum']))
            $this->CKfuncNum = $_GET['CKEditorFuncNum'];

        if (isset($_GET['langCode']) &&
            preg_match('/^[a-z]{2,3}$/i', $_GET['langCode']) &&
            file_exists("lang/" . strtolower($_GET['langCode']) . ".php")
        )
            $this->lang = $_GET['langCode'];
        $this->localization($this->lang);

        $typeDirs = array_keys($this->types);
        $this->type = (isset($_GET['type']) && isset($this->types[strtolower($_GET['type'])]))
            ? strtolower($_GET['type']) : $typeDirs[0];

        // CHECK & MAKE DEFAULT .htaccess
        $htaccess = "{$this->config['uploadDir']}/.htaccess";
        if (!file_exists($htaccess)) {
            if (!@file_put_contents($htaccess, $this->get_htaccess()))
                $this->backMsg($this->label("Cannot write to upload folder."));
        } else {
            if (false === ($data = file_get_contents($htaccess)))
                $this->backMsg($this->label("Cannot read .htaccess"));
            if (($data != $this->get_htaccess()) &&
                !@file_put_contents($htaccess, $this->get_htaccess())
            )
                $this->backMsg($this->label("Incorrect .htaccess file. Cannot rewrite it!"));
        }

        // CHECK & CREATE UPLOAD FOLDER
        if (!is_dir("{$this->config['uploadDir']}/{$this->type}")) {
            if (!mkdir("{$this->config['uploadDir']}/{$this->type}", $this->config['dirPerms']))
                $this->backMsg($this->label("Cannot create {dir} folder.",
                    array('dir' => $this->type)));
        } elseif (!is_readable("{$this->config['uploadDir']}/{$this->type}"))
            $this->backMsg($this->label("Cannot read upload folder."));
        $this->config['uploadDir'] .= "/{$this->type}";
        $this->config['uploadURL'] .= "/{$this->type}";

        // UPLOADED FILE
        if (isset($_FILES['upload']))
            $this->file = &$_FILES['upload'];
        elseif(isset($_FILES['NewFile']))
            $this->file = &$_FILES['NewFile'];
    }

    public function upload() {
        $config = &$this->config;
        $file = &$this->file;

        if ($config['disabled']) {
            if (isset($file['tmp_name'])) @unlink($file['tmp_name']);
            $message = $this->label("You don't have permissions to upload files.");

        } elseif (true === ($message = $this->checkUploadedFile())) {
            $message = "";

            $dir = "{$config['uploadDir']}/";
            if (isset($_GET['dir'])) {
                $udir = helper::normalize_path("$dir{$_GET['dir']}");
                if (substr($udir, 0, strlen($dir)) !== $dir) {
                    $message = $this->label("Unknown error.");
                    @unlink($file['tmp_name']);
                } else {
                    $l = strlen($dir);
                    $dir = "$udir/";
                    $udir = substr($udir, $l);
                }
            }

            if (!strlen($message)) {
                $sufix = ""; $i = 1;
                $ext = $this->getExtension($file['name'], false);
                $base = strlen($ext)
                    ? substr($file['name'], 0, -strlen($ext) - 1)
                    : $file['name'];
                do {
                    $target = "$dir$base$sufix" . (strlen($ext) ? ".$ext" : "");
                    $sufix = "(" . $i++ . ")";
                } while (file_exists($target));

                if (!move_uploaded_file($file['tmp_name'], $target)) {
                    @unlink($file['tmp_name']);
                    $message = $this->label("Cannot move uploaded file to target folder.");
                } else {
                    if (function_exists('chmod'))
                        chmod($target, $this->config['filePerms']);

                    $url = "{$config['uploadURL']}/";
                    if (isset($udir))
                        foreach (explode("/", $udir) as $cdir)
                            $url .= rawurlencode($cdir) . "/";

                    $url .= rawurlencode(basename($target));
                }
            }
        }

        $this->callBack($url, $message);
    }

    protected function checkUploadedFile() {
        $config = &$this->config;
        $file = &$this->file;

        if (!is_array($file) || !isset($file['name']))
            return $this->label("Unknown error");

        $extension = preg_match('/^.*\.([^\.]*)$/s', $file['name'], $patt)
            ? strtolower($patt[1]) : "";
        $extensions = strtolower(helper::clear_whitespaces($this->types[$this->type]));

        // CHECK FOR UPLOAD ERRORS
        if ($file['error'])
            return
                ($file['error'] == UPLOAD_ERR_INI_SIZE) ?
                    $this->label("The uploaded file exceeds {size} bytes.",
                        array('size' => ini_get('upload_max_filesize'))) : (
                ($file['error'] == UPLOAD_ERR_FORM_SIZE) ?
                    $this->label("The uploaded file exceeds {size} bytes.",
                        array('size' => $_GET['MAX_FILE_SIZE'])) : (
                ($file['error'] == UPLOAD_ERR_PARTIAL) ?
                    $this->label("The uploaded file was only partially uploaded.") : (
                ($file['error'] == UPLOAD_ERR_NO_FILE) ?
                    $this->label("No file was uploaded.") : (
                ($file['error'] == UPLOAD_ERR_NO_TMP_DIR) ?
                    $this->label("Missing a temporary folder.") : (
                ($file['error'] == UPLOAD_ERR_CANT_WRITE) ?
                    $this->label("Failed to write file.") :
                    $this->label("Unknown error.")
            )))));

        // HIDDEN FILENAMES CHECK
        elseif (substr($file['name'], 0, 1) == ".")
            return $this->label("File name shouldn't begins with '.'");

        // EXTENSION CHECK
        elseif (!$this->validateExtension($extension, $this->type))
            return $this->label("Denied file extension.");

        // SPECIAL DIRECTORY TYPES CHECK (e.g. *img)
        elseif (preg_match('/^\*([^ ]+)(.*)?$/s', $extensions, $patt)) {
            list($extensions, $type, $params) = $patt;
            if (class_exists("type_$type")) {
                $class = "type_$type";
                $type = new $class();
                $cfg = $config;
                if (strlen($params))
                    $cfg['params'] = trim($params);
                $response = $type->checkFile($file['tmp_name'], $cfg);
                if ($response !== true)
                    return $this->label($response);
            } else
                return $this->label("Unexisting directory type.");
        }

        // IMAGE RESIZE
        $gd = new gd($file['tmp_name']);
        if (!$gd->init_error &&
            ($config['maxImageWidth'] || $config['maxImageHeight']) &&
            (
                ($gd->get_width() > $config['maxImageWidth']) ||
                ($gd->get_height() > $config['maxImageHeight'])
            ) && (
                !$gd->resize_fit($config['maxImageWidth'], $config['maxImageHeight']) ||
                !$gd->imagejpeg($file['tmp_name'], $config['jpegQuality'])
            )
        )
            return $this->label("The image is too big and/or cannot be resized.");

        return true;
    }

    protected function getExtension($file, $toLower=true) {
        return preg_match('/^.*\.([^\.]*)$/s', $file, $patt)
            ? ($toLower ? strtolower($patt[1]) : $patt[1]) : "";
    }

    protected function validateExtension($ext, $type) {
        $ext = trim(strtolower($ext));
        if (!isset($this->types[$type]))
            return false;

        $exts = strtolower(helper::clear_whitespaces($this->config['deniedExts']));
        if (strlen($exts)) {
            $exts = explode(" ", $exts);
            if (in_array($ext, $exts))
                return false;
        }

        $exts = trim($this->types[$type]);
        if (!strlen($exts) || substr($exts, 0, 1) == "*")
            return true;

        if (substr($exts, 0, 1) == "!") {
            $exts = explode(" ", trim(strtolower(substr($exts, 1))));
            return !in_array($ext, $exts);
        }

        $exts = explode(" ", trim(strtolower($exts)));
        return in_array($ext, $exts);
    }

    protected function label($string, array $data=null) {
        $return = isset($this->labels[$string]) ? $this->labels[$string] : $string;
        if (is_array($data) && count($data))
            foreach ($data as $key => $val)
                $return = str_replace("{{$key}}", $val, $return);
        return $return;
    }

    protected function backMsg($message) {
        $this->callBack("", $message);
        die;
    }

    protected function localization($langCode) {
        require "lang/{$langCode}.php";
        setlocale(LC_ALL, $lang['_locale']);
        header("Content-Type: text/html; charset={$lang['_charset']}");
        $this->dateTimeFull = $lang['_dateTimeFull'];
        $this->dateTimeMid = $lang['_dateTimeMid'];
        $this->dateTimeSmall = $lang['_dateTimeSmall'];
        unset($lang['_locale']);
        unset($lang['_charset']);
        unset($lang['_dateTimeFull']);
        unset($lang['_dateTimeMid']);
        unset($lang['_dateTimeSmall']);
        $this->labels = $lang;
    }

    protected function callBack($url, $message="") {
        $message = helper::js_value($message);
        $funcNum = $this->CKfuncNum ? $this->CKfuncNum : 0;
?><html>
<body>
<script type='text/javascript'>
var kc_CKEditor = (window.parent && window.parent.CKEDITOR)
    ? window.parent.CKEDITOR.tools.callFunction
    : ((window.opener && window.opener.CKEDITOR)
        ? window.opener.CKEDITOR.tools.callFunction
        : false);
var kc_FCKeditor = (window.opener && window.opener.OnUploadCompleted)
    ? window.opener.OnUploadCompleted
    : ((window.parent && window.parent.OnUploadCompleted)
        ? window.parent.OnUploadCompleted
        : false);
var kc_Custom = (window.parent && window.parent.KCFinder)
    ? window.parent.KCFinder.callBack
    : ((window.opener && window.opener.KCFinder)
        ? window.opener.KCFinder.callBack
        : false);
if (kc_CKEditor)
    kc_CKEditor(<?php echo $funcNum ?>, '<?php echo $url ?>', '<?php echo $message ?>');
if (kc_FCKeditor)
    kc_FCKeditor(<?php echo strlen($message) ? 1 : 0 ?>, '<?php echo $url ?>', '', '<?php echo $message ?>');
if (kc_Custom) {
    if (<?php echo strlen($message) ?>) alert('<?php echo $message ?>');
    kc_Custom('<?php echo $url ?>');
}
</script>
</body>
</html><?php

    }

    protected function get_htaccess() {
        return "<IfModule mod_php4.c>
  php_value engine off
</IfModule>
<IfModule mod_php5.c>
  php_value engine off
</IfModule>
";
    }
}

?>