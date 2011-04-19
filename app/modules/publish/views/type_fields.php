<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$type['name'];?>: Manage Fields</h1>
<?=$this->dataset->table_head();?>
<? if ($type['is_standard'] == TRUE) { ?>
	<tr>
		<td></td>
		<td>n/a</td>
		<td>Title</td>
		<td>title</td>
		<td>text (standard page field)</td>
		<td class="options"></td>
	</tr>
	<tr>
		<td></td>
		<td>n/a</td>
		<td>URL Path</td>
		<td>url_path</td>
		<td>text (standard page field)</td>
		<td class="options"></td>
	</tr>
	<tr>
		<td></td>
		<td>n/a</td>
		<td>Topic</td>
		<td>topic</td>
		<td>dropdown (standard page field)</td>
		<td class="options"></td>
	</tr>
<? } ?>
<? if ($type['is_privileged'] == TRUE) { ?>
	<tr>
		<td></td>
		<td>n/a</td>
		<td>Restricted to Member Group(s)</td>
		<td>member_group_access</td>
		<td>dropdown</td>
		<td class="options"></td>
	</tr>
<? } ?>
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
			<td class="options"><a href="<?=site_url('admincp/publish/type_field_edit/' . $type['id'] . '/' . $row['id']);?>">edit</a></td>
		</tr>
	<?
	}
} else {
?>
	<tr>
		<td colspan="6">No content type fields yet.  <a href="<?=site_url('admincp/publish/type_field_add/' . $type['id']);?>">Add a new field</a>.</td>
	</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>