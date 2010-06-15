<?=$this->load->view(branded_view('cp/header'), array('head_files' => '<script type="text/javascript" src="' . branded_include('js/form.address.js') . '"></script>
<script type="text/javascript" src="' . branded_include('js/form.transaction.js') . '"></script>')); ?>
<h1>New Transaction</h1>
<form class="form" id="form_transaction" method="post" action="<?=site_url('transactions/post');?>">
<? if ($gateways === FALSE) { ?>
<p class="warning no_gateway"><span>You do not have any active gateways available to process this transaction.  The submit button has been disabled.  To
begin processing transactions, you should <a href="<?=site_url('settings/new_gateway');?>">setup a new payment gateway</a>.</span></p>
<? } ?>
<div id="transaction_amount">
	<fieldset>
		<legend>Payment Information</legend>
		<ul class="form">
			<li>
				<label for="amount" class="full">Amount</label>
			</li>
			<li>
				<input type="text" class="text full required number" id="amount" name="amount" />
			</li>
		</ul>
	</fieldset>
</div>
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
<div id="transaction_recur">
	<fieldset>
		<legend>Recurring</legend>
		<ul class="form">
			<li>
				<input type="radio" name="recurring" value="0" checked="checked" /> This transaction does not recur.
			</li>
			<? if (is_array($plans)) { ?>
			<li>
				<input type="radio" name="recurring" value="1" /> Create a recurring transaction by plan.
				<select name="recurring_plan">
				<? foreach ($plans as $plan) { ?>
				<option value="<?=$plan['id'];?>"><?=$plan['name'];?></option>
				<? } ?>
				</select>
			</li>
			<? } else { ?>
			<li>
				<input type="radio" name="recurring" value="0" disabled="disabled" /> Create a recurring transaction by plan.
			</li>
			<? } ?>
			<li>
				<input type="radio" id="specify_recurring" name="recurring" value="2" /> Specify recurring transaction details
			</li>
		</ul>
	</fieldset>
	<div id="recurring_details">
	<fieldset>
		<legend>Recurring Details</legend>
			<ul class="form">
			<li>
				<label for="interval">Charge Interval</label>
				<input type="text" class="text number" id="interval" name="interval" />
			</li>
			<li>
				<div class="help">(Days) Customer will be charged every Interval days.</div>
			</li>
			<li>
				<label for="free_trial">Free Trial</label>
				<input type="text" class="text number" id="free_trial" name="free_trial" />
			</li>
			<li>
				<div class="help">(Days) Wait this many days before making the first charge.</div>
			</li>
			<li>
				<label for="start_date">Start Date</label>
				<select name="start_date_day">
					<? for ($i = 1; $i <= 31; $i++) { ?>
					<option value="<?=$i;?>"<? if (date('j') == $i) { ?> selected="selected" <? } ?>><?=$i;?></option>
					<? } ?>
				</select>&nbsp;&nbsp;
				<select name="start_date_month">
					<? for ($i = 1; $i <= 12; $i++) {
					       $month = str_pad($i, 2, "0", STR_PAD_LEFT);
					       $month_text = date('M',strtotime('2010-' . $month . '-01')); ?>
					<option value="<?=$month;?>"<? if (date('m') == $month) { ?> selected="selected" <? } ?>><?=$month_text;?></option>
					<? } ?>
				</select>
				&nbsp;&nbsp;
				<select name="start_date_year">
					<?
						$now = date('Y');
						$future = $now + 5;
						for ($i = $now; $i <= $future; $i++) {
						?>
					<option value="<?=$i;?>"<? if (date('Y') == $i) { ?> selected="selected" <? } ?>><?=$i;?></option>
						<?
						}
					?>
				</select>
			</li>
			<li>
				<div class="help">Start the recurring charge at this date.  If a free trial is set, it will delay the first charge from this date.</div>
			</li>
			<li>
				<label for="recurring_end">Recur Until</label>
				<input type="radio" id="recurring_end" name="recurring_end" value="forever" checked="checked" /> Forever&nbsp;&nbsp;&nbsp;
				<label for="occurrences" style="display:none">Occurrences</label>
				<input type="radio" id="recurring_end" name="recurring_end" value="occurrences" /> <input type="text" class="text number" id="occurrences" name="occurrences" /> charges
				&nbsp;&nbsp;&nbsp;
				<input type="radio" id="recurring_end" name="recurring_end" value="date" /> Date: 
				<select name="end_date_day">
					<? for ($i = 1; $i <= 31; $i++) { ?>
					<option value="<?=$i;?>"><?=$i;?></option>
					<? } ?>
				</select>&nbsp;&nbsp;
				<select name="end_date_month">
					<? for ($i = 1; $i <= 12; $i++) {
					       $month = str_pad($i, 2, "0", STR_PAD_LEFT);
					       $month_text = date('M',strtotime('2010-' . $month . '-01')); ?>
					<option value="<?=$month;?>"><?=$month_text;?></option>
					<? } ?>
				</select>
				&nbsp;&nbsp;
				<select name="end_date_year">
					<?
						$now = date('Y');
						$future = $now + 5;
						for ($i = $now; $i <= $future; $i++) {
						?>
					<option value="<?=$i;?>"><?=$i;?></option>
						<?
						}
					?>
				</select>
			</li>
		</ul>
	</fieldset>
	</div>
</div>
<div id="transaction_customer">
	<fieldset>
		<legend>Customer Information</legend>
		<ul class="form">
			<li>
				<div class="help">Optional: Enter any customer information to keep more detailed customer records, or link the charge
				to an existing customer.</div>
			</li>
			<li>
				<label for="customer_id">Existing Customer</label>
				<select id="customer_id" name="customer_id">
					<option value=""></option>
					<? if (is_array($customers)) { ?>
					<? foreach ($customers as $customer) { ?>
					<option value="<?=$customer['id'];?>"><?=$customer['last_name'];?>, <?=$customer['first_name'];?><? if (!empty($customer['email'])) { ?> (<?=$customer['email'];?>)<? } ?></option>
					<? } ?>
					<? } ?>
				</select>
			</li>
		</ul>
	</fieldset>
	<div id="transaction_customer_details">
	<fieldset>
		<legend>New Customer Details</legend>
		<ul class="form">
			<li>
				<label for="first_name">Name</label>
				<input class="text mark_empty" rel="First Name" type="text" id="first_name" name="first_name" />&nbsp;&nbsp;<label style="display:none" for="last_name">Last Name</label><input class="text mark_empty" rel="Last Name" type="text" id="last_name" name="last_name" />
			</li>
			<li>
				<label for="company">Company</label>
				<input type="text" class="text" id="company" name="company" />
			</li>
			<li>
				<label for="address_1">Street Address</label>
				<input type="text" class="text" name="address_1" id="address_1" />
			</li>
			<li>
				<label for="address_2">Address Line 2</label>
				<input type="text" class="text" name="address_2" id="address_2" />
			</li>
			<li>
				<label for="city">City</label>
				<input type="text" class="text" name="city" id="city" />
			</li>
			<li>
				<label for="Country">Country</label>
				<select id="country" name="country"><option value=""></option><? foreach ($countries as $country) { ?><option value="<?=$country['iso2'];?>"><?=$country['name'];?></option><? } ?></select>
			</li>
			<li>
				<label for="state">Region</label>
				<input type="text" class="text" name="state" id="state" /><select id="state_select" name="state_select"><option value=""></option><? foreach ($states as $state) { ?><option value="<?=$state['code'];?>"><?=$state['name'];?></option><? } ?></select>
			</li>
			<li>
				<label for="postal_code">Postal Code</label>
				<input type="text" class="text" name="postal_code" id="postal_code" />
			</li>
			<li>
				<label for="phone">Phone</label>
				<input type="text" class="text" id="phone" name="phone" />
			</li>
			<li>
				<label for="email">Email</label>
				<input type="text" class="text email mark_empty" rel="email@example.com" id="email" name="email" />
			</li>
		</ul>
	</fieldset>
	</div>
</div>
<? if (is_array($gateways)) { ?>
<div id="transaction_gateway">
	<fieldset>
		<legend>Gateway</legend>
		<? if (count($gateways) == 1) { ?>
			<p>Transaction will be processed on your <?=$gateways[0]['gateway'];?> gateway.</p>
		<? } else { ?>
			<ul class="form">
				<li>
					<input type="radio" name="gateway_type" value="default" checked="checked" /> Use my default gateway
				</li>
				<li>
					<input type="radio" name="gateway_type" value="specify" /> Select gateway: 
					<select name="gateway">
						<? foreach ($gateways as $gateway) { ?>
							<option value="<?=$gateway['id'];?>" <? if ($this->user->Get('default_gateway_id') == $gateway['id']) {?> selected="selected"<? } ?>><?=$gateway['gateway'];?></option>
						<? } ?>
					</select>
				</li>
			</ul>
		<? } ?>
	</fieldset>
</div>
<? } ?>
<div class="transaction submit">
	<input type="submit" name="go_transation" value="Submit Transaction" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>