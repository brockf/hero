<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Forms</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><a href="<?=site_url('admincp/forms/edit/' . $row['id']);?>"><?=$row['title'];?></a></td>
			<td><a href="<?=$row['admin_link'];?>"><?=$row['num_responses'];?> responses</a></td>
			<td class="options"><a href="<?=site_url('admincp/forms/fields/' . $row['id']);?>">manage fields</a> <a href="<?=site_url('admincp/forms/edit/' . $row['id']);?>">edit</a> <a href="<?=$row['url'];?>">view</a></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="5">No forms have been created.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>