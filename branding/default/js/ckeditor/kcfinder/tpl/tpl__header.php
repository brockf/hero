<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>KCFinder: /<?php echo $_currDir ?></title>
<link href="css/base.css.php" rel="stylesheet" type="text/css" />
<link href="themes/<?php echo $this->config['theme'] ?>/style.css" rel="stylesheet" type="text/css" />
<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/jquery.rightClick.js" type="text/javascript"></script>
<script src="js/helper.js" type="text/javascript"></script>
<script src="js/browser/" type="text/javascript"></script>
<?php

if (isset($this->get['opener']) &&
    (strtolower($this->get['opener']) == "tinymce") &&
    isset($this->config['_tinyMCEPath']) &&
    strlen($this->config['_tinyMCEPath'])
) {

?>
<script src="<?php echo $this->config['_tinyMCEPath'] ?>/tiny_mce_popup.js" type="text/javascript"></script>
<?php

}

if (file_exists("themes/{$this->config['theme']}/init.js")) {

?>
<script src="themes/<?php echo $this->config['theme'] ?>/init.js" type="text/javascript"></script>
<?php

}

?><script type="text/javascript">
browser.chromeFrame = <?php echo (strpos($_SERVER['HTTP_USER_AGENT'], " chromeframe") !== false) ? "true" : "false" ?>;
browser.lang = "<?php echo $this->lang ?>";
browser.uploadURL = "<?php echo helper::js_value(dirname($this->config['uploadURL'])) ?>";
browser.thumbsURL = browser.uploadURL + "/<?php echo helper::js_value($this->config['thumbsDir']) ?>";
browser.CKfuncNum = <?php echo $this->CKfuncNum ? $this->CKfuncNum : 0 ?>;
<?php

if (isset($this->get['opener']) && ($this->get['opener'] != "tinymce")) {

?>browser.opener = "<?php echo helper::js_value(strtolower($this->get['opener'])) ?>";
<?php

}

$labels = array("files", "Uploading file...", "New Subfolder...", "Rename...", "Delete", "Please eneter new folder name.", "OK", "Cancel", "New folder name:", "Unallowed characters in folder name.", "Folder name shouldn't begins with '.'", "Are you sure you want to delete this folder and all its content?", "Select", "Select Thumbnail", "View", "Download", "New file name:" , "Please eneter new file name.", "Unallowed characters in file name.", "File name shouldn't begins with '.'", "Are you sure you want to delete this file?", "Loading image...", "Loading files...", "Loading folders...", "Clipboard", "Click to remove from the Clipboard", "Copy files here", "Move files here", "Delete files", "Clear the Clipboard", "Are you sure you want to delete all files in the clipboard?", "Copy {count} files", "Move {count} files", "Add to Clipboard", "This file is already added to the Clipboard.");
foreach ($labels as $label) {
    $labelKey = helper::js_value($label);
    $labelVal = helper::js_value($this->label($label));
    if ($labelKey != $labelVal) {

?>
browser.labels['<?php echo $labelKey ?>'] = "<?php echo $labelVal ?>";
<?php

    }
}

?>
_.kuki.domain = "<?php echo helper::js_value($this->config['cookieDomain']) ?>";
_.kuki.path = "<?php echo helper::js_value($this->config['cookiePath']) ?>";
_.kuki.prefix = "<?php echo helper::js_value($this->config['cookiePrefix']) ?>";

$(document).ready(function() {
    browser.resize();
    browser.init();
    $('#all').css('visibility', 'visible');
});
$(window).resize(browser.resize);
<?php

if (isset($_headJS)) echo $_headJS;

?></script></head>
<body>
<script type="text/javascript">
$('body').noContext();
</script>
<div id="shadow"></div>
<div id="dialog"></div>
<div id="clipboard"></div>
<div class="data">
<span class="currentDir"><?php echo $_currDir ?></span>
</div>
<div id="all">
