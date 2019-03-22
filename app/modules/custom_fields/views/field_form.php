<?=$this->head_assets->javascript('js/form.field.js');?>

<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="go_field" method="post" action="<?=$form_action;?>">
<input type="hidden" name="area" value="<?=$area;?>" />
<input type="hidden" name="custom_field_group_id" value="<?=$custom_field_group_id;?>" />
<input type="hidden" name="table" value="<?=$table;?>" />

<? if (isset($field)) { ?>
	<input type="hidden" name="id" value="<?=$field['id'];?>" id="field_id" ?>
<? } ?>

<fieldset>
	<legend>Basic Details</legend>
	<ul class="form">
		<li class="full">
			<label for="name" class="full">Name</label>
		</li>
		<li class="full">
			<input type="text" class="text full required" name="name" id="name" value="<? if (isset($field)) { ?><?=$field['friendly_name'];?><? } ?>" />
		</li>
		<li class="full">
			<div class="help" style="margin-left: 0px">Give the full name of the field (e.g, "School Name", "CV Upload").</div>
		</li>
		<li>
			<label for="type" class="full">Fieldtype</label>
		</li>
		<li>
			<?=form_dropdown('type', $fieldtypes, (isset($field)) ? $field['type'] : FALSE, 'id="type"');?>
		</li>
	</ul>
</fieldset>
<fieldset id="field_options">
	<ul class="form">
		<li>Select a Fieldtype above for more customizable options.</li>
	</ul>
</fieldset>
<div class="submit">
	<input type="submit" class="button" name="go_field" value="Save Custom Field" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>