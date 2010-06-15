<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>
	<link href="<?=branded_include('css/universal.css');?>" rel="stylesheet" type="text/css" media="screen" />
	<link href="<?=branded_include('css/datepicker.css');?>" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="<?=branded_include('js/jquery-1.4.2.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/date.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/datePicker.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/universal.js');?>"></script>
	<? if (isset($head_files)) { ?><?=$head_files;?><? } ?>
</head>
<body>
	<div id="notices"><?=get_notices();?></div>
	<div id="header">
		<div id="logo">&nbsp;</div>
		<ul id="topnav">
			
		</ul>
		<div id="account">
			
		</div>
		<div style="clear: both"></div>
	</div>
	<div id="wrapper">
		<div id="sidebar">
			
		</div>
		<div id="content">
			<div id="box-top-right"></div>
			<div id="box-bottom-left"></div>
			<div id="box-bottom-right"></div>
			<div id="box-content">