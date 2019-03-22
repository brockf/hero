<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form['title'];?>: Manage Fields</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=$row['friendly_name'];?></td>
			<td><?=$row['name'];?></td>
			<td><?=$row['type'];?></td>
			<td class="options"><a href="<?=site_url('admincp/forms/field_edit/' . $form['id'] . '/' . $row['id']);?>">edit</a></td>
		</tr>
	<?
	}
} else {
?>
	<tr>
		<td colspan="6">No form fields yet.  <a href="<?=site_url('admincp/forms/field_add/' . $form['id']);?>">Add a new field</a>.</td>
	</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>