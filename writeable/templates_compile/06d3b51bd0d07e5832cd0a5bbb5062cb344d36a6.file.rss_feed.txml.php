<?php /* Smarty version Smarty-3.0.6, created on 2011-04-11 23:02:20
         compiled from "/Volumes/MyData/WebSites/Caribou/trunk/themes/orchard/rss_feed.txml" */ ?>
<?php /*%%SmartyHeaderCode:1061797784da3cecc283585-75423819%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '06d3b51bd0d07e5832cd0a5bbb5062cb344d36a6' => 
    array (
      0 => '/Volumes/MyData/WebSites/Caribou/trunk/themes/orchard/rss_feed.txml',
      1 => 1300309857,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1061797784da3cecc283585-75423819',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if (!is_callable('smarty_function_setting')) include '/Volumes/MyData/WebSites/Caribou/trunk/themes/_plugins/function.setting.php';
if (!is_callable('smarty_modifier_date_format')) include '/Volumes/MyData/WebSites/Caribou/trunk/app/libraries/smarty/plugins/modifier.date_format.php';
?><?php echo '<?xml';?> version="1.0" encoding="utf-8"<?php echo '?>';?>
<rss version="2.0"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:admin="http://webns.net/mvcb/"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:content="http://purl.org/rss/1.0/modules/content/">

    <channel>
    
    <title><?php echo $_smarty_tpl->getVariable('title')->value;?>
</title>

    <link><?php echo $_smarty_tpl->getVariable('url')->value;?>
</link>
    <description><?php echo $_smarty_tpl->getVariable('description')->value;?>
</description>
    <dc:creator><?php echo smarty_function_setting(array('name'=>"site_email"),$_smarty_tpl);?>
</dc:creator>

    <dc:rights>Copyright <?php echo smarty_modifier_date_format(time(),"%Y");?>
</dc:rights>
    <admin:generatorAgent rdf:resource="<?php echo smarty_function_setting(array('name'=>"app_link"),$_smarty_tpl);?>
" />

    <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('content')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
?>
    
        <item>

          <title><?php echo $_smarty_tpl->tpl_vars['item']->value['title'];?>
</title>
          <link><?php echo $_smarty_tpl->tpl_vars['item']->value['url'];?>
</link>
          <guid><?php echo $_smarty_tpl->tpl_vars['item']->value['url_path'];?>
</guid>
			
		  <?php if ($_smarty_tpl->getVariable('summary_field')->value){?>
          <description><![CDATA[
     		<?php echo $_smarty_tpl->tpl_vars['item']->value['summary'];?>

     	  ]]></description>
     	  <?php }?>
          <pubDate><?php echo smarty_modifier_date_format($_smarty_tpl->tpl_vars['item']->value['date'],"%a %b %e %H:%M:%S %Z %Y");?>
</pubDate>
        </item>

        
    <?php }} ?>
    
    </channel>
</rss> 

