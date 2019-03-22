<?

/* Default Values */

if (!isset($form)) {
	$form = array(
				'recipients' => array(),
				'other_recipients' => '',
				'parameters' => array(),
				'bccs' => array(),
				'other_bccs' => '',
				'subject' => '',
				'body' => '',
				'is_html' => TRUE
			);

} ?>

<?=$this->head_assets->javascript('js/form.email.js');?>

<?=$this->load->view(branded_view('cp/header'));?>

<div class="sidebar">
	<h2>Email Variables</h2>
	<div class="sidebar_content">
		<b>The following variables are available for use in both the Subject and Body.  You can do way more than just display the data, too!
		All of the power of <a target="_blank" href="http://www.smarty.net/crash_course">Smarty</a> is available here (conditionals, loops, etc.!).</b>
		<ul>
			<? foreach ($variables as $var) { ?>
				<li><?=$var['tag'];?>&nbsp;&nbsp;<span class="light">(<?=$var['type'];?>)</span></li>
			<? } ?>
		</ul>
	</div>
</div>

<div style="float: left; width: 70%">
	<h1><?=$form_title;?></h1>
	<form class="form validate" id="form_email" method="post" action="<?=$form_action;?>">
	<? if (isset($form['id'])) { ?>
		<input type="hidden" name="email_id" value="<?=$form['id'];?>" />
	<? } ?>
	<fieldset>
		<legend>Hook</legend>
		<ul class="form">
			<li>
				<label for="trigger">Hook</label>
				<?=$hook['name'];?>
				<input type="hidden" name="hook" value="<?=$hook['name'];?>" />
			</li>
			<li>
				<div class="help"><?=$hook['description'];?></div>
			</li>
		</ul>
	</fieldset>
	<fieldset>
		<legend>Parameters</legend>
		<ul class="form">
			<li>
				Specify <b>Parameters</b> that must be met for this email to be sent out.
			</li>
			<? if (empty($form['parameters'])) { ?>
				<li class="no_params">
					You have not selected any parameters.			
				</li>
			<? } else { ?>
				<? foreach ($form['parameters'] as $param => $param_value) { ?>
					<li>
					<?
						list($param,$operator) = explode(' ',$param);
						if (empty($operator)) {
							$operator = '==';
						}
						else {
							$operator = trim($operator);
						}
						
						$param = trim($param);
					?>
						<select name="param[]" class="param">
							<? foreach ($hook['email_data'] as $param2) { ?>
								<option <? if ($param2 == $param) { ?> selected="selected" <? } ?> value="<?=$param2;?>"><?=str_replace('_',' ',ucfirst($param2));?></option>
							<? } reset($hook['email_data']); ?>
						</select>
						&nbsp;&nbsp;
						<select class="operator" name="operator[]">
							<option <? if ($operator == '==') { ?> selected="selected" <? } ?> value="==">equals</option>
							<option <? if ($operator == '!=') { ?> selected="selected" <? } ?> value="!=">does not equal</option>
						</select>
						&nbsp;
						&nbsp;
						<? if ($param == 'product') { ?>
							<select class="value" name="param_value[]">
								<? foreach ($products as $product) { ?>
									<option <? if ($product == $param_value) { ?> selected="selected" <? } ?> value="<?=$product['id'];?>"><?=$product['name'];?></option>
								<? } reset($products); ?>
							</select>
						<? } elseif ($param == 'plan') { ?>
							<select class="value" name="param_value[]">
								<? foreach ($plans as $plan) { ?>
									<option <? if ($plan == $param_value) { ?> selected="selected" <? } ?> value="<?=$plan['id'];?>"><?=$plan['name'];?></option>
								<? } reset($products); ?>
							</select>
						<? } else { ?>
							<input type="text" class="text value" name="param_value[]" value="<?=$param_value;?>" />
						<? } ?>
						&nbsp;&nbsp;(<a href="#" class="delete_param">remove</a>)
						</li>
				<? } ?>
			<? } ?>
			<li>
				<input type="button" class="button" id="add_param" value="Add Parameter" />
			</li>
		</ul>
		<select style="display:none" id="operator_options">
			<option value="==">equals</option>
			<option value="!=">does not equal</option>
		</select>
		<select style="display:none" id="parameter_options">
			<option value=""></option>
			<? foreach ($hook['email_data'] as $param) { ?>
				<option value="<?=$param;?>"><?=str_replace('_',' ',ucfirst($param));?></option>
			<? } ?>
		</select>
		<select style="display:none" id="product_options">
			<? foreach ($products as $product) { ?>
				<option value="<?=$product['id'];?>"><?=$product['name'];?></option>
			<? } ?>
		</select>
		<select style="display:none" id="plan_options">
			<? foreach ($plans as $plan) { ?>
				<option value="<?=$plan['id'];?>"><?=$plan['name'];?></option>
			<? } ?>
		</select>
	</fieldset>
	<fieldset>
		<legend>Recipients</legend>
		<ul class="form">
			<li>
				<label>To:</label>
				<? if (is_array($hook['email_data']) and in_array('member', $hook['email_data'])) { ?><input type="checkbox" name="to_member" value="1" <? if (in_array('member', $form['recipients'])) { ?> checked="checked" <? } ?> /> Member<? } ?>
				<input type="checkbox" name="to_admin" value="1" <? if (in_array('admin', $form['recipients'])) { ?> checked="checked" <? } ?> /> Administrator
				&nbsp;&nbsp;&nbsp;Others: <input type="text" style="width: 450px" name="to_others" class="text mark_empty" rel="e.g., tom@example.com, bill@otherdomain.com" value="<?=$form['other_recipients'];?>" />
			</li>
			<li>
				<div class="help">
					Select the recipients for this email.  Besides either the member or the site administrator, you can also enter other email addresses separated by a comma.
				</div>
			</li>
			<li>
				<label>BCC:</label>
				<? if (is_array($hook['email_data']) and in_array('member', $hook['email_data'])) { ?><input type="checkbox" name="bcc_member" value="1" <? if (in_array('member', $form['bccs'])) { ?> checked="checked" <? } ?> /> Member<? } ?>
				<input type="checkbox" name="bcc_admin" value="1" <? if (in_array('member', $form['bccs'])) { ?> checked="checked" <? } ?> /> Administrator
				&nbsp;&nbsp;&nbsp;Others: <input type="text" style="width: 450px" name="bcc_others" class="text mark_empty" rel="e.g., tom@example.com, bill@otherdomain.com" value="<?=$form['other_bccs'];?>" />
			</li>
			<li>
				<div class="help">
					Select (BCC) recipients for this email, same format as above.
				</div>
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
				<input type="text" class="text full required" id="subject" name="subject" value="<?=$form['subject'];?>" />
			</li>
			<li>
				<input type="checkbox" name="is_html" value="1" <? if ($form['is_html'] == TRUE) { ?> checked="checked" <? } ?> /> Send the email in HTML format
			</li>
			<li>
				<label for="email_body" class="full">Email Body</label>
			</li>
			<li>
				<textarea class="full required" id="body" name="body"><?=$form['body'];?></textarea>
			</li>
		</ul>
	</fieldset>
	<div class="submit">
		<input type="submit" class="button" name="go_email" value="<?=ucfirst($form_title);?>" />
	</div>
	</form>
</div>

<div style="clear:both"></div>

<?=$this->load->view(branded_view('cp/footer'));?>