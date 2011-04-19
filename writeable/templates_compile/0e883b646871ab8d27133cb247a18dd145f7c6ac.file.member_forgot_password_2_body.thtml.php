<?php /* Smarty version Smarty-3.0.6, created on 2011-04-11 23:09:17
         compiled from "/Volumes/MyData/WebSites/Caribou/trunk/writeable/email_templates/member_forgot_password_2_body.thtml" */ ?>
<?php /*%%SmartyHeaderCode:11347218014da3d06db3a643-33189605%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0e883b646871ab8d27133cb247a18dd145f7c6ac' => 
    array (
      0 => '/Volumes/MyData/WebSites/Caribou/trunk/writeable/email_templates/member_forgot_password_2_body.thtml',
      1 => 1300310762,
      2 => 'file',
    ),
    '3bc42f64de225594de66a0f927d4bbc5dc02266e' => 
    array (
      0 => '/Volumes/MyData/WebSites/Caribou/trunk/writeable/email_templates/email_layout.thtml',
      1 => 1300310762,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11347218014da3d06db3a643-33189605',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_function_url')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.url.php';
if (!is_callable('smarty_function_setting')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.setting.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style>
h1 { background-color: #e6f2f8; color: #0b5679; font-size: 18pt; font-weight: normal; font-family: helvetica, arial, sans-serif; margin: 0 0 10px 0; padding: 7px 10px }
div.body { padding: 10px; font-size: 10pt; font-family: helvetica, arial, sans-serif; color: #111; }
</style>
</head>
<body>
<div class="body">
	
<p>Hi <?php echo $_smarty_tpl->getVariable('member')->value['first_name'];?>
,</p>

<p>You requested a new password at <?php echo $_smarty_tpl->getVariable('site_name')->value;?>
.  Your new login information is below.</p>

<p><b>Username</b>: <?php echo $_smarty_tpl->getVariable('member')->value['username'];?>
</p>
<p><b>Password</b>: <?php echo $_smarty_tpl->getVariable('new_password')->value;?>
</p>

<a href="<?php echo smarty_function_url(array(),$_smarty_tpl);?>
">Click here to login now</a>.


	
	<?php echo smarty_function_setting(array('name'=>"email_signature"),$_smarty_tpl);?>

</div>
</body>
</html>