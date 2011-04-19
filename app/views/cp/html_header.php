<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?=setting('site_name');?> | Control Panel</title>
	<base href="<?=site_url();?>" />
	
	<link href="<?=branded_include('css/universal.css');?>" rel="stylesheet" type="text/css" media="screen" />
	
	<script type="text/javascript" src="<?=branded_include('js/jquery-1.4.2.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/jquery.simplemodal.1.4.min.js');?>"></script>
	
	<? /* date picker must be loaded here, so that it's methods are available in universal.js/form.js */ ?>
	<? if (defined('INCLUDE_DATEPICKER')) { ?>
	<link href="<?=branded_include('css/datepicker.css');?>" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="<?=branded_include('js/date.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/datePicker.js');?>"></script>
	<? } ?>
	
	<? /* load essential javascript routines */ ?>
	<script type="text/javascript" src="<?=branded_include('js/tiptip.min.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/universal.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/form.js');?>"></script>
	
	<? /* previously, this was an ugly $head_files print.  now we use a class. */ ?>
	<?=$this->head_assets->display();?>
	
	<? /* ckeditor must be loaded here because it's acting on classes we defined in universal.js above */ ?>
	<? if (defined("INCLUDE_CKEDITOR")) { ?>
		<script type="text/javascript" src="<?=branded_include('js/ckeditor/ckeditor.js');?>"></script> 
		<script type="text/javascript" src="<?=branded_include('js/ckeditor/adapters/jquery.js');?>"></script>
	<? } ?>
</head>