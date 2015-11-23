<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="form_collection" enctype="multipart/form-data" method="post" action="<?=$form_action;?>">
<?=$form;?>
<div class="submit">
	<input type="submit" class="button" name="go_collection" value="Save Collection" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>