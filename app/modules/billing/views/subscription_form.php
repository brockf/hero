<?

/* Default Values */

if (!isset($form)) {
	$form = array(
				'name' => '',
				'type' => 'free',
				'amount' => '0',
				'interval' => '30',
				'notification_url' => 'http://',
				'occurrences' => '0',
				'free_trial' => '0',
				'initial_charge' => '',
				'require_billing_for_trial' => '1',
				'is_taxable' => '1'
				);

} 

?>

<?=$this->head_assets->javascript('js/form.plan.js');?>

<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form" id="form_plan" method="post" action="<?=$form_action;?>">
<? if ($action == 'edit') { ?>
<p class="warning"><span>Editing an existing subscription plan will not affect current subscribers.  Their subscriptions will continue as is
until they expire.</span></p>
<? } ?>
<fieldset>
	<legend>Basic Info</legend>
	<ul class="form">
		<li>
			<label for="name" class="full">Plan Name</label>
		</li>
		<li>
			<input type="text" class="required full" id="name" name="name" value="<?=$form['name'];?>" />
		</li>
	</ul>
</fieldset>
<fieldset>
	<legend>Charge Info</legend>
	<ul class="form">
		<li>
			<label for="amount">Recurring Charge</label>
			<input <? if ($form['amount'] == '0.00' or $form['type'] == 'free') { ?>checked="checked" <? } ?> type="radio" name="plan_type" id="plan_type" value="free" />&nbsp;Free Plan&nbsp;&nbsp;&nbsp;
			<input <? if ($form['amount'] > 0) { ?>checked="checked" <? } ?> type="radio" name="plan_type" id="plan_type" value="paid" />&nbsp;Enter Price:&nbsp;<?=setting('currency_symbol');?><input type="text" class="number" name="amount" id="amount" value="<?=$form['amount'];?>" />
		</li>
		<li>
			<label for="amount">Initial Charge</label>
			<input <? if ($form['initial_charge'] == '' or $form['initial_charge'] == $form['amount']) { ?>checked="checked" <? } ?> type="radio" name="initial_charge_same" id="initial_charge_same" value="1" />&nbsp;Same as recurring charge&nbsp;&nbsp;&nbsp;
			<input <? if ($form['initial_charge'] != '' and $form['initial_charge'] != $form['amount']) { ?>checked="checked" <? } ?> type="radio" name="initial_charge_same" id="initial_charge_same" value="0" />&nbsp;Enter Price:&nbsp;<?=setting('currency_symbol');?><input type="text" class="number" name="initial_charge" id="initial_charge" value="<?=$form['initial_charge'];?>" />
		</li>
		<li>
			<div class="help">Plans with a different Initial Charge (e.g., Setup Fee) cannot have free trials.  This value will be ignored if you specify a free trial.</div>
		</li>
		<li>
			<label>&nbsp;</label>
			<input type="checkbox" id="taxable" name="taxable" value="1" <? if ($form['is_taxable'] == '1') { ?>checked="checked"<? } ?> />&nbsp;<b><a href="<?=site_url('admincp/store/taxes');?>">Tax rules</a> apply to this product</b>
		</li>
		<li>
			<label for="interval">Charge Interval (days)</label>
			<input type="text" class="text required number" name="interval" id="interval" value="<?=$form['interval'];?>" />
		</li>
		<li>
			<div class="help">The customer will be charged every <i>interval</i> days until the subscription expires or is cancelled.</div>
		</li>
		<li>
			<label for="occurrences">Total Occurrences</label>
			<input <? if ($form['occurrences'] == '0') { ?>checked="checked" <? } ?> type="radio" name="occurrences_radio" id="occurrences_radio" value="0" />&nbsp;Infinite&nbsp;&nbsp;&nbsp;
			<input <? if ($form['occurrences'] > '0') { ?>checked="checked" <? } ?> type="radio" name="occurrences_radio" id="occurrences_radio" value="1" />&nbsp;Enter # of Occurrences:&nbsp;<input type="text" class="text number" name="occurrences" id="occurrences" <? if ($form['occurrences'] != '0') { ?>value="<?=$form['occurrences'];?>"<? } ?> />
		</li>
		<li>
			<div class="help">The customer will be charged the specified amount this many times.</div>
		</li>
		<li>
			<label for="free_trial">Free Trial Period (days)</label>
			<input <? if ($form['free_trial'] == '0') { ?>checked="checked" <? } ?> type="radio" name="free_trial_radio" id="free_trial_radio" value="0" />&nbsp;None (charge immediately)&nbsp;&nbsp;&nbsp;
			<input <? if ($form['free_trial'] > '0') { ?>checked="checked" <? } ?>  type="radio" name="free_trial_radio" id="free_trial_radio" value="1" />&nbsp;Enter # of Days:&nbsp;<input type="text" name="free_trial" class="text number" id="free_trial" <? if ($form['free_trial'] != '0') { ?>value="<?=$form['free_trial'];?>"<? } ?> />
		</li>
		<li class="free_trial_options">
			<label for="billing_for_trial">Require Billing Information for Free Trial?</label>
 			<input <? if ($form['require_billing_for_trial'] > '0') { ?>checked="checked" <? } ?>  type="radio" name="require_billing_for_trial" id="require_billing_for_trial" value="1" />&nbsp;Yes, require upon subscription&nbsp;&nbsp;&nbsp;
 			<input <? if ($form['require_billing_for_trial'] == '0') { ?>checked="checked" <? } ?> type="radio" name="require_billing_for_trial" id="require_billing_for_trial" value="0" />&nbsp;No, they can enter it after the trial
		</li>
	</ul>
</fieldset>
<?=$member_form;?>
<div class="submit">
	<input type="submit" class="button" name="go_plan" value="<?=ucfirst($form_title);?>" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>