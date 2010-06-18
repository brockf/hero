<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?=setting('site_name');?></title>
	<link href="<?=branded_include('css/universal.css');?>" rel="stylesheet" type="text/css" media="screen" />
	<link href="<?=branded_include('css/datepicker.css');?>" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="<?=branded_include('js/jquery-1.4.2.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/date.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/datePicker.js');?>"></script>
	<link type="text/css" rel="stylesheet" href="<?=branded_include('js/jwysiwyg/jquery.wysiwyg.css');?>" />
	<script type="text/javascript" src="<?=branded_include('js/jwysiwyg/jquery.wysiwyg.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/universal.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/form.js');?>"></script>
	<? if (isset($head_files)) { ?><?=$head_files;?><? } ?>
</head>
<body>
	<div id="notices"><?=get_notices();?></div>
	<div id="header">
		<div id="app_bar">
			<span class="app_name"><?=setting('site_name');?></span> | Control Panel
			<div id="logged_in">
				Logged in as <span class="username">brockf</span>
			</div>
			<a id="get_support" href="<?=setting('app_support');?>">Get Support</a>
		</div>
		<div id="navigation">
			<ul>
				<?=$this->navigation->display();?>
			</ul>
			<div style="clear: both"></div>
		</div>
		<div id="nav_children">
		
		</div>
		<div style="clear: both"></div>
	</div>
	<div id="wrapper">
		<div id="content">
			<div id="box-top-right"></div>
			<div id="box-bottom-left"></div>
			<div id="box-bottom-right"></div>
			<div id="box-content">
			<?=$this->navigation->get_module_links();?>