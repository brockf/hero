<?=$this->load->view(branded_view('cp/header'));?>
<h1>Content Types</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=$row['name'];?></td>
			<td><?=$row['system_name'];?></td>
			<td><? if ($row['is_standard'] == TRUE) { ?>Yes<? } ?></td>
			<td><? if ($row['is_privileged'] == TRUE) { ?>Yes<? } ?></td>
			<td class="options">
				<a href="<?=site_url('admincp/publish/type_edit/' . $row['id']);?>">edit</a> 
				<a href="<?=site_url('admincp/publish/type_fields/' . $row['id']);?>">manage fields</a>
			</td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="7">No content types in this dataset.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>