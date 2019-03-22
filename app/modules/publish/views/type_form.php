<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" enctype="multipart/form-data" id="form_type" method="post" action="<?=$form_action;?>">

<?=$form;?>

<div class="submit">
	<? if ($action == 'new') { ?>
	<input type="submit" class="button" name="go_type" value="Continue to creating input fields for this type" />
	<? } else { ?>
	<input type="submit" class="button" name="go_type" value="Save Content Type" />
	<? } ?>
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>