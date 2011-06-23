<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Modules</h1>
<?=$this->dataset->table_head();?>

<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><?=$row['name'];?></td>
			<td>
				<? if ($row['installed'] == TRUE) { ?>installed<? } ?>
				<? if ($row['ignored'] == TRUE) { ?>not installed<? } ?>
			</td>
			<td><?=$row['version'];?></td>
			<td class="options">
				<? if ($row['installed'] == TRUE) { ?><a href="<?=site_url('admincp/modules/uninstall/' . $row['id']);?>">uninstall</a><? } ?>
				<? if ($row['ignored'] == TRUE) { ?><a href="<?=site_url('admincp/modules/install/' . $row['id']);?>">install</a><? } ?>
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
<?=$this->load->view(branded_view('cp/footer'));?>