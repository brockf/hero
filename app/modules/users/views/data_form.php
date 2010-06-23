<?=$this->load->view(branded_view('cp/header'), array('head_files' => '<script type="text/javascript" src="' . branded_include('js/form.field.js') . '"></script>'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="form_user_field" method="post" action="<?=$form_action;?>">
<fieldset>
	<legend>Field Options</legend>
	<ul class="form">
		<?=$this->load->view('cp/field_form.php', array('field' => $field));?>
	</ul>
</fieldset>
<fieldset>
	<legend>Options</legend>
	<ul class="form">
		<li>
			<label for="billing_equiv" style="width:100%; text-align: left;">Should this field be used to auto-populate a billing address field?</label>
		</li>
		<li>
			<?=form_dropdown('billing_equiv',array(
									'' => 'No, this is not an address field.',
									'address_1' => 'Address Line 1',
									'address_2' => 'Address Line 2',
									'city' => 'City',
									'state' => 'State/Province',
									'country' => 'Country',
									'postal_code' => 'Postal Code'
								), (isset($field['billing_equiv'])) ? $field['billing_equiv'] : '');?>
		</li>
	</ul>
</fieldset>
<div class="submit">
	<input type="submit" class="button" name="go_field" value="Save Field" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>