<?=$this->head_assets->javascript('js/form.send_email.js');?>

<?=$this->load->view(branded_view('cp/header'));?>
<div class="sidebar">
	<h2>Select Recipient(s)</h2>
	<div class="sidebar_content">
		Specify the recipient(s) for this email below.
	</div>
	<h3>Member Groups</h3>
	<div class="sidebar_content">
		<ul class="recipients groups">
			<? foreach ($usergroups as $group) { ?>
			<li>
				<input type="checkbox" name="recipient" class="group" rel="<?=$group['name'];?>" value="<?=$group['id'];?>" /> <?=$group['name'];?>
			</li>
			<? } ?>
		</ul>
	</div>	
	<h3>Members</h3>
	<div class="sidebar_content">
		<form method="post" action="">
			<input type="text" id="member_search" class="mark_empty" autocomplete="off" rel="Search by ID, Username, Name, or Email" name="member_search" style="width:98%" />
		</form>
		<ul class="recipients members">
		
		</ul>
	</div>
</div>

<h1>Send Email</h1>


<div style="float: left; width: 70%;">
		<form class="form validate" method="post" action="<?=site_url('admincp/emails/post_send');?>">
		<ul class="form">
			<li>
				<label for="recipients" class="full">Recipients</label>
			</li>
			<li>
				<ul class="final_recipients">
					<li>Add Recipient Email: <input type="text" placeholder="email@example.com" name="email_address" value="" /> <input type="submit" id="add_email" name="" value="Add" /></li>
					
					<? if (!empty($passed_users)) { ?>
						<li class="empty" style="display:none">No recipients, yet.  Select your recipients in the dialog to the left (or enter an email below).</li>
						<? foreach ($passed_users as $user) { ?>
							<li>Member: <?=$user['name'];?> (<?=$user['email'];?>) (<a rel="<?=$user['name'];?> (<?=$user['email'];?>)" href="#" class="remove member">remove</a>) <input type="hidden" name="recipient_members[]" value="<?=$user['id'];?>" /></li>
						<? } ?>
					<? } else { ?>
						<li class="empty">No recipients, yet.  Select your recipients in the dialog to the left (or enter an email below).</li>	
					<? } ?>
				</ul>
			</li>
		
			<? if (!empty($templates)) { ?>
			
			<li>
				<label class="full" for="templates">Use Template</label>
			</li>
			<li>
				<select name="templates">
					<option value=""></option>
					<? foreach ($templates as $template) { ?>
						<option value="<?=$template['id'];?>"><?=$template['name'];?></option>
					<? } ?>
				</select>
			</li>
				
			<? } ?>
			
			<li>
				<label for="subject" class="full">Subject</label>
			</li>
			<li>
				<input type="text" class="text full required" id="subject" name="subject" value="" />
			</li>
			<li>
				<label for="body" class="full">Body</label>
			</li>
			<li>
				<textarea name="body" id="body" class="full required wysiwyg basic"></textarea>
			</li>
			<li>
				<div class="help" style="margin: 0; padding: 0;">You may use the tags <b>[MEMBER_FIRST_NAME]</b>, <b>[MEMBER_LAST_NAME]</b>, and <b>[MEMBER_EMAIL]</b> in the email body and subject.  They will be replaced with the appropriate values for each member.</div>
			</li>
			<li style="display: none">
				<input type="checkbox" name="html" value="1" checked="checked" /> <b>This email is formatted with HTML tags</b>
			</li>
			<li>
				<input type="checkbox" name="new_template" value="1" /> <b>Save this email as a template</b> <input type="text" class="text" placeholder="Enter Template Name" name="new_template_name" value="" />
			</li>
		</ul>
		<div class="submit">
			<input type="submit" class="submit button" name="" value="Send Email" />
		</div>
		<div class="warning"><span>For emails with member group recipients, message delivery will be staggered so that <?=setting('mail_queue_limit');?> emails will be sent every 5 minutes.  This setting is customizable in Configuration > Settings.</span></div>
	</form>
</div>
<div style="clear:both"></div>

<?=$this->load->view(branded_view('cp/footer'));?>