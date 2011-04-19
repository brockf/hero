<?=$this->load->view(branded_view('cp/header'));?>

<h1>Themes</h1>

<ul class="themes">
	<? foreach ($themes as $theme) { ?>
		<li>
			<div class="image">
				<img src="<?=$this->theme_model->preview_image($theme);?>" alt="<?=$theme;?>" />
			</div>
			<div class="info">
				<h4><?=$theme;?></h4>
				<? if ($this->config->item('theme') == $theme) { ?>
					<p class="already">Currently installed</p>
					<form method="get" action="<?=$this->theme_model->install_url($theme);?>">
					<input type="submit" class="button" name="" value="Re-install Theme" />
					</form>
				<? } else { ?>
					<form method="get" action="<?=$this->theme_model->install_url($theme);?>">
					<input type="submit" class="button" name="" value="Install Theme" />
					</form>
				<? } ?>
			</div>
		</li>
	<? } ?>
</ul>

<div style="clear:both"></div>

<?=$this->load->view(branded_view('cp/footer'));?>