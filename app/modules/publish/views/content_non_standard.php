<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage <?=$type['name'];?></h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<? foreach ($columns as $column) { ?>
				<td><?=$row[$column];?></td>
			<? } ?>
			<? reset($columns); ?>
			<td><?=$row['date'];?></td>
			<td><?=$row['hits'];?></td>
			<td class="options"><a href="<?=site_url('admincp/publish/edit/' . $row['id']);?>">edit</a><? if ($row['is_standard'] == TRUE) { ?> <a href="<?=$row['url'];?>">view</a><? } ?></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="6">No content available.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>