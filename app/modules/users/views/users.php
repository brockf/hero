<?=$this->load->view(branded_view('cp/header'));?>
<h1>Member Search</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><a href="<?=$row['admin_link'];?>"><?=$row['username'];?></a></td>
			<td><?=$row['email'];?></td>
			<td><?=$row['last_name'];?>, <?=$row['first_name'];?></td>
			<td><? foreach ($row['usergroups'] as $group) { ?><?=$usergroups[$group];?><br /><? } ?></td>
			<td><? if ($row['suspended'] == '1') { ?>Suspended<? } else { ?>Active<? } ?></td>
			<td><?=$row['last_login'];?></td>
			<td class="options"><a href="<?=site_url('admincp/users/edit/' . $row['id']);?>">edit</a></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="9">No members match your filters.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>