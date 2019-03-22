<?=$this->load->view(branded_view('cp/header'));?>
<h1>Member Groups</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><? if ($row['default'] == TRUE) { ?><b><? } ?><?=$row['name'];?><? if ($row['default'] == TRUE) { ?></b> (default for new registrations)<? } ?></td>
			<td class="options"><a href="<?=site_url('admincp/users/group_edit/' . $row['id']);?>">edit</a><? if ($row['default'] != TRUE) {?>&nbsp;&nbsp;<a href="<?=site_url('admincp/users/group_default/' . $row['id']);?>">make default</a><? } ?></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="9">No member groups available.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>