<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Tax Rules</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=$row['state'];?></td>
			<td><?=$row['country'];?></td>
			<td><?=$row['percentage'];?>%</td>
			<td><?=$row['name'];?></td>
			<td><a href="<?=site_url('admincp/store/tax_edit/' . $row['id']);?>">edit</a></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="7">No tax rules match your filters.  <a href="<?=site_url('admincp/store/tax_add');?>">Add a new tax rule</a>.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>