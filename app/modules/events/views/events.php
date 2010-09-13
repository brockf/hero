<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Events</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><a href="<?=site_url('admincp/events/edit/' . $row['id']);?>"><?=$row['title'];?></a></td>
			<td><?=$row['location'];?></td>
			<td><?=$row['price'];?></td>
			<td class="options"><a href="<?=site_url('admincp/events/edit/' . $row['id']);?>">edit</a></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="5">No events available.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>