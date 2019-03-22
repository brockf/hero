<?=$this->load->view(branded_view('cp/header'));?>
<h1>Login Records</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><?=$row['id'];?></td>
			<td><a href="<?=site_url('admincp/users/profile/' . $row['user_id']);?>"><?=$row['username'];?></a></td>
			<td><? foreach ($row['usergroups'] as $group) { ?><?=$usergroups[$group];?><br /><? } ?></td>
			<td><?=$row['date'];?></td>
			<td><?=$row['ip'];?></td>
			<td><?=$row['browser'];?></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="6">No member logins match your filters.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>