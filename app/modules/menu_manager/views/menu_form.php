<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="form_menu" method="post" action="<?=$form_action;?>">
<?=$form;?>
<div class="submit">
	<input type="submit" class="button" name="form_menu" value="Save Menu" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>