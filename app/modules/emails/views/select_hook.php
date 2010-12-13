<?=$this->load->view(branded_view('cp/header'));?>
<h1>Select Hook for Email</h1>
<p>Emails are sent out upon specific system actions, or "hooks".  Select the action that you want to trigger this email, below.</p>

<form method="get" class="form validate" action="<?=$form_action;?>">
	<fieldset>
		<legend>Hooks</legend>
		<ul class="form">
			<? foreach ($hooks as $hook) { ?>
				<li>
					<input type="radio" class="required" rel="Hook" name="hook" value="<?=$hook['name'];?>" /> <b><?=$hook['name'];?></b> - <span class="help"><?=$hook['description'];?></span>
				</li>
			<? } ?>
		</ul>
		<div class="submit">
			<input type="submit" class="submit button" name="" value="Continue Creating Email" />
		</div>
	</fieldset>
</form>

<?=$this->load->view(branded_view('cp/footer'));?>