<?php /* Smarty version Smarty-3.0.6, created on 2011-03-16 16:26:42
         compiled from "/Volumes/MyData/WebSites/Caribou/trunk/themes/orchard/account_templates/login.thtml" */ ?>
<?php /*%%SmartyHeaderCode:15235945354d812b125127a1-05155455%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9113c0117fef70e4913c8603a8ef98d236f45d5f' => 
    array (
      0 => '/Volumes/MyData/WebSites/Caribou/trunk/themes/orchard/account_templates/login.thtml',
      1 => 1300309857,
      2 => 'file',
    ),
    'ce343879aec0a59ba7a607753dabbd839bb143a0' => 
    array (
      0 => '/Volumes/MyData/WebSites/Caribou/trunk/themes/orchard/layout.thtml',
      1 => 1300309857,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '15235945354d812b125127a1-05155455',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_function_url')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.url.php';
if (!is_callable('smarty_function_setting')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.setting.php';
if (!is_callable('smarty_function_theme_url')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.theme_url.php';
if (!is_callable('smarty_block_has_cart')) include 'app/modules/store/template_plugins/block.has_cart.php';
if (!is_callable('smarty_function_cart_items')) include 'app/modules/store/template_plugins/function.cart_items.php';
if (!is_callable('smarty_modifier_date_format')) include '/Volumes/MyData/WebSites/Caribou/trunk/app/libraries/smarty/plugins/modifier.date_format.php';
if (!is_callable('smarty_function_menu')) include 'app/modules/menu_manager/template_plugins/function.menu.php';
if (!is_callable('smarty_block_content')) include 'app/modules/publish/template_plugins/block.content.php';
if (!is_callable('smarty_block_login_form')) include 'app/modules/users/template_plugins/block.login_form.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
<base href="<?php echo smarty_function_url(array(),$_smarty_tpl);?>
" />
<title>
Account Login - <?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>

</title>
<link href="<?php echo smarty_function_theme_url(array('path'=>"css/universal.css"),$_smarty_tpl);?>
" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo smarty_function_url(array('path'=>"themes/_common/jquery-1.4.2.min.js"),$_smarty_tpl);?>
"></script>
<script type="text/javascript" src="<?php echo smarty_function_theme_url(array('path'=>"js/universal.js"),$_smarty_tpl);?>
"></script>
<script type="text/javascript" src="<?php echo smarty_function_theme_url(array('path'=>"js/form.js"),$_smarty_tpl);?>
"></script>


</head>
<body>
<div id="notices"></div>

<div class="container">
	<div id="header">
		<a class="logo" href="<?php echo smarty_function_url(array(),$_smarty_tpl);?>
">
			<img src="<?php echo smarty_function_theme_url(array('path'=>"images/logo.jpg"),$_smarty_tpl);?>
" alt="<?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>
" />
			
			<div class="logo_text">
				<?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>

				
				<div class="slogan">
					Orchard, a simple theme for business
				</div>
			</div>
		</a>

		<div class="date">
			<?php $_smarty_tpl->smarty->_tag_stack[] = array('has_cart', array()); $_block_repeat=true; smarty_block_has_cart(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

				<a href="<?php echo smarty_function_url(array('path'=>"store/cart"),$_smarty_tpl);?>
">Shopping Cart (<?php echo smarty_function_cart_items(array(),$_smarty_tpl);?>
)</a> | 
			<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_has_cart(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

			<?php echo smarty_modifier_date_format(time(),"%A, %B %e, %Y");?>

		</div>
		
		<div class="search">
			<form method="get" action="<?php echo smarty_function_url(array('path'=>"search"),$_smarty_tpl);?>
" class="validate">
				<input type="text" class="text required mark_empty" rel="search query" name="q" /> <input type="submit" class="button small" name="" value="Search" />
			</form>
		</div>
	</div>
	
	<div id="navigation">
		<?php echo smarty_function_menu(array('name'=>"main_menu",'show_sub_menus'=>"yes"),$_smarty_tpl);?>

		<div style="clear:both"></div>
	</div>
	
	<div id="banner">
		<img src="<?php echo smarty_function_theme_url(array('path'=>"images/banner.jpg"),$_smarty_tpl);?>
" alt="" />
	</div>
	
	<div id="content">
		<div class="sidebar">
			<h3>Latest News</h3>
			
			<ul class="news">
				<?php $_smarty_tpl->smarty->_tag_stack[] = array('content', array('var'=>"news",'type'=>"news",'limit'=>"5")); $_block_repeat=true; smarty_block_content(array('var'=>"news",'type'=>"news",'limit'=>"5"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

				<li>
					<span class="date"><?php echo smarty_modifier_date_format($_smarty_tpl->getVariable('news')->value['date'],"%B %e, %Y");?>
</span>
					<a href="<?php echo $_smarty_tpl->getVariable('news')->value['url'];?>
"><?php echo $_smarty_tpl->getVariable('news')->value['title'];?>
</a>
				</li>
				<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_content(array('var'=>"news",'type'=>"news",'limit'=>"5"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

			</ul>
			
			<a class="rss" href="<?php echo smarty_function_url(array('path'=>"news_rss"),$_smarty_tpl);?>
">RSS Feed</a>
		</div>
		
		<div class="inner_content">
			
	<?php $_smarty_tpl->smarty->_tag_stack[] = array('login_form', array('var'=>"login",'return'=>$_smarty_tpl->getVariable('return')->value,'username'=>$_smarty_tpl->getVariable('username')->value)); $_block_repeat=true; smarty_block_login_form(array('var'=>"login",'return'=>$_smarty_tpl->getVariable('return')->value,'username'=>$_smarty_tpl->getVariable('username')->value), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

		<h1>Account Login</h1>
		<form class="form validate" method="post" action="<?php echo $_smarty_tpl->getVariable('login')->value['form_action'];?>
">
			<input type="hidden" name="return" value="<?php echo $_smarty_tpl->getVariable('login')->value['return'];?>
">
			
			<?php if ($_smarty_tpl->getVariable('validation_errors')->value){?>
				<div class="errors">
					<?php echo $_smarty_tpl->getVariable('validation_errors')->value;?>

				</div>
			<?php }?>
			
			<?php if ($_smarty_tpl->getVariable('notices')->value){?>
				<div class="notices">
					<?php echo $_smarty_tpl->getVariable('notices')->value;?>

				</div>
			<?php }?>
		
			<ul class="form">
				<li>
					<label for="username">Username/Email</label>
					<input type="text" class="text required" id="username" name="username" value="<?php echo $_smarty_tpl->getVariable('login')->value['username'];?>
">
				</li>
				<li>
					<label for="password">Password</label>
					<input type="password" class="text required" id="password" name="password" />
				</li>
				<li class="indent">
					<input type="checkbox" value="1" name="remember" /> Remember me for future visits?
				</li>
				<li class="indent">
					<input type="submit" class="button" name="login" value="Login" />
				</li>
			</ul>
			
			<ul class="login_form_links">
				<li>
					<a href="<?php echo smarty_function_url(array('path'=>"users/register"),$_smarty_tpl);?>
">Don't have an account? Click here to register.</a>
				</li>
				<li>
					<a href="<?php echo smarty_function_url(array('path'=>"users/forgot_password"),$_smarty_tpl);?>
">Forgot your password?</a>
				</li>
			</ul>
		</form>
	<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_login_form(array('var'=>"login",'return'=>$_smarty_tpl->getVariable('return')->value,'username'=>$_smarty_tpl->getVariable('username')->value), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


		</div>
		
		<div style="clear:both"></div>
	</div>
</div>

<div class="container footer">
	Copyright &copy; <?php echo smarty_modifier_date_format(time(),"%Y");?>
, <?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>
.  All Rights Reserved.
	<?php echo smarty_function_menu(array('name'=>"footer_menu",'class'=>"menu"),$_smarty_tpl);?>

</div>
</body>
</html>