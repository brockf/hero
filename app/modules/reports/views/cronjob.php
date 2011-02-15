<?=$this->load->view(branded_view('cp/header'));?>
<h1>System Cronjob</h1>

<? if (setting('cron_last_update') == FALSE) { ?>
	<p class="warning"><span>Your cronjob appears to have never been run!  This is not good - we need this script to run <i>at least</i> once per
	day to take care of all the automated tasks involving subscriptions, like auto-charging subscriptions.  <b>You must
	configure the cronjob below to run <i>at least</i> once per day, or ask your system administrator to do the same.</b></span></p>
<? } elseif ((time() - strtotime(setting('cron_last_update'))) > (60*60*24)) { ?>
	<p class="warning"><span>Your cronjob appears to have been run over one day ago!  This is not good - we need this script to run <i>at least</i> once per
	day to take care of all the automated tasks involving subscriptions, like auto-charging subscriptions.  <b>You must
	configure the cronjob below to run <i>at least</i> once per day, or ask your system administrator to do the same.</b></span></p>
<? } else { ?>
	<p><b>Everything looks good with your cronjob!  It's running at least once per day.  No attention is needed, here.</b></p>
<? } ?>

<p><b>Cronjob Last Run:</b> <? if (setting('cron_last_update') == FALSE) { ?>Never<? } else { ?><?=date('M d, Y h:i:a', strtotime(setting('cron_last_update')));?><? } ?></p>

<p><b>Cronjob Command for *nix Servers:</b> */5 * 	* 	* 	* wget -q -O /dev/null <?=site_url('cron/update/' . setting('cron_key'));?> > /dev/null 2>&1</p>

<div class="help" style="margin: 0; padding: 0;">This command executes the following script every 5 minutes.  However, the cronjob actions themselves
won't unnecessarily repeat themselves.  For example, subscription maintenance will only occur once per day.</div>

<?=$this->load->view(branded_view('cp/footer'));?>