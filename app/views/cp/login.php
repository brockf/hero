<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Login</title>
	<link href="<?=branded_include('css/universal.css');?>" rel="stylesheet" type="text/css" media="screen" />
	<link href="<?=branded_include('css/login.css');?>" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="<?=branded_include('js/jquery-1.4.2.js');?>"></script>
	<script type="text/javascript" src="<?=branded_include('js/universal.js');?>"></script>
</head>
<body>
	<div id="notices"><?=get_notices();?></div>
	<div id="login_form">
		<h1>Control Panel</h1>
		<form method="post" action="<?=site_url('/admincp/login/go');?>">
			<ul>
				<li>
					<label for="username">Username</label>
					<input id="username" name="username" type="text" />
					<div style="clear:both"></div>
				</li>
				<li>
					<label for="password">Password</label>
					<input id="password" name="password" type="password" />
					<div style="clear:both"></div>
				</li>
				<li class="submit">
					<input type="submit" name="login_button" value="Login" />
				</li>
			</ul>
		</form>
	</div>
</body>
</html>