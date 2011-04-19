<?php /* Smarty version Smarty-3.0.6, created on 2011-04-11 13:45:13
         compiled from "/Volumes/MyData/WebSites/Caribou/trunk/themes/orchard/store_product.thtml" */ ?>
<?php /*%%SmartyHeaderCode:2423090794da34c39afc331-04536976%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8647181da1c83bb12f91a4be279b46e7b787ee8d' => 
    array (
      0 => '/Volumes/MyData/WebSites/Caribou/trunk/themes/orchard/store_product.thtml',
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
  'nocache_hash' => '2423090794da34c39afc331-04536976',
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
if (!is_callable('smarty_function_thumbnail')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.thumbnail.php';
if (!is_callable('smarty_function_money_format')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.money_format.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
<base href="<?php echo smarty_function_url(array(),$_smarty_tpl);?>
" />
<title>
<?php echo $_smarty_tpl->getVariable('name')->value;?>
 - <?php echo smarty_function_setting(array('name'=>"site_name"),$_smarty_tpl);?>

</title>
<link href="<?php echo smarty_function_theme_url(array('path'=>"css/universal.css"),$_smarty_tpl);?>
" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo smarty_function_url(array('path'=>"themes/_common/jquery-1.4.2.min.js"),$_smarty_tpl);?>
"></script>
<script type="text/javascript" src="<?php echo smarty_function_theme_url(array('path'=>"js/universal.js"),$_smarty_tpl);?>
"></script>
<script type="text/javascript" src="<?php echo smarty_function_theme_url(array('path'=>"js/form.js"),$_smarty_tpl);?>
"></script>

	<link rel="stylesheet" type="text/css" href="<?php echo smarty_function_url(array('path'=>"themes/_common/shadowbox/shadowbox.css"),$_smarty_tpl);?>
" />
	<script type="text/javascript" src="<?php echo smarty_function_url(array('path'=>"themes/_common/shadowbox/shadowbox.js"),$_smarty_tpl);?>
"></script>
	<script type="text/javascript">
		Shadowbox.init();
	</script>
	


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
			
	<h1><?php echo $_smarty_tpl->getVariable('name')->value;?>
</h1>
	
	<div class="product">
		<?php if ($_smarty_tpl->getVariable('images')->value){?>
			<div class="images">
				<a rel="shadowbox[product_images]" href="<?php echo $_smarty_tpl->getVariable('feature_image_url')->value;?>
" class="feature_image"><img src="<?php echo smarty_function_thumbnail(array('path'=>$_smarty_tpl->getVariable('feature_image')->value,'height'=>"165",'width'=>"165"),$_smarty_tpl);?>
" alt="<?php echo $_smarty_tpl->getVariable('name')->value;?>
" /></a>
				<ul>
					<?php  $_smarty_tpl->tpl_vars['image'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('images')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['image']->key => $_smarty_tpl->tpl_vars['image']->value){
?>
						<?php if ($_smarty_tpl->tpl_vars['image']->value['path']!=$_smarty_tpl->getVariable('feature_image')->value){?>
							<?php $_smarty_tpl->tpl_vars["image_id"] = new Smarty_variable($_smarty_tpl->tpl_vars['image']->value['id'], null, null);?>
							<li><a rel="shadowbox[product_images]" href="<?php echo $_smarty_tpl->tpl_vars['image']->value['url'];?>
"><img src="<?php echo smarty_function_thumbnail(array('path'=>$_smarty_tpl->tpl_vars['image']->value['path'],'height'=>"50",'width'=>"50"),$_smarty_tpl);?>
" alt="click to enlarge" title="click to enlarge" /></a>
						<?php }?>
					<?php }} ?>
				</ul>
			</div>
		<?php }?>
		<div class="description <?php if (!$_smarty_tpl->getVariable('images')->value){?>full<?php }?>">
			<div class="cart_form">
				<?php if ($_smarty_tpl->getVariable('track_inventory')->value&&!$_smarty_tpl->getVariable('inventory_allow_oversell')->value&&$_smarty_tpl->getVariable('inventory')->value<1){?>
					<p>Unfortunately, this product is sold out.  Please check back again later.</p>
				<?php }else{ ?>
					<span class="price"><?php echo smarty_function_setting(array('name'=>"currency_symbol"),$_smarty_tpl);?>
<?php echo $_smarty_tpl->getVariable('price')->value;?>
</span>
					
					<form method="post" action="<?php echo smarty_function_url(array('path'=>"store/add_to_cart"),$_smarty_tpl);?>
">
						<input type="hidden" name="product_id" value="<?php echo $_smarty_tpl->getVariable('id')->value;?>
" />
						
						<?php if ($_smarty_tpl->getVariable('options')->value){?>
							<ul class="options">
							<?php  $_smarty_tpl->tpl_vars['option'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('options')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['option']->key => $_smarty_tpl->tpl_vars['option']->value){
?>
								<li>
									<select name="option_<?php echo $_smarty_tpl->tpl_vars['option']->value;?>
">
										<?php if ($_smarty_tpl->getVariable('product_options')->value[$_smarty_tpl->tpl_vars['option']->value]['options']){?>
											<?php  $_smarty_tpl->tpl_vars['value'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('product_options')->value[$_smarty_tpl->tpl_vars['option']->value]['options']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['value']->key => $_smarty_tpl->tpl_vars['value']->value){
?>
												<option value="<?php echo $_smarty_tpl->tpl_vars['value']->value['label'];?>
"><?php echo $_smarty_tpl->tpl_vars['value']->value['label'];?>
<?php if ($_smarty_tpl->tpl_vars['value']->value['price']!="0"){?> (<?php echo smarty_function_setting(array('name'=>"currency_symbol"),$_smarty_tpl);?>
<?php echo smarty_function_money_format(array('value'=>$_smarty_tpl->tpl_vars['value']->value['price']),$_smarty_tpl);?>
)<?php }?></option>
											<?php }} ?>
										<?php }?>
									</select>
								</li>
							<?php }} ?>
							</ul>
						<?php }?>
						
						Quantity: <input type="text" style="width: 40px" name="quantity" value="1" />
						<input type="submit" class="button" name="add_to_cart" value="Add to Cart" />
					</form>
				<?php }?>
			</div>
			
			<?php echo $_smarty_tpl->getVariable('description')->value;?>

		</div>
	</div>

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