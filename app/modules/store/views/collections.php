<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Store Collections</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=$row['name'];?></td>
			<td class="options"><a href="<?=site_url('admincp/store/collection_edit/' . $row['id']);?>">edit</a></td>
		</tr>
	<?
	}
} else {
?>
	<tr>
		<td colspan="4">No collections, yet.  <a href="<?=site_url('admincp/store/collection_add');?>">Add a new collection</a>.</td>
	</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>