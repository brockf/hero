<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?=$this->config->item('app_name');?> Installer</title>
	<link href="../branding/default/css/installer.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="../branding/default/js/jquery-1.4.2.js"></script>
	<script type="text/javascript" src="../branding/default/js/universal.js"></script>
	<script type="text/javascript" src="../branding/default/js/form.address.js"></script>
</head>
<body>
	<div id="notices"></div>
	<div id="wrapper">
		<div id="header">
			<div id="logo">
				<?=$this->config->item('app_name');?>
				<span>Installation Wizard</span>
			</div>
		
			<div id="nav">
				<ol>
					<li<? if ($this->router->fetch_method() == 'index') { ?> class="active"<? } ?>>Configuration</li>
					<li<? if ($this->router->fetch_method() == 'admin' and !isset($complete)) { ?> class="active"<? } ?>>Administrator</li>
					<li<? if (isset($complete) and $complete == TRUE) { ?> class="active"<? } ?>>Install Complete</li>
				</ol>
			</div>
		</div>
		