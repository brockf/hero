<?=$this->load->view(branded_view('install/header'));?>
<h1>Configuration</h1>
<p><strong>Welcome to <?=$this->config->item('app_name');?>!</strong></p>
<p>Installation takes just a few moments.</p>
<? if (!empty($file_permission_errors)) { ?>
	<? foreach ($file_permission_errors as $error) { ?>
		<p class="error"><strong><?= $error['file']; ?> must be writable by the web server</strong> - You must set the
		<? if ($error['folder'] == TRUE) { ?>folder<? } else { ?>file<? } ?> permissions
		with CHMOD (0666, 0755, or 0777) and, possibly, file ownership with a CHOWN command.</strong></p>
	<? } ?>
	<p><a href="">Click here to refresh after making permissions changes</a></p>
<? } else { ?>
<p>Before continuing, please create and take note of a MySQL database user account and empty MySQL database.  Electric Publisher
will automatically create the necessary tables and data but you must ensure that your MySQL account has the proper permissions
and that this database exists.</p>
<form class="form" method="post" action="">
	<fieldset>
		<legend>Site Information</legend>
		<? if ($error_empty_site == TRUE) { ?>
			<p class="error">These are required fields and must not be empty.</p>
		<? } ?>
		<? if ($error_base_url == TRUE) { ?>
			<p class="error">Your Base Server URL must include "http://" (it will be rewritten as "https://" where necessary)
			and be the domain path to your <?=$this->config->item('app_name');?> installation.</p>
		<? } ?>
		<ol>
			<li>
				<label for="base_url">Base Server URL</label>
				<input type="text" name="base_url" id="base_url" class="text required" value="<?=$domain;?>" />
			</li>
			<li>
				<label for="site_name">Site Name</label>
				<input type="text" name="site_name" id="site_name" class="text required" value="<?=$site_name;?>" />
			</li>
			<li>
				<label for="site_email">Site Email</label>
				<input type="text" name="site_email" id="site_email" class="text required" value="<?=$site_email;?>" />
			</li>
		</ol>
		<input type="hidden" name="cron_key" id="cron_key" class="text required" value="<?=$cron_key;?>" />
		<input type="hidden" name="encryption_key" id="encryption_key" class="text required" value="<?=$encryption_key;?>" />
	</fieldset>
	<fieldset>
		<legend>MySQL Database</legend>
		<? if ($error_mysql == TRUE) { ?>
			<p class="error">Your MySQL connection information is invalid.  Please verify your user credentials, access privileges,
			and database name.</p>
		<? } ?>
		<ol>
			<li>
				<label for="db_host">Database Host</label>
				<input type="text" name="db_host" id="db_host" class="text required" value="<?=$db_host;?>" />
			</li>
			<li>
				<label for="db_user">Database Username</label>
				<input type="text" name="db_user" id="db_user" class="text required mark_empty" rel="Your MySQL Database Username" value="<?=$db_user;?>" />
			</li>
			<li>
				<label for="db_pass">Database Password</label>
				<input type="password" name="db_pass" id="db_pass" class="text required mark_empty" rel="Your MySQL Database Password" value="<?=$db_pass;?>" />
			</li>
			<li>
				<label for="db_name">Database Name</label>
				<input type="text" name="db_name" id="db_name" class="text required mark_empty" rel="The name of your empty MySQL database" value="<?=$db_name;?>" />
			</li>
		</ol>
	</fieldset>
	<div class="submit"><input type="submit" class="button" name="continue" id="continue" value="Save Configuration" /></div>
</form>
<? } ?>
<?=$this->load->view(branded_view('install/footer'));?>