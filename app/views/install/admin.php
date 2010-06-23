<?=$this->load->view(branded_view('install/header'));?>
<h1>Administrator Account</h1>
<p>Your MySQL database and configuration files have been setup.</p>
<p>We will now create the primary administrator account for your <?=$this->config->item('app_name');?> server.</p>
<form class="form" method="post" action="">
	<fieldset>
		<legend>Admin Information</legend>
		<ol>
			<li>
				<label for="first_name">Name</label>
				<input class="text required mark_empty" rel="First Name" type="text" id="first_name" name="first_name" value="<?=$first_name;?>" />&nbsp;&nbsp;<label style="display:none" for="last_name">Last Name</label><input class="text required mark_empty" rel="Last Name" type="text" id="last_name" name="last_name" value="<?=$last_name;?>" />
			</li>
			<li>
				<label for="email" class="full">Email Address</label>
			</li>
			<li>
				<input type="text" autocomplete="off" class="text required full email mark_empty" rel="email@example.com" id="email" name="email" value="<?=$email;?>" />
			</li>
			<li>
				<label for="username" class="full">Username</label>
			</li>
			<li>
				<input type="text" autocomplete="off" class="text required full mark_empty" rel="select a username" id="username" name="username" value="<?=$username;?>" />
			</li>
			<? if (!empty($error_password)) { ?>
			<li>
				<p class="error"><?=$error_password;?></p>
			</li>
			<? } ?>
			<li>
				<label for="password" class="full">Password</label>
			</li>
			<li>
				<input type="password" autocomplete="off" class="text required full" id="password" name="password" value="" />
			</li>	
			<li>
				<label for="password2" class="full">Repeat Password</label>
			</li>
			<li>
				<input type="password" autocomplete="off" class="text required full" id="password2" name="password2" value="" />
			</li>
			<li>
				<div class="help" style="margin-left:0px">Passwords must be at least 6 characters in length.</div>
			</li>
			<li>
				<label for="timezone">Timezone</label>
				<?=timezone_menu($gmt_offset);?>
			</li>
		</ol>
	</fieldset>
	<div class="submit"><input type="submit" class="button" name="continue" id="continue" value="Create Account" /></div>
</form>
<?=$this->load->view(branded_view('install/footer'));?>