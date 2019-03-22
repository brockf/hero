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
			<td><?=$row['submission_date'];?></td>
			<td><? if (!empty($row['user_id'])) { ?><a href="<?=site_url('admincp/users/profile/' . $row['user_id']);?>"><?=$row['member_username'];?></a><? } else { ?>n/a<? } ?></td>
			<td class="options"><a href="<?=site_url('admincp/forms/response/' . $row['form_id'] . '/' . $row['id']);?>">view</a></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="5">There are no submissions to display.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>