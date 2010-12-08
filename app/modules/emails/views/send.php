<?=$this->load->view(branded_view('cp/header'), array('head_files' => '<script type="text/javascript" src="' . branded_include('js/form.send_email.js') . '"></script>'));?>
<h1>Send Email</h1>

<div class="sidebar">
	<h2>Select Recipient(s)</h2>
	<div class="sidebar_content">
		Specify the recipient(s) for this email below.
		<h3>Member Groups</h3>
		<ul class="recipients groups">
			<? foreach ($usergroups as $group) { ?>
			<li>
				<input type="checkbox" name="recipient" class="group" rel="<?=$group['name'];?>" value="<?=$group['id'];?>" /> <?=$group['name'];?>
			</li>
			<? } ?>
		</ul>
		
		<h3>Members</h3>
		<form method="post" action="">
			<input type="text" id="member_search" class="mark_empty" autocomplete="off" rel="Search by ID, Username, Name, or Email" name="member_search" style="width:98%" />
		</form>
		<ul class="recipients members">
		
		</ul>
	</div>
</div>

<div style="float: left; width: 70%;">
	<form class="form validate" method="post" action="<?=site_url('admincp/emails/post_send');?>">
		<ul class="form">
			<li>
				<label for="recipients" class="full">Recipients</label>
			</li>
			<li>
				<ul class="final_recipients">
					<li class="empty">No recipients, yet.  Select your recipients in the dialog to the left.</li>
				</ul>
			</li>
			<li>
				<label for="subject" class="full">Subject</label>
			</li>
			<li>
				<input type="text" class="text full required" name="subject" value="" />
			</li>
			<li>
				<label for="body" class="full">Body</label>
			</li>
			<li>
				<textarea name="body" class="full required"></textarea>
			</li>
			<li>
				<div class="help" style="margin: 0; padding: 0;">You may use the tags <b>[MEMBER_FIRST_NAME]</b>, <b>[MEMBER_LAST_NAME]</b>, and <b>[MEMBER_EMAIL]</b> in the email body and subject.  They will be replaced with the appropriate values for each member.</div>
			</li>
			<li>
				<label>Send as HTML mail?</label> <input type="checkbox" name="html" value="1" />
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