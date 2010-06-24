<?php

/** This file is part KCFinder project
  *
  *      @desc Browser actions class
  *   @package KCFinder
  *   @version 1.7
  *    @author Pavel Tzonkov <pavelc@users.sf.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class browser extends uploader {
    protected $get;
    protected $post;
    protected $cookie;
    protected $session;

    protected $action;
    protected $vars = array();

    public function __construct() {
        parent::__construct();
        $gpc = new gpc();
        $this->get = &$gpc->get;
        $this->post = &$gpc->post;
        $this->cookie = &$gpc->cookie;
        
        if (isset($this->config['_sessionVar'])) {
            $this->config['_sessionVar']['browser'] = array();
            $this->session = &$this->config['_sessionVar']['browser'];
        } else
            $this->session = &$_SESSION;
            
        if (isset($this->post['dir'])) {
            if (substr($this->post['dir'], 0, 1) == "/")
                $this->post['dir'] = substr($this->post['dir'], 1);
            $this->post['dir'] = helper::normalize_path($this->post['dir']);
            if (!$this->checkDir($this->post['dir'], false))
                $this->backMsg($this->label("Unknown error."));
            $parts = explode("/", $this->post['dir']);
            if (isset($this->types[$parts[0]]) && ($this->type != $parts[0]))
                $this->type = $parts[0];

        } elseif (isset($this->get['dir'])) {
            if (substr($this->get['dir'], 0, 1) == "/")
                $this->get['dir'] = substr($this->get['dir'], 1);
            $this->get['dir'] = helper::normalize_path($this->get['dir']);
            if (!$this->checkDir($this->get['dir'], false))
                $this->backMsg($this->label("Unknown error."));
            $parts = explode("/", $this->get['dir']);
            if (isset($this->types[$parts[0]]) && ($this->type != $parts[0]))
                $this->type = $parts[0];
        }
       
        $this->config['uploadDir'] = dirname($this->config['uploadDir']) . "/" . $this->type;
        $this->config['uploadURL'] = dirname($this->config['uploadURL']) . "/" . $this->type;
        
        $thumbsDir = dirname($this->config['uploadDir']) . "/" . $this->config['thumbsDir'];
        if ((
                !is_dir($thumbsDir) &&
                !@mkdir($thumbsDir, $this->config['dirPerms'])
            ) ||

            !is_readable($thumbsDir) ||
            !is_writable($thumbsDir)
        )
            $this->backMsg($this->label("Cannot access or create thumbnails folder."));

        $thumbsDir .= "/" . $this->type;
        if (!is_dir($thumbsDir))
            mkdir($thumbsDir, $this->config['dirPerms']);
    }

    public function getSession() {
        return $this->session;
    }

    public function getConfig($key=null) {
        return ($key === null)
            ? $this->config
            : (isset($this->config[$key])
                ? $this->config[$key]
                : null);
    }

    public function action() {
        if ($this->config['disabled'])
            $this->backMsg($this->label("You don't have permissions to browse server."));

        $act = isset($this->get['act']) ? $this->get['act'] : "browser";
        if (!method_exists($this, "act_$act"))
            $act = "browser";
        $this->action = $act;
        $method = "act_$act";

        $content = $this->$method();

        $this->vars['currDir'] = $this->type . (strlen($this->session['dir']) ? "/{$this->session['dir']}" : "");
        if ($act == "browser") header("X-UA-Compatible: chrome=1");
        echo ($act == "browser")
            ? $this->template("_header") . $content . $this->template("_footer")
            : $content;
    }

    protected function act_browser() {
        $dir = (isset($this->get['dir']) && $this->checkDir($this->get['dir']))
            ? $this->get['dir']
            : (isset($this->session['dir']) ? $this->session['dir'] : "");
        if (strlen($dir) &&
            !is_dir(dirname($this->config['uploadDir']) . "/{$this->type}/$dir")
        )
            $dir = "";
        $this->session['dir'] = $dir;

        $index = (($dir === "") ? "/" : "") . basename($this->config['uploadDir']) . (is_writable($this->config['uploadDir']) ? "/" : "");
        $tree = array($index => $this->getTree("", $dir, true));

        $files = $this->getFiles($dir);

        $this->vars['dir'] = $dir;
        $this->vars['tree'] = &$tree;
        $this->vars['files'] = &$files;
        return $this->template();
    }

    protected function act_expand() {
    	if (!isset($this->post['dir'])) return "";
        list($type) = explode("/", $this->post['dir']);
        if ($type != $this->type) return "";
        $dir = ($this->post['dir'] == $type)
            ? "" : preg_replace('/^[^\/]+\/(.*)/s', "$1", $this->post['dir']);
        if (!$this->checkDir($dir)) return "";
        
        return $this->drawDirs($dir);
    }

    protected function act_chDir() {
        if (!isset($this->post['dir'])) return "";
        list($type) = explode("/", $this->post['dir']);
        if ($type != $this->type) return "";
        $dir = ($this->post['dir'] == $type)
            ? "" : preg_replace('/^[^\/]+\/(.*)/s', "$1", $this->post['dir']);
        if (!$this->checkDir($dir)) return "";
        $this->session['dir'] = $dir;
        $files = $this->getFiles($dir);
        $html = $this->drawFiles($dir, $files);
        return $html ? $html : "&nbsp;";
    }

    protected function act_thumb() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->get['image']))
            return $this->outIcon();
        $file = $this->get['image'];
        $path = "$baseDir/$file";

        if (!file_exists($path))
            return $this->outIcon();

        $thumb = "$baseDir/{$this->config['thumbsDir']}/$file";

        $thumbDir = dirname($thumb);
        if (!is_dir($thumbDir) &&
            !helper::rmkdir($thumbDir, $this->config['dirPerms'])
        )
            return $this->outIcon($file);

        if (file_exists($thumb)) {
            header("Content-type: image/jpeg");
            readfile($thumb);
            return;
        }

        $gd = new gd($path);
        if ($gd->init_error)
            return $this->outIcon($file);

        $browsable = array(
            IMAGETYPE_GIF => "gif",
            IMAGETYPE_JPEG => "jpeg",
            IMAGETYPE_JPEG2000 => "jpeg",
            IMAGETYPE_PNG => "png",
        );

        // Images with smaller resolutions than thumbnails
        if (($gd->get_width() <= $this->config['thumbWidth']) &&
            ($gd->get_height() <= $this->config['thumbHeight'])
        ) {

            // Browsable types
            if (isset($browsable[$gd->type])) {
                header("Content-type: image/{$browsable[$gd->type]}");
                readfile($path);
                return;

            // Non-browsable types
            } elseif (!$gd->imagejpeg($thumb, $this->config['jpegQuality']))
                return $this->outIcon($file);

        // Resize image
        } elseif (
            !$gd->resize_fit($this->config['thumbWidth'], $this->config['thumbHeight']) ||
            !$gd->imagejpeg($thumb, $this->config['jpegQuality'])
        )
           return $this->outIcon($file);

        // Show Image
        header("Content-type: image/jpeg");
        readfile($thumb);
        return;
    }

    protected function act_upload() {
        $baseDir = dirname($this->config['uploadDir']);
        
        if (!isset($this->post['dir']) ||
            (false === ($dir = "$baseDir/{$this->post['dir']}")) ||
            !is_dir($dir) || !is_readable($dir) || !is_writable($dir)
        )
            die($this->label("Cannot access or write to upload folder."));

        $message = $this->checkUploadedFile();
        if ($message !== true) {
            if (isset($this->file['tmp_name']))
                @unlink($this->file['tmp_name']);
            die($message);
        }

        $sufix = ""; $i = 1;
        $ext = $this->getExtension($this->file['name'], false);
        $base = strlen($ext)
            ? substr($this->file['name'], 0, -strlen($ext) - 1)
            : $this->file['name'];
        do {
            $target = "$dir/$base$sufix" . (strlen($ext) ? ".$ext" : "");
            $sufix = "(" . $i++ . ")";
        } while (file_exists($target));

        if (!move_uploaded_file($this->file['tmp_name'], $target)) {
            @unlink($this->file['tmp_name']);
            die($this->label("Cannot move uploaded file to target folder."));
        } elseif (function_exists('chmod'))
            chmod($target, $this->config['filePerms']);

        echo "/" . basename($target);


        // THUMBNAIL GENERATION

        $gd = new gd($target);
        if ($gd->init_error)
            return;

        // Images with smaller resolutions than thumbnails
        if (($gd->get_width() <= $this->config['thumbWidth']) &&
            ($gd->get_height() <= $this->config['thumbHeight'])
        ) {
            $browsable = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG);
            // Browsable types does not need a thumbnail
            if (in_array($gd->type, $browsable))
                return;

        // Resize image
        } elseif (!$gd->resize_fit($this->config['thumbWidth'], $this->config['thumbHeight']))
            return;

        // Check thumbnail directory
        $thumb = "$baseDir/{$this->config['thumbsDir']}/{$this->post['dir']}";
        if (!is_dir($thumb) && !helper::rmkdir($thumb, $this->config['dirPerms']))
            return;
        $thumb .= "/" . basename($target);

        // Save thumbnail
        $gd->imagejpeg($thumb, $this->config['jpegQuality']);
    }

    protected function act_newDir() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->post['dir']) ||
            (false === ($dir = "$baseDir/{$this->post['dir']}")) ||
            !is_dir($dir) || !is_readable($dir) || !is_writable($dir) ||
            !isset($this->post['newDir']) ||
            (false === ($newDir = helper::clear_whitespaces($this->post['newDir']))) ||
            !strlen($newDir) || preg_match('/\//s', $newDir) ||
            (substr($newDir, 0, 1) == ".") ||
            !@mkdir("$baseDir/{$this->post['dir']}/$newDir", $this->config['dirPerms'])
        )
            die($this->label("Unknown error."));
    }

    protected function act_renameDir() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->post['dir']) ||
            (false === ($dir = "$baseDir/{$this->post['dir']}")) ||
            !is_dir($dir) || !is_readable($dir) || !is_writable($dir) ||
            !isset($this->post['newName']) ||
            (false === ($newName = helper::clear_whitespaces($this->post['newName']))) ||
            !strlen($newName) || preg_match('/\//s', $newName) ||
            (substr($newName, 0, 1) == ".") ||
            !@rename(
                "$baseDir/{$this->post['dir']}",
                dirname("$baseDir/{$this->post['dir']}") . "/$newName"
            )
        )
            die($this->label("Unknown error."));

        $thumbDir = "$baseDir/{$this->config['thumbsDir']}/{$this->post['dir']}";
        if (is_dir($thumbDir))
            @rename($thumbDir, dirname($thumbDir) . "/$newName");
    }

    protected function act_deleteDir() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->post['dir']) ||
            (false === ($dir = "$baseDir/{$this->post['dir']}")) ||
            !is_dir($dir) || !is_readable($dir) || !is_writable($dir) ||
            !helper::rrmdir($dir)
        )
            die($this->label("Unknown error."));

        $thumbDir = "$baseDir/{$this->config['thumbsDir']}/{$this->post['dir']}";
        if (is_dir($thumbDir))
            helper::rrmdir($thumbDir);
    }

    protected function act_download() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->post['dir']) ||
            (false === ($dir = "$baseDir/{$this->post['dir']}")) ||
            !is_dir($dir) || !is_readable($dir) ||
            !isset($this->post['file']) ||
            (false === ($file = "$dir/{$this->post['file']}")) ||
            !file_exists($file) || !is_readable($file)
        )
            die($this->label("Unknown error."));

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . str_replace('"', "_", $this->post['file']) . '"');
        header("Content-Transfer-Encoding:­ binary");
        header("Content-Length: " . filesize($file));
        readfile($file);
        die;
    }

    protected function act_rename() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->post['dir']) ||
            (false === ($dir = "$baseDir/{$this->post['dir']}")) ||
            !is_dir($dir) || !is_readable($dir) ||
            !isset($this->post['file']) ||
            (false === ($file = "$dir/{$this->post['file']}")) ||
            !file_exists($file) || !is_readable($file) || !is_writable($file) ||
            !isset($this->post['newName']) ||
            (false === ($newName = helper::clear_whitespaces($this->post['newName']))) ||
            !strlen($newName) || preg_match('/\//s', $newName) ||
            (substr($newName, 0, 1) == ".")
        )
            die($this->label("Unknown error."));

        list($type) = explode("/", $this->post['dir']);
        $newName = "$baseDir/{$this->post['dir']}/$newName";
        $ext = $this->getExtension($newName);
        if (!$this->validateExtension($ext, $type))
            die($this->label("Denied file extension."));

        if (!@rename($file, $newName))
            die($this->label("Unknown error."));

        $thumbDir = "$baseDir/{$this->config['thumbsDir']}/{$this->post['dir']}";
        $thumbFile = "$thumbDir/{$this->post['file']}";

        if (file_exists($thumbFile))
            @rename($thumbFile, "$thumbDir/{$this->post['newName']}");
    }

    protected function act_delete() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->post['dir']) ||
            (false === ($dir = "$baseDir/{$this->post['dir']}")) ||
            !is_dir($dir) || !is_readable($dir) ||
            !isset($this->post['file']) ||
            (false === ($file = "$dir/{$this->post['file']}")) ||
            !file_exists($file) || !is_readable($file) || !is_writable($file) ||
            !@unlink($file)
        )
            die($this->label("Unknown error."));

        $thumbDir = "$baseDir/{$this->config['thumbsDir']}/{$this->post['dir']}";
        $thumbFile = "$thumbDir/{$this->post['file']}";

        if (file_exists($thumbFile))
            @unlink($thumbFile);
    }

    protected function act_cp_cbd() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->post['dir']) ||
            (false === ($dir = "$baseDir/{$this->post['dir']}")) ||
            !is_dir($dir) || !is_readable($dir) || !is_writable($dir) ||
            !isset($this->post['files']) || !is_array($this->post['files']) ||
            !count($this->post['files'])
        )
            die($this->label("Unknown error."));

        $message = "";
        foreach($this->post['files'] as $file) {
            $path = "$baseDir/$file";
            $base = basename($file);
            $replace = array('file' => $base);
            $ext = $this->getExtension($base);
            $type = explode("/", $file);
            $type = $type[0];
            if (!file_exists($path))
                $message .= $this->label("The file '{file}' does not exist.", $replace) . "\n";
            elseif (substr($base, 0, 1) == ".")
                $message .= $this->label("File name shouldn't begins with '.'");
            elseif (!$this->validateExtension($ext, $type))
                $message .= $this->label("Denied file extension.");
            elseif (!is_readable($path) || !is_file($path))
                $message .= $this->label("Cannot read '{file}'.", $replace) . "\n";
            elseif (!@copy($path, "$dir/$base"))
                $message .= $this->label("Cannot copy '{file}'.", $replace) . "\n";
            else {
                @chmod("$dir/$base", $this->config['filePerms']);
                $fromThumb = "$baseDir/{$this->config['thumbsDir']}/$file";
                if (file_exists($fromThumb) &&
                    is_readable($fromThumb) &&
                    is_file($fromThumb)
                ) {
                    $toThumb = "$baseDir/{$this->config['thumbsDir']}/{$this->post['dir']}";
                    if (!is_dir($toThumb))
                        helper::rmkdir($toThumb, $this->config['dirPerms']);
                    $toThumb .= "/$base";
                    @copy($fromThumb, $toThumb);
                }
            }
        }
        if (strlen($message)) die(substr($message, 0, -1));
    }

    protected function act_mv_cbd() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->post['dir']) ||
            (false === ($dir = "$baseDir/{$this->post['dir']}")) ||
            !is_dir($dir) || !is_readable($dir) || !is_writable($dir) ||
            !isset($this->post['files']) || !is_array($this->post['files']) ||
            !count($this->post['files'])
        )
            die($this->label("Unknown error."));

        $message = "";
        foreach($this->post['files'] as $file) {
            $path = "$baseDir/$file";
            $base = basename($file);
            $replace = array('file' => $base);
            $ext = $this->getExtension($base);
            $type = explode("/", $file);
            $type = $type[0];
            if (!file_exists($path))
                $message .= $this->label("The file '{file}' does not exist.", $replace) . "\n";
            elseif (substr($base, 0, 1) == ".")
                $message .= $this->label("File name shouldn't begins with '.'");
            elseif (!$this->validateExtension($ext, $type))
                $message .= $this->label("Denied file extension.");
            elseif (!is_readable($path) || !is_file($path))
                $message .= $this->label("Cannot read '{file}'.", $replace) . "\n";
            elseif (!@rename($path, "$dir/$base"))
                $message .= $this->label("Cannot move '{file}'.", $replace) . "\n";
            else {
                $fromThumb = "$baseDir/{$this->config['thumbsDir']}/$file";
                if (is_file($fromThumb) &&
                    is_readable($fromThumb) &&
                    is_writable($fromThumb)
                ) {
                    $toThumb = "$baseDir/{$this->config['thumbsDir']}/{$this->post['dir']}";
                    if (!is_dir($toThumb))
                        helper::rmkdir($toThumb, $this->config['dirPerms']);
                    $toThumb .= "/$base";
                    @rename($fromThumb, $toThumb);
                }
            }

        }
        if (strlen($message)) die(substr($message, 0, -1));
    }

    protected function act_rm_cbd() {
        $baseDir = dirname($this->config['uploadDir']);
        if (!isset($this->post['files']) || !is_array($this->post['files']) ||
            !count($this->post['files'])
        )
            die($this->label("Unknown error."));

        $message = "";
        foreach($this->post['files'] as $file) {
            $path = "$baseDir/$file";
            $base = basename($file);
            $replace = array('file' => $base);
            if (!is_file($path))
                $message .= $this->label("The file '{file}' does not exist.", $replace) . "\n";
            elseif (!@unlink($path))
                $message .= $this->label("Cannot delete '{file}'.", $replace) . "\n";
            else {
                $thumb = "$baseDir/{$this->config['thumbsDir']}/$file";
                if (is_file($thumb)) @unlink($thumb);
            }
        }
        if (strlen($message)) die(substr($message, 0, -1));
    }

    protected function getIcon($file="", $res="big") {
        $ext = $this->getExtension($file);

        $icon = "themes/{$this->config['theme']}/img/files/$res/" .
            ($ext ? $ext : ".") . ".png";

        if (!file_exists($icon))
            $icon = dirname($icon) . "/..png";

        return $icon;
    }

    protected function outIcon($file="") {
        header("Content-Type: image/png");
        readfile($this->getIcon($file));
    }

    protected function getFullPath($dir) {
        return $this->config['uploadDir'] . (strlen($dir) ? "/$dir" : "");
    }

    protected function getFiles($dir) {
        $baseDir = dirname($this->config['uploadDir']);
        $r_dir = $dir;
        $dir = $this->getFullPath($dir);
        $workDir = getcwd();
        chdir($dir);
        $all = glob("*", GLOB_NOESCAPE);
        chdir($workDir);
        if ($all === false) return false;
        $files = array();
        foreach ($all as $i => $file) {
            $thumb = "$baseDir/{$this->config['thumbsDir']}/{$this->type}/" . (strlen($r_dir) ? "$r_dir/" : "") . $file;
            $full = "$dir/$file";
            if (!is_dir($full) &&
                (substr($file, 0, 1) != ".") &&
                is_readable($full)
            ) {
                $files[$file] = array(
                    'time' => filectime($full),
                    'size' => filesize($full),
                    'bigIcon' => $this->getIcon($file),
                    'smallIcon' => $this->getIcon($file, "small"),
                    'writable' => is_writable($full),
                    'hasThumb' => file_exists($thumb)
                );

                $files[$file]['thumb'] = (@getimagesize($full) !== false)
                    ? ("browse.php?act=thumb&amp;image=" . urlencode("{$this->type}/" .
                        (strlen($r_dir) ? "$r_dir/" : "") . $file))
                    : "";
            }
        }

        return $files;
    }

    protected function getDirs($dir) {
        $dir = $this->getFullPath($dir);
        $workDir = getcwd();
        chdir($dir);
        $dirs = glob("*", GLOB_NOESCAPE | GLOB_ONLYDIR);
        chdir($workDir);
        if ($dirs === false) return false;
        foreach ($dirs as $i => $currentDir)
            if ((substr($currentDir, 0, 1) == ".") || !is_readable("$dir/$currentDir"))
                unset($dirs[$i]);
            elseif (is_writable("$dir/$currentDir"))
                $dirs[$i] .= "/";
        return array_values($dirs);
    }

    protected function getTree($dir, $path) {
        if (substr($dir, 0, 1) == "/") $dir = substr($dir, 1);
        if (!$this->checkDir($dir) || (false === ($dirs = $this->getDirs($dir))))
            return false;

        $tree = array();
        foreach ($dirs as $c_dir) {
            $r_dir = strlen($dir) ? "$dir/$c_dir" : $c_dir;
            if (substr($r_dir, -1) == "/") $r_dir = substr($r_dir, 0, -1);
            $dirIndex = ($r_dir == $path) ? "/$c_dir" : $c_dir;
            if ($r_dir == $path)
                $tree[$dirIndex] = $this->getTree($r_dir, $path);
            elseif ($r_dir == substr($path, 0, strlen($r_dir)))
                $tree[$dirIndex] = $this->getTree($r_dir, $path);
            else
                $tree[$dirIndex] = $this->checkDir($r_dir);
        }
        return $tree;
    }

    protected function checkDir($dir, $isExists=true) {
        $dir = helper::normalize_path($dir);
        if (substr($dir, 0, 1) == "/")
            $dir = substr($dir, 1);
        if(substr($dir, 0, 1) == ".")
            return false;
        if (!$isExists)
            return true;
        $dir = $this->getFullPath($dir);
        return (is_dir($dir) && is_readable($dir));
    }

    protected function template($template=null) {
        if ($template === null)
            $template = $this->action;

        if (file_exists("tpl/tpl_$template.php")) {
            foreach (array_keys($this->vars) as $key)
                eval("\$_$key = &\$this->vars['$key'];");
            ob_start();
            require "tpl/tpl_$template.php";
            return ob_get_clean();
        }

        return "";
    }

    protected function drawTree(array $tree, $path, $url="", $first=true) {
        ob_start();
        foreach ($tree as $dir => $dirs) {
            if (substr($dir, 0, 1) == "/") {
                $d_dir = substr($dir, 1);
                $folder = "current";
            } else {
                $d_dir = $dir;
                $folder = "regular";
            }

            if (substr($d_dir, -1) == "/")
                $d_dir = substr($d_dir, 0, -1);

            $expand = ($dirs === true) ? "closed" :
                (is_array($dirs) ? "opened" : "denied");

            $f_url = helper::html_value((strlen($url) ? "$url/" : "") . $d_dir);

?><div class="folder"><a href="kcdir:/<?php echo $f_url ?>" target="<?php echo (substr($dir, -1) == "/") ? ($first ? "first" : "writable") : "readonly" ?>"><span class="brace <?php echo $expand ?>">&nbsp;</span><span class="folder <?php echo $folder ?>"><?php echo $d_dir ?></span></a><?php

            if (is_array($dirs) && count($dirs)) {

?><div class="folders">
<?php echo $this->drawTree($dirs, $path, $f_url, false) ?>
</div><?php

            }

?></div><?php

        }
        return ob_get_clean();
    }

    protected function drawDirs($dir) {
        ob_start();
        $dirs = $this->getDirs($dir);
        
        if (!is_array($dirs)) {
        	return FALSE;
        }
        foreach ($dirs as $c_dir) {
            $d_dir = (substr($c_dir, -1) == "/") ? substr($c_dir, 0, -1) : $c_dir;
            $s_dir = (strlen($dir) ? "$dir/" : "") . $d_dir;
            $url = "kcdir:/{$this->type}" . (strlen($dir) ? "/$dir" : "") . "/$d_dir";
            $folder_status = (@$this->session['dir'] == $s_dir) ? "current" : "regular";

?><div class="folder"><a  style="display:block" href="<?php echo $url ?>" target="<?php echo ($c_dir != $d_dir) ? "writable" : "readonly" ?>"><span class="brace closed">&nbsp;</span><span class="folder <?php echo $folder_status ?>"><?php echo $d_dir ?></span></a></div><?php

        }
        return ob_get_clean();
    }

    protected function drawFiles($dir, array $files) {
    	ob_start();
        $basePath = $this->config['uploadDir'] . "/$dir";
        $baseURL = $this->config['uploadURL'] . "/$dir";

        foreach ($files as $name => $file) {

?><div>
<div class="name"><?php echo $name ?></div>
<div class="time"><?php echo $file['time'] ?></div>
<div class="size"><?php echo $file['size'] ?></div>
<div class="date"><?php echo strftime($this->dateTimeSmall, $file['time']) ?></div>
<div class="bigIcon"><?php echo $file['bigIcon'] ?></div>
<div class="smallIcon"><?php echo $file['smallIcon'] ?></div>
<div class="thumb"><?php echo $file['thumb'] ?></div>
<div class="access"><?php echo $file['writable'] ? "writable" : "readonly" ?></div>
<div class="hasThumb"><?php echo $file['hasThumb'] ? "yes" : "no" ?></div>
</div><?php

        }

        return preg_replace('/\r?\n/s', "", ob_get_clean());
    }

    protected function backMsg($message) {
        $act = isset($this->get['act']) ? $this->get['act'] : "browser";
        if (!method_exists($this, "act_$act"))
            $act = "browser";
        if ($act == "browser")
            parent::backMsg($message);
        else
            die($message);
    }
}

?>