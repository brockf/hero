<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<p class="warning"><span>Is your gateway in test mode?  Even when "Test mode" is specified below, your transactions can
still be processed as live, real transactions if your gateway is not in test mode.  You must set your gateway
to test mode in your gateway control panel.  "Test mode" below only indicates which of your gateway's servers to use.</span></p>
<form class="form validate" id="form_plan" method="post" action="<?=$form_action;?>">
<input type="hidden" name="external_api" value="<?=$external_api;?>" />
<fieldset>
	<legend>Gateway Settings</legend>
	<ul class="form">
	<li>
		<label for="alias">Gateway Alias</label>
		<input type="text" class="text" name="alias" value="<?=$name;?>" />
	</li>
	<? foreach ($fields as $name => $field) { ?>
	<? if (!isset($values[$name])) { $values[$name] = ''; } ?>
		<li>
		<label for="<?=$name;?>"><?=$field['text'];?></label>
		<? if ($field['type'] == 'text') { ?>
			<input type="text" class="text required" name="<?=$name;?>" id="<?=$name;?>" value="<?=$values[$name];?>" />
		<? } elseif ($field['type'] == 'password') { ?>
			<input type="password" class="text required" name="<?=$name;?>" id="<?=$name;?>" value="<?=$values[$name];?>" />
		<? } elseif ($field['type'] == 'radio') { ?>
			<? foreach ($field['options'] as $value => $display) { ?>
				<input type="radio" id="<?=$name;?>" name="<?=$name;?>" class="required" value="<?=$value;?>" <? if ($values[$name] == $value) { ?>checked="checked"<? } ?> />&nbsp;<?=$display;?>&nbsp;&nbsp;&nbsp;
			<? } ?>
		<? } elseif ($field['type'] == 'select') { ?>
			<select id="<?=$name;?>" name="<?=$name;?>" class="required">
			<? foreach ($field['options'] as $value => $display) { ?>
				<option value="<?=$value;?>" <? if ($values[$name] == $value) { ?>selected="selected"<? } ?>><?=$display;?></option>
			<? } ?>
			</select>
		<? } ?>
		</li>
	<? } ?>
</fieldset>
<div class="submit">
	<input type="submit" class="button" name="go_gateway" value="Save Gateway" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>