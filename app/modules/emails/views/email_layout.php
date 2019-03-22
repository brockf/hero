<?=$this->load->view(branded_view('cp/header'));?>
<h1>Edit Global Email Layout</h1>
<form class="form validate" id="form_email_layout" method="post" action="<?=$form_action;?>">
<?=$form;?>
<div class="submit">
	<input type="submit" class="button" name="go_email_layout" value="Save Changes to File" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>