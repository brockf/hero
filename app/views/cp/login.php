<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Login</title>
	<link href="<?=branded_include('css/universal.css');?>" rel="stylesheet" type="text/css" media="screen" />
	<link href="<?=branded_include('css/login.css');?>" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="<?=branded_include('js/jquery-1.4.2.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/tiptip.min.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/universal.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/form.js');?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			$('#username').focus();
		});
	</script>
</head>
<body>
	<div id="notices"><?=get_notices();?></div>
	<div id="login_form">
		<h1><span class="app_name"><?=setting('site_name');?></span> | Control Panel</h1>
		<form class="validate" method="post" action="<?=site_url('/admincp/login/go');?>">
			<ul class="form">
				<li>
					<label for="username">Username</label>
					<input id="username" name="username" type="text" class="text required" />
					<div style="clear:both"></div>
				</li>
				<li>
					<label for="password">Password</label>
					<input id="password" name="password" type="password" class="text required" />
					<div style="clear:both"></div>
				</li>
				<li class="submit">
					<input type="submit" class="button" name="login_button" class="button" value="Login" />
				</li>
			</ul>
		</form>
		<div class="links">
			<ul class="links">
				<li><a href="<?=site_url();?>">Visit site</a></li>
				<li><a href="<?=site_url('users/forgot_password');?>">Reset your password</a></li>
				<li><a href="<?=setting('app_support');?>"><?=setting('app_name');?> Support</a></li>
			</ul>
		</div>
	</div>
	<div id="copyright">
		Powered by <a href="<?=setting('app_link');?>"><?=setting('app_name');?></a>.
	</div>
</body>
</html>