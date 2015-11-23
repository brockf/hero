<?=$this->head_assets->javascript('js/form.address.js');?>
<?=$this->head_assets->javascript('js/form.transaction.js');?>

<?=$this->load->view(branded_view('cp/header')); ?>
<h1><?=$form_title;?></h1>
<form class="form validate" enctype="multipart/form-data" id="form_update_cc" method="post" action="<?=$form_action;?>">

<div id="transaction_amount">
	<fieldset>
		<legend>Subscription Details</legend>
		<ul class="form">
			<li>
				<label>Recurring ID #</label>
				<?=$subscription['id'];?>
			</li>
			<li>
				<label>Member</label>
				<a href="<?=site_url('users/profile/' . $subscription['user_id']);?>"><?=$subscription['user_username'];?></a>
			</li>
			<li>
				<label>Amount</label>
				<?=setting('currency_symbol');?><?=$subscription['amount'];?>
			</li>
			<li>
				<label>Interval</label>
				<?=$subscription['interval'];?> days
			</li>
			<li>
				<label>Start Date</label>
				<?=date('d-M-Y',strtotime($subscription['start_date']));?>
			</li>
			<li>
				<label>Last Charge Date</label>
				<?=date('d-M-Y',strtotime($subscription['last_charge_date']));?>
			</li>
			<li>
				<label>Next Charge Date</label>
				<?=date('d-M-Y',strtotime($subscription['next_charge_date']));?>
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
							<option value="<?=$gateway['id'];?>" <? if ($subscription['gateway_id'] == $gateway['id']) {?> selected="selected"<? } ?>><?=$gateway['gateway'];?></option>
						<? } ?>
					</select>
				</li>
				<li>
					<div class="help">This payment gateway will be used to process future payments for this subscription.</div>
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

<input type="hidden" name="subscription_id" value="<?=$subscription['id'];?>" />

<div class="submit">
	<input type="submit" class="button" name="go_update_cc" value="Update Credit Card" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>