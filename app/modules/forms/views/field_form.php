<?=$this->load->view(branded_view('cp/header'), array('head_files' => '<script type="text/javascript" src="' . branded_include('js/form.field.js') . '"></script>'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="form_form_field" method="post" action="<?=$form_action;?>">
<? if (!empty($form)) { ?><input type="hidden" name="form_id" value="<?=$form['id'];?>" /><? } ?>
<fieldset>
	<legend>Field Options</legend>
	<ul class="form">
		<?=$this->load->view('cp/field_form.php', array('field' => $field));?>
	</ul>
</fieldset>
<div class="submit">
	<input type="submit" class="button" name="go_field" value="Save Field" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>