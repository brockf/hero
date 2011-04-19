<?php /* Smarty version Smarty-3.0.6, created on 2011-04-11 23:24:20
         compiled from "/Volumes/MyData/WebSites/Caribou/trunk/themes/cubed/frontpage.thtml" */ ?>
<?php /*%%SmartyHeaderCode:14424806964da3d3f4a82e93-19931647%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3077ca9cc543e717c4b80ec7737feec6d2fcea66' => 
    array (
      0 => '/Volumes/MyData/WebSites/Caribou/trunk/themes/cubed/frontpage.thtml',
      1 => 1300309855,
      2 => 'file',
    ),
    'f4f042f670dcce9e647da196d5a6831a8364623b' => 
    array (
      0 => '/Volumes/MyData/WebSites/Caribou/trunk/themes/cubed/layout.thtml',
      1 => 1300309855,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '14424806964da3d3f4a82e93-19931647',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_function_url')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.url.php';
if (!is_callable('smarty_function_setting')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.setting.php';
if (!is_callable('smarty_function_theme_url')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.theme_url.php';
if (!is_callable('smarty_block_login_form')) include 'app/modules/users/template_plugins/block.login_form.php';
if (!is_callable('smarty_block_has_cart')) include 'app/modules/store/template_plugins/block.has_cart.php';
if (!is_callable('smarty_function_cart_items')) include 'app/modules/store/template_plugins/function.cart_items.php';
if (!is_callable('smarty_block_no_cart')) include 'app/modules/store/template_plugins/block.no_cart.php';
if (!is_callable('smarty_function_menu')) include 'app/modules/menu_manager/template_plugins/function.menu.php';
if (!is_callable('smarty_block_content')) include 'app/modules/publish/template_plugins/block.content.php';
if (!is_callable('smarty_modifier_date_format')) include '/Volumes/MyData/WebSites/Caribou/trunk/app/libraries/smarty/plugins/modifier.date_format.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
<base href="<?php echo smarty_function_url(array(),$_smarty_tpl);?>
" />
<title><?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>
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
		<a href="<?php echo smarty_function_url(array(),$_smarty_tpl);?>
">
			<img class="logo_mark" src="<?php echo smarty_function_theme_url(array('path'=>"images/logo.jpg"),$_smarty_tpl);?>
" alt="<?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>
" />
		</a>
	
		<a class="logo" href="<?php echo smarty_function_url(array(),$_smarty_tpl);?>
">
			<?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>

			
			<div class="slogan">
				A demo membership website about business
			</div>
		</a>
		
		<div class="top_box account <?php if (!$_smarty_tpl->getVariable('logged_in')->value){?>logged_out<?php }?>">
			<?php if ($_smarty_tpl->getVariable('logged_in')->value){?>
				<h4>Welcome, <?php echo $_smarty_tpl->getVariable('member')->value['first_name'];?>
</h4>
				<ul>
					<li><a href="<?php echo smarty_function_url(array('path'=>"users"),$_smarty_tpl);?>
">Account Manager</a></li>
					<li><a href="<?php echo smarty_function_url(array('path'=>"users/profile"),$_smarty_tpl);?>
">Edit My Profile</a></li>
					<li><a href="<?php echo smarty_function_url(array('path'=>"users/logout"),$_smarty_tpl);?>
">Logout</a></li>
				</ul>
			<?php }else{ ?>
				<h4>My Account</h4>
				<?php $_smarty_tpl->smarty->_tag_stack[] = array('login_form', array('var'=>"login",'return'=>$_smarty_tpl->getVariable('return')->value)); $_block_repeat=true; smarty_block_login_form(array('var'=>"login",'return'=>$_smarty_tpl->getVariable('return')->value), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

				    <form method="post" action="<?php echo $_smarty_tpl->getVariable('login')->value['form_action'];?>
">
				        <input type="hidden" name="return" value="<?php echo $_smarty_tpl->getVariable('login')->value['return'];?>
">
				     
				        <input type="text" class="text mark_empty required" rel="Username" id="username" name="username" value="<?php echo $_smarty_tpl->getVariable('login')->value['username'];?>
"><br />
				        <input type="password" class="text mark_empty" rel="password" id="password" name="password" /><br />
				        <input type="submit" class="button small" name="login" value="Login" />&nbsp;&nbsp;<a class="small_link" href="<?php echo smarty_function_url(array('path'=>"users/register"),$_smarty_tpl);?>
">Register now</a>
				        </ul>
				    </form>
				<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_login_form(array('var'=>"login",'return'=>$_smarty_tpl->getVariable('return')->value), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

			<?php }?>
		</div>

		<div class="top_box search">
			<h4>Site Search</h4>
			<form method="get" action="<?php echo smarty_function_url(array('path'=>"search"),$_smarty_tpl);?>
" class="validate">
				<input type="text" class="text required mark_empty" rel="search query" name="q" /> <input type="submit" class="button small" name="" value="Search" />
			</form>
			<a class="small_link" href="<?php echo smarty_function_url(array('path'=>"search"),$_smarty_tpl);?>
">Advanced Search</a>
		</div>
		
		<div class="top_box cart">
			<h4>Shopping Cart</h3>
			<?php $_smarty_tpl->smarty->_tag_stack[] = array('has_cart', array()); $_block_repeat=true; smarty_block_has_cart(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

				<a href="<?php echo smarty_function_url(array('path'=>"store/cart"),$_smarty_tpl);?>
">You have <?php echo smarty_function_cart_items(array(),$_smarty_tpl);?>
 items in your shopping cart</a>
			<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_has_cart(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

			<?php $_smarty_tpl->smarty->_tag_stack[] = array('no_cart', array()); $_block_repeat=true; smarty_block_no_cart(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

				<a href="<?php echo smarty_function_url(array('path'=>"store/cart"),$_smarty_tpl);?>
">Your shopping cart is currently empty</a>
			<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_no_cart(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

		</div>
	</div>
	
	<div id="navigation">
		<div class="menu_items">
			<?php echo smarty_function_menu(array('name'=>"main_menu",'show_sub_menus'=>"yes"),$_smarty_tpl);?>

			<div style="clear:both"></div>
		</div>
	</div>
	
	<div id="content">
		
	<p class="intro">Welcome to <b><?php echo $_smarty_tpl->getVariable('setting')->value['site_name'];?>
</b>!  Subscribe today for access to the best (fake) business resources!</p>
	
	<div class="frontpage_features">
		<ul>
			<li>
				<div class="image">
					<img src="<?php echo smarty_function_theme_url(array('path'=>"images/placeholders/front_content.png"),$_smarty_tpl);?>
" alt="" /></a>
				</div>
				<div class="text">
					<p>Award winning content.</p>
				</div>
			</li>
			<li>
				<div class="image">
					<img src="<?php echo smarty_function_theme_url(array('path'=>"images/placeholders/front_money.png"),$_smarty_tpl);?>
" alt="" /></a>
				</div>
				<div class="text">
					<p>Tips to increase revenue.</p>
				</div>
			</li>
			<li>
				<div class="image">
					<img src="<?php echo smarty_function_theme_url(array('path'=>"images/placeholders/front_lock.png"),$_smarty_tpl);?>
" alt="" /></a>
				</div>
				<div class="text">
					<p>Exclusively for paid subscribers.</p>
				</div>
			</li>
			<li>
				<div class="image">
					<img src="<?php echo smarty_function_theme_url(array('path'=>"images/placeholders/front_talk.png"),$_smarty_tpl);?>
" alt="" /></a>
				</div>
				<div class="text">
					<p>The best op-ed pieces.</p>
				</div>
			</li>
		</ul>
		<div style="clear:both"></div>
		
		<a class="frontpage_subscribe" href="<?php echo smarty_function_url(array('path'=>"subscriptions"),$_smarty_tpl);?>
">Subscribe Now!</a>
	</div>
	<div class="frontpage_blog">
		<h2>Site News</h2>
		
		<ul>
		<?php $_smarty_tpl->smarty->_tag_stack[] = array('content', array('var'=>"item",'type'=>"news",'limit'=>"5",'sort_dir'=>"DESC",'sort'=>"content.content_date")); $_block_repeat=true; smarty_block_content(array('var'=>"item",'type'=>"news",'limit'=>"5",'sort_dir'=>"DESC",'sort'=>"content.content_date"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

			<li>
				<a class="title" href="<?php echo $_smarty_tpl->getVariable('item')->value['url'];?>
"><?php echo $_smarty_tpl->getVariable('item')->value['title'];?>
</a>
				<h4 class="date"><?php echo smarty_modifier_date_format($_smarty_tpl->getVariable('item')->value['date'],"%b %e");?>
<br /><?php echo smarty_modifier_date_format($_smarty_tpl->getVariable('item')->value['date'],"%Y");?>
</h4>
				<?php echo $_smarty_tpl->getVariable('item')->value['summary'];?>

			</li>
		<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_content(array('var'=>"item",'type'=>"news",'limit'=>"5",'sort_dir'=>"DESC",'sort'=>"content.content_date"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

		</ul>
	</div>
	
	<div style="clear:both"></div>

	</div>
	
	<div style="clear:both"></div>
</div>

<div class="container footer">
	<img id="corner" src="<?php echo smarty_function_theme_url(array('path'=>"images/footer_corner.gif"),$_smarty_tpl);?>
" alt="" />
	
	<?php echo smarty_function_menu(array('name'=>"footer_menu",'class'=>"menu",'show_sub_menus'=>"no"),$_smarty_tpl);?>

	
	Copyright &copy; <?php echo smarty_modifier_date_format(time(),"%Y");?>
, <?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>
.  All Rights Reserved.
	<?php echo smarty_function_menu(array('name'=>"footer_menu_2",'class'=>"menu_2"),$_smarty_tpl);?>

</div>
</body>
</html>