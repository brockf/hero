<?=$this->load->view(branded_view('cp/header'));?>
<form class="form validate" id="form_create_post" enctype="multipart/form-data" method="post" action="<?=$form_action;?>">
<?=form_hidden('type',$type['id']);?>

<? if (!empty($standard) or (!empty($privileges))) { ?>

<div class="sidebar">
	<h2>Publish Options</h2>
	
	<div class="sidebar_content">
	<? if (!empty($standard)) { ?>
		<div class="post_standard">
			<fieldset>
				<ul class="form">
					<?=$standard;?>	
				</ul>
			</fieldset>
		</div>
	<? } ?>
	</div>
	
	<? if (!empty($privileges)) { ?>
	<h3>Access Restrictions</h3>
	
	<div class="sidebar_content">
	<div class="post_privileges">
		<fieldset>
			<ul class="form">
				<?=$privileges;?>
			</ul>
		</fieldset>
	</div>
	</div>
<? } ?>
</div>

<? } ?>

<h1><?=$type['singular_name'];?> Publisher</h1>

<? if (!empty($standard) or (!empty($privileges))) { ?>
	<div style="float: left; width: 70%;">
<? } ?>

<? if ($invalid === TRUE) { ?>
	<p class="warning"><span>This content did not pass validation (errors below).  However, <b>it is posted live</b>!  Please correct these errors
	as soon as possible: <div style="font-weight: bold"><?=$errors;?></span></div></p>
<? } ?>

	<? if (!empty($standard)) { ?>
		<div class="post_title">
			<?=$title;?>
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
	
<? if (!empty($standard) or (!empty($privileges))) { ?>
	</div>
	<div style="clear:both"></div>
<? } ?>

<?=$this->load->view(branded_view('cp/footer'));?>