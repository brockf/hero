<?php
$this->load->view(branded_view('cp/header'));

$setting_types = unserialize(setting('search_content_types'));

?>
<h1>Search Configuration</h1>
<form class="form validate" id="form_search" method="post" action="<?=$form_action;?>">
<fieldset>
	<legend>Content Types</legend>
	<p><b>Which content types would you like to include in the content search?</b></p>
	<? if (empty($content_types)) { ?>
		<p>No content types are available.</p>		
	<? } else { ?>
		<ul class="form">
			<? foreach ($content_types as $type) { ?>
				<li style="margin-left: 20px"><input type="checkbox" name="content_type_<?=$type['id'];?>" value="1" <? if (array_key_exists($type['id'],$setting_types)) { ?> checked="checked" <? } ?> /> "<?=$type['name'];?>" - Summary Field for Results: <?=form_dropdown('summary_field_' . $type['id'],$field_options[$type['id']],(isset($setting_types[$type['id']])) ? $setting_types[$type['id']] : '0' ,'id="summary_field" rel="' . $type['id'] . '" class="populate_fields"');?></li>
			<? } ?>
		</ul>
	<? } ?>
	
</fieldset>

<fieldset>
	<legend>Options</legend>
	<ul class="form">
		<? if (module_installed('store')) { ?>
			<li style="margin-left: 20px">
				<input type="checkbox" name="search_products" id="search_products" value="1" <? if (setting('search_products') == '1') { ?>checked="checked"<? } ?> /> <b>Include store products in search results</b>
			</li>
		<? } ?>
		<li style="margin-left: 20px">
			<b>Require the user to wait <input type="text" name="search_delay" class="number required" style="width: 50px" rel="Search Delay" value="<?=setting('search_delay');?>" /> seconds before searching again.</b> (This option is recommended to reduce server strain.)
		</li>
		<li style="margin-left: 20px">
			<b>If displaying a summary for a search result, trim the summary text to <input type="text" name="search_trim" class="number required" style="width: 50px" rel="Summary Trim" value="<?=setting('search_trim');?>" /> characters.</b>
		</li>
	</ul>
</fieldset>

<div class="submit">
	<input type="submit" class="button" name="form_search" value="Save Configuration" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>