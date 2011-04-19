<?php /* Smarty version Smarty-3.0.6, created on 2011-03-16 16:26:46
         compiled from "/Volumes/MyData/WebSites/Caribou/trunk/themes/orchard/account_templates/home.thtml" */ ?>
<?php /*%%SmartyHeaderCode:6774450384d812b16adc457-39074635%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '04e0ec864b5fc2dae9856b8cafc5dfd4a82c1556' => 
    array (
      0 => '/Volumes/MyData/WebSites/Caribou/trunk/themes/orchard/account_templates/home.thtml',
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
  'nocache_hash' => '6774450384d812b16adc457-39074635',
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
if (!is_callable('smarty_block_has_subscriptions')) include 'app/modules/billing/template_plugins/block.has_subscriptions.php';
if (!is_callable('smarty_block_subscriptions')) include 'app/modules/billing/template_plugins/block.subscriptions.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
<base href="<?php echo smarty_function_url(array(),$_smarty_tpl);?>
" />
<title>
Account Manager - <?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>

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
			
	<h1>Account Manager</h1>
	
	<?php if ($_smarty_tpl->getVariable('notice')->value){?>
		<div class="notices">
			<p><?php echo $_smarty_tpl->getVariable('notice')->value;?>
</p>
		</div>
	<?php }?>
	
	<p>Hello, <?php echo $_smarty_tpl->getVariable('member')->value['first_name'];?>
!</p>
	<p>Welcome back to your account manager.  Here, you can review your account records, update your profile and password, and review
	your purchases.</p>
	<ul class="account_links">
		<li><a href="<?php echo smarty_function_url(array('path'=>"users/profile"),$_smarty_tpl);?>
">Edit your profile</a></li>
		<li><a href="<?php echo smarty_function_url(array('path'=>"users/password"),$_smarty_tpl);?>
">Change your password</a></li>
		<li><a href="<?php echo smarty_function_url(array('path'=>"users/invoices"),$_smarty_tpl);?>
">View all invoices</a></li>
		<li><a href="<?php echo smarty_function_url(array('path'=>"users/logout"),$_smarty_tpl);?>
">Logout</a></li>
	</ul>
	
	<?php $_smarty_tpl->smarty->_tag_stack[] = array('has_subscriptions', array()); $_block_repeat=true; smarty_block_has_subscriptions(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

	<table class="table" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td colspan="2">Your Subscriptions</td>
			</tr>
		</thead>
		<tbody>
		<?php $_smarty_tpl->smarty->_tag_stack[] = array('subscriptions', array('var'=>"sub",'active'=>true)); $_block_repeat=true; smarty_block_subscriptions(array('var'=>"sub",'active'=>true), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

		<?php $_smarty_tpl->tpl_vars["sub_id"] = new Smarty_variable($_smarty_tpl->getVariable('sub')->value['id'], null, null);?>
			<tr>
				<td style="width:50%"><b><?php echo $_smarty_tpl->getVariable('sub')->value['plan']['name'];?>
</b></td>
				<td>
					<?php if ($_smarty_tpl->getVariable('sub')->value['is_recurring']==true){?>Next Charge: <?php echo smarty_modifier_date_format($_smarty_tpl->getVariable('sub')->value['next_charge_date'],"%B %e, %Y");?>

					<?php }else{ ?>Expires: <?php echo smarty_modifier_date_format($_smarty_tpl->getVariable('sub')->value['end_date'],"%B %e, %Y");?>
<?php }?>
					<?php if ($_smarty_tpl->getVariable('sub')->value['is_renewed']==true){?> (Renewed)<?php }?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<ul class="sub_options">
						<?php if ($_smarty_tpl->getVariable('sub')->value['card_last_four']&&$_smarty_tpl->getVariable('sub')->value['is_recurring']){?>
							<li>
								<a href="<?php echo $_smarty_tpl->getVariable('sub')->value['update_cc_link'];?>
">Update Credit Card Information</a>
							</li>
						<?php }?>
						<?php if ($_smarty_tpl->getVariable('sub')->value['last_charge_date']){?>
							<li>
								<a href="<?php echo smarty_function_url(array('path'=>"users/invoices/".($_smarty_tpl->getVariable('sub_id')->value)),$_smarty_tpl);?>
">View Related Invoices</a>
							</li>
						<?php }?>
						<?php if ($_smarty_tpl->getVariable('sub')->value['is_recurring']){?>
							<li>
								<a href="<?php echo $_smarty_tpl->getVariable('sub')->value['cancel_link'];?>
">Cancel Subscription</a>
							</li>
						<?php }?>
						<?php if ($_smarty_tpl->getVariable('sub')->value['is_renewed']==false&&$_smarty_tpl->getVariable('sub')->value['last_charge_date']){?>
							<li>
								<a href="<?php echo $_smarty_tpl->getVariable('sub')->value['renew_link'];?>
">Renew Subscription</a>
							</li>
						<?php }?>
						
						<?php if (!$_smarty_tpl->getVariable('sub')->value['is_recurring']&&!$_smarty_tpl->getVariable('sub')->value['last_charge_date']){?>
							<li>No options available.</li>
						<?php }?>
					</ul>
				</td>
			</tr>
		<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_subscriptions(array('var'=>"sub",'active'=>true), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

		</tbody>
	</table>
	<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_has_subscriptions(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


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