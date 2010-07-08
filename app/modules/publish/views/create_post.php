<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="form_create_post" method="post" action="<?=$form_action;?>">
<?=form_hidden('type',$type['id']);?>
<? if (!empty($standard)) { ?>
	<div class="post_standard">
		<?=$standard;?>
	</div>
<? } ?>
<? if (!empty($privileges)) { ?>
	<div class="post_privileges">
		<?=$privileges;?>
	</div>
<? } ?>
<? if (!empty($custom_fields)) { ?>
	<div class="post_custom_fields">
		<?=$custom_fields;?>
	</div>
<? } ?>
<div class="submit">
	<input type="submit" class="button" name="form_create_post" value="Save Post" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>