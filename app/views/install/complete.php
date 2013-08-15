<?php echo $this->load->view(branded_view('install/header'), array('complete' => TRUE));?>
<h1>Install Complete!</h1>
<p class="error"><b>Do not refresh this page!  Now that <?php echo $this->config->item('app_name');?> is installed, the installer will be completely disabled.</b></p>
<p><strong>Congratulations!  You have successfully uploaded and configured <?php echo $this->config->item('app_name');?>.</strong></p>
<p>Important instructions, credentials and links will follow.</p>
<h2>Setup your cronjobs</h2>
<p>Automated processes like emails and recurring charges require the daily execution of two cronjobs.  Please use
a crontab manager (either in your cPanel/Plesk control panel or via SSH) to setup the following crontabs, exactly as so:</p>
<ul>
	<li>*/5 * 	* 	* 	* wget -q -O /dev/null <?php echo site_url('cron/update/' . $cron_key);?> > /dev/null 2>&1</li>
</ul>
<h2>Control Panel</h2>
<ul>
	<li>Your control panel is accessible at: <a href="<?php echo $cp_link;?>"><strong><?php echo $cp_link;?></strong></a></li>
</ul>
<h2>Your Account Credentials</h2>
<p>You can login to the control panel, and throughout the site, with:</p>
<ul>
	<li>Username: <strong><?php echo $username;?></strong> or <strong><?php echo $email;?></strong></li>
	<li>Password: <strong><?php echo $password;?></strong></li>
</ul>
<p><a href="<?php echo $cp_link;?>">Login to your Control Panel now to setup gateways, client accounts, emails, and more</a>.</p>
<?php echo $this->load->view(branded_view('install/footer'));?>