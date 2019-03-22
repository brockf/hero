<?=$this->load->view(branded_view('cp/header'));?>

<h1>Install Theme: <?=$theme;?></h1>

<p>You are about to install the <b><?=$theme;?></b> theme.</p>

<form class="form" method="post" action="<?=site_url('admincp/theme/complete_install');?>">
<input type="hidden" name="theme" value="<?=$theme;?>">

<? if ($install_file == TRUE) { ?>
<ul class="form">
	<li>
		<h2>Default Content</h2>
	</li>
	<li>
		<b>Would you like create this theme's default (placeholder) content?</b>
		<ul class="list">
			<li>&bull; Content, content types, forms, blogs, topics, RSS feeds, products, collections, product options, subscriptions, and menus may be created.</li>
			<li>&bull; Installing default content can break your site if your site is not a fresh install of <?=$this->config->item('app_name');?></li>
			<li>&bull; If you choose not to install default content, the theme may required some template modifications before it works.</li>
		</ul>
	</li>
	<li>
		<input type="radio" name="default_content" value="yes" <? if (setting('theme') == FALSE) { ?> checked="checked" <? } ?> /> Yes, install default content.<br />
		<input type="radio" name="default_content" value="no" <? if (setting('theme') != FALSE) { ?> checked="checked" <? } ?> /> No, do not install default content.
	</li>
	<li>
		<h2 style="margin-top: 15px">System Reset</h2>
	</li>
	<li>
		<b>Would you like to reset your <?=$this->config->item('app_name');?> back to the state of a "fresh install"?</b>  If you are loading default content above, it is highly suggested.
		<ul class="list">
			<li>&bull; Content, content types, forms, blogs, topics, RSS feeds, products, collections, product options, subscriptions, and menus WILL be erased.</li>
			<li>&bull; Members/administrators, and other configurations, will NOT be erased.</li>
			<li>&bull; If you choose not to reset the platform, the theme may required some template modifications before it works.</li>
		</ul>
	</li>
	<li>
		<input type="radio" name="reset" value="yes" <? if (setting('theme') == FALSE) { ?> checked="checked" <? } ?> /> Yes, reset my platform.  This includes deleting all content, content types,
		custom fields, products, collections, forms, blogs &amp; archives, topics, and menus.<br />
		<input type="radio" name="reset" value="no" <? if (setting('theme') != FALSE) { ?> checked="checked" <? } ?> /> No, do not perform a system reset.
	</li>
</ul>
<? } ?>

<div class="submit">
	<input type="submit" class="button" onclick="javascript:return confirm('Are you sure you want to install this theme?');" value="Install Theme Now" />
</div>
</form>

<?=$this->load->view(branded_view('cp/footer'));?>