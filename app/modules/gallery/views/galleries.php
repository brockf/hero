<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Galleries</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><a href="<?=site_url('admincp/gallery/edit/' . $row['id']);?>"><?=$row['title'];?></a></td>
			<td><?=$row['author_username'];?></td>
			<td><?=$row['date'];?></td>
			<td><?=$row['hits'];?></td>
			<td class="options">
				<a href="<?=site_url('admincp/gallery/edit/' . $row['id']);?>">edit</a>
				<a href="<?=site_url('admincp/gallery/images/' . $row['id']);?>">manage images</a>
				<a href="<?=$row['url'];?>">view</a>
			</td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="7">No galleries available.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>