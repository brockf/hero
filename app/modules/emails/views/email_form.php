<?

/* Default Values */

if (!isset($form)) {
	$form = array(
				'trigger' => '',
				'to_address' => 'customer',
				'bcc_address' => '',
				'email_subject' => '',
				'email_body' => '',
				'plan' => '',
				'is_html' => '0'
			);

} ?>
<?=$this->load->view(branded_view('cp/header'), array('head_files' => '<script type="text/javascript" src="' . branded_include('js/form.email.js') . '"></script>'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="form_email" method="post" action="<?=site_url($form_action);?>">
<fieldset>
	<legend>System Information</legend>
	<ul class="form">
		<li>
			<label for="trigger">Trigger</label>
			<select id="trigger" class="required" name="trigger">
				<option <? if ($form['trigger'] == '') { ?> selected="selected" <? } ?> value=""></option>
				<? foreach ($triggers as $trigger) { ?>
				<option <? if ($form['trigger'] == $trigger['system_name']) {?> selected="selected" <? } ?> value="<?=$trigger['email_trigger_id'];?>"><?=$trigger['human_name'];?></option>
				<? } ?>
			</select>
		</li>
		<li>
			<div class="help">This system action will trigger this email.</div>
		</li>
		<li>
			<label for="plan">Subscription Link</label>
			<select id="plan" name="plan">
				<option <? if ($form['plan'] == '0') { ?> selected="selected" <? } ?>  value="">All plans</option>
				<? foreach ($plans as $plan) { ?>
				<option  <? if ($form['plan'] == $plan['id']) { ?> selected="selected" <? } ?> value="<?=$plan['id'];?>">Plan: <?=$plan['name'];?></option>
				<? } ?>
			</select>
		</li>
		<li>
			<div class="help">(If applicable - Optional) Only send when the action relates to the subscription plan(s) above.</div>
		</li>
	</ul>
</fieldset>
<fieldset>
	<legend>Send To</legend>
	<ul class="form">
		<li>
			<label for="to_address_email">Send to</label>
			<input <? if ($form['to_address'] == 'customer') { ?>checked="checked" <? } ?>type="radio" class="required" id="to_address" name="to_address" value="user" />&nbsp;User&nbsp;&nbsp;&nbsp;
			<input <? if ($form['to_address'] != 'customer') { ?>checked="checked" <? } ?>type="radio" class="required" id="to_address" name="to_address" value="email" />&nbsp;<input type="text" class="text email mark_empty" rel="email@example.com" id="to_address_email" name="to_address_email" <? if ($form['to_address'] != 'customer' and $form['to_address'] != '') { ?> value="<?=$form['to_address'];?>" <? } ?> />
		</li>
		<li>
			<label for="bcc_address_email">BCC</label>
			<input <? if ($form['bcc_address'] == '') { ?>checked="checked" <? } ?>type="radio" id="bcc_address" name="bcc_address" value="" />&nbsp;None&nbsp;&nbsp;&nbsp;
			<input <? if ($form['bcc_address'] == 'client') { ?>checked="checked" <? } ?>type="radio" id="bcc_address" name="bcc_address" value="site_email" />&nbsp;<?=setting('site_email');?>&nbsp;&nbsp;&nbsp;
			<input <? if ($form['bcc_address'] != 'client' and $form['bcc_address'] != '') { ?>checked="checked" <? } ?>type="radio" id="bcc_address" name="bcc_address" value="email" />&nbsp;<input type="text" class="text email mark_empty" rel="email@example.com" id="bcc_address_email" name="bcc_address_email" <? if ($form['bcc_address'] != 'client' and $form['bcc_address'] != '') { ?> value="<?=$form['bcc_address'];?>" <? } ?> />
		</li>
	</ul>
</fieldset>
<fieldset>
	<legend>Email</legend>
	<ul class="form">
		<li>
			<label for="email_subject" class="full">Email Subject</label>
		</li>
		<li>
			<input type="text" class="text full required" id="email_subject" name="email_subject" value="<?=$form['email_subject'];?>" />
		</li>
		<li>
			<label for="email_body" class="full">Email Body</label><? if ($form['is_html'] == '0') { ?> <a href="#" id="make_html">use HTML format</a><? } ?>
			<input type="hidden" name="is_html" id="is_html" value="<?=$form['is_html'];?>" autocomplete="off" />
		</li>
		<li>
			<textarea class="full required" id="email_body" name="email_body"><?=$form['email_body'];?></textarea>
		</li>
		<li>
			<div id="email_variables">
			</div>
		</li>
	</ul>
</fieldset>
<div class="submit">
	<input type="submit" name="go_email" value="<?=ucfirst($form_title);?>" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>