<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Modules</h1>

<h2 class="cat">System Modules</h2>
<?=$this->dataset->table_head();?>

<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td>
				<? if ($row['installed'] == TRUE) { ?><b><?=$row['name'];?></b><? } else { ?>
				<?=$row['name'];?><? } ?>
			</td>
			<td>
				<? if ($row['installed'] == TRUE) { ?>installed<? } ?>
				<? if ($row['ignored'] == TRUE) { ?>not installed<? } ?>
			</td>
			<td><?=$row['version'];?></td>
			<td class="options">
				<? if ($row['installed'] == TRUE and !in_array($row['name'], $core_modules)) { ?><a href="<?=site_url('admincp/settings/module_uninstall/' . $row['name']);?>">uninstall</a>
				<? } elseif ($row['installed'] == TRUE and in_array($row['name'], $core_modules)) { ?> core module - no uninstall
				<? } elseif ($row['ignored'] == TRUE) { ?><a href="<?=site_url('admincp/settings/module_install/' . $row['name']);?>">install</a><? } ?>
			</td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="4">No modules are installed.  What?  How?.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>

<h2 class="cat">Module Settings</h2>

<form class="form validate" method="post" action="<?=site_url('admincp/settings/modules_settings');?>">
	<fieldset>
		<legend>Settings</legend>
		<ul class="form">
			<li>
				<label for="auto_install">Auto-Install Modules?</label>
				<input type="checkbox" id="auto_install" name="auto_install" value="1" <? if ($this->config->item('modules_auto_install') == '1') { ?> checked="checked" <? } ?> /> Install new modules automatically as soon as they are dropped into <i>/app/modules/</i>.
			</li>
		</ul>
		<div class="submit" style="margin-left: 150px">
			<input type="submit" name="" value="Save" class="button" />
		</div>
	</fieldset>
</form>

<?=$this->load->view(branded_view('cp/footer'));?>