<?=$this->head_assets->javascript('js/form.address.js');?>
<?=$this->head_assets->javascript('js/form.transaction.js');?>

<?=$this->load->view(branded_view('cp/header')); ?>
<h1><?=$form_title;?></h1>
<form class="form validate" enctype="multipart/form-data" id="form_user" method="post" action="<?=$form_action;?>">

<? if ($gateways === FALSE) { ?>
<p class="warning no_gateway"><span>You do not have any active gateways available to process this transaction.  The submit button has been disabled.  To
begin processing transactions, you should <a href="<?=site_url('admincp/billing/new_gateway');?>">setup a new payment gateway</a>.</span></p>
<? } ?>

<div id="transaction_amount">
	<fieldset>
		<legend>Subscription Details</legend>
		<ul class="form">
			<li>
				<label>Subscription Plan</label>
				<?=$subscription['name'];?>
			</li>
			<li id="row_initial_charge">
				<label for="initial_charge">Initial Charge</label><?=setting('currency_symbol');?><input type="text" class="text required number" id="initial_charge" name="initial_charge" value="<?=$subscription['initial_charge'];?>" />
			</li>
			<li id="row_recurring_charge">
				<label for="amount">Recurring Charge</label><?=setting('currency_symbol');?><input type="text" class="text required number" id="amount" name="amount" value="<?=$subscription['amount'];?>" />
			</li>
			<li id="row_free_trial">
				<label for="free_trial">Free Trial</label><input type="text" class="text required number" id="free_trial" name="free_trial" value="<?=$subscription['free_trial'];?>" /> days&nbsp;&nbsp;&nbsp;<input type="checkbox" id="no_free_trial" name="no_free_trial" value="1" <? if ($subscription['free_trial'] == 0) { ?>checked="checked"<? } ?> /> No free trial
			</li>
			<li>
				<div class="help">A subscription can have either a free trial or a unique initial charge.  It cannot have both.  Subscriptions with free trials will charge the recurring amount as the user's first charge. Set the Initial Charge to "0" in order to specify a free trial.</div>
			</li>
			<li>
				<label for="end_date">End Date</label><input type="text" class="text datepick" id="end_date" name="end_date" style="width:110px" />&nbsp;&nbsp;<input type="checkbox" id="no_enddate" name="no_enddate" value="1" checked="checked" />&nbsp;No end date
			</li>
			<li>
				<div class="help">If specified, this subscription will stop recurring on this date.  The member will have to renew their subscription.</div>
			</li>
		</ul>
	</fieldset>
</div>
<? if (is_array($gateways)) { ?>
<div id="transaction_gateway">
	<fieldset>
		<legend>Gateway</legend>
			<ul class="form">
				<li>
					<label>Payment Gateway</label>
					<select name="gateway" id="gateway">
						<? foreach ($gateways as $gateway) { ?>
							<option value="<?=$gateway['id'];?>" class="<? if ($gateway['external'] == TRUE) { ?>external<? } ?><? if ($gateway['billing_address'] == TRUE) { ?>billing_address<? } ?> <? if ($gateway['no_credit_card'] == TRUE) { ?>no_credit_card<? } ?>" <? if (setting('default_gateway') == $gateway['id']) {?> selected="selected"<? } ?>><?=$gateway['gateway'];?></option>
						<? } ?>
					</select>
				</li>
				<li>
					<div class="help">This payment gateway will be used to process this subscription.</div>
				</li>
			</ul>
	</fieldset>
</div>
<? } ?>
<div id="transaction_cc">
	<fieldset>
		<legend>Credit Card Information</legend>
		<ul class="form">
			<li>
				<label for="cc_number" class="full">Credit Card Number</label>
			</li>
			<li>
				<input type="text" class="text full required number" id="cc_number" name="cc_number" />
			</li>
			<li>
				<label for="cc_name" class="full">Credit Card Name</label>
			</li>
			<li>
				<input type="text" class="text full required" id="cc_name" name="cc_name" />
			</li>
			<li>
				<label for="cc_expiry" class="full">Credit Card Expiry</label>
			</li>
			<li>
				<select name="cc_expiry_month">
					<? for ($i = 1; $i <= 12; $i++) {
					       $month = str_pad($i, 2, "0", STR_PAD_LEFT);
					       $month_text = date('M',strtotime('2010-' . $month . '-01')); ?>
					<option value="<?=$month;?>"><?=$month;?> - <?=$month_text;?></option>
					<? } ?>
				</select>
				&nbsp;&nbsp;
				<select name="cc_expiry_year">
					<?
						$now = date('Y');
						$future = $now + 15;
						for ($i = $now; $i <= $future; $i++) {
						?>
					<option value="<?=$i;?>"><?=$i;?></option>
						<?
						}
					?>
				</select>
			</li>
			<li>
				<label for="cc_security" class="full">Credit Card Security Code</label>
			</li>
			<li>
				<input type="text" class="text full number" id="cc_security" name="cc_security" />
			</li>
		</ul>
	</fieldset>
</div>
<div id="transaction_customer">
	<div id="transaction_customer_details">
	<fieldset>
		<legend>Billing Address Details</legend>
		<ul class="form">
			<li>
				<div class="help">Your selected payment gateway requires an accurate and complete customer billing address.  Please complete the fields below.</div>
			</li>
			<li>
				<label for="first_name">Name</label>
				<input class="text mark_empty" rel="First Name" type="text" id="first_name" name="first_name" value="<?=$billing['first_name'];?>" />&nbsp;&nbsp;<label style="display:none" for="last_name">Last Name</label><input class="text mark_empty" rel="Last Name" type="text" id="last_name" name="last_name" value="<?=$billing['last_name'];?>"  />
			</li>
			<li>
				<label for="company">Company</label>
				<input type="text" class="text" id="company" name="company" value="<?=$billing['company'];?>" />
			</li>
			<li>
				<label for="address_1">Street Address</label>
				<input type="text" class="text" name="address_1" id="address_1" value="<?=$billing['address_1'];?>" />
			</li>
			<li>
				<label for="address_2">Address Line 2</label>
				<input type="text" class="text" name="address_2" id="address_2" value="<?=$billing['address_2'];?>" />
			</li>
			<li>
				<label for="city">City</label>
				<input type="text" class="text" name="city" id="city" value="<?=$billing['city'];?>" />
			</li>
			<li>
				<label for="Country">Country</label>
				<select id="country" name="country"><option value=""></option><? foreach ($countries as $country) { ?><option value="<?=$country['iso2'];?>" <? if ($billing['country'] == $country['iso2']) { ?>selected="selected"<? } ?>><?=$country['name'];?></option><? } ?></select>
			</li>
			<li>
				<label for="state">Region</label>
				<input type="text" class="text" name="state" id="state" /><select id="state_select" name="state_select"><option value=""></option><? foreach ($states as $state) { ?><option value="<?=$state['code'];?>" <? if ($billing['state'] == $state['code']) { ?>selected="selected"<? } ?>><?=$state['name'];?></option><? } ?></select>
			</li>
			<li>
				<label for="postal_code">Postal Code</label>
				<input type="text" class="text" name="postal_code" id="postal_code" value="<?=$billing['state'];?>" />
			</li>
		</ul>
	</fieldset>
	</div>
</div>

<input type="hidden" name="plan" value="<?=$subscription['id'];?>" />
<input type="hidden" name="member" value="<?=$member;?>" />

<div class="submit">
	<input type="submit" class="button" name="go_subscription" value="Create subscription" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>