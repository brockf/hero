<?=$this->load->view(branded_view('cp/header')); ?>
<h1>Setup New Gateway</h1>
<form class="form" id="form_email" method="post" action="<?=site_url('admincp/billing/new_gateway_details');?>">
<fieldset>
	<legend>Select your gateway</legend>
	<label for="external_api" style="display:none">Gateway Type</label>
	<? foreach ($gateways as $gateway) { ?>
	<div class="gateway_listing">
		<h2><input type="radio" class="required" name="external_api" id="external_api" value="<?=$gateway['class_name'];?>" />&nbsp;<?=$gateway['name'];?> <a class="purchase" href="<?=$gateway['purchase_link'];?>">Apply for an account now.</a></h2>
		<p class="description"><?=$gateway['description'];?></p>
		<div class="monthly_fee"><?=$gateway['monthly_fee'];?><h3>Monthly Fee</h3></div>
		<div class="setup_fee"><?=$gateway['setup_fee'];?><h3>Setup Fee</h3></div>
		<div class="transaction_fee"><?=$gateway['transaction_fee'];?><h3>Transaction Fee</h3></div>
		<div style="clear:both"></div>
	</div>
	<? } ?>
</fieldset>

<div class="submit">
	<input type="submit" class="button" name="go_gateway" value="Continue setting up gateway" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>