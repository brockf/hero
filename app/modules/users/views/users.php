<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Members</h1>
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
			<td class="options">
				<input type="hidden" name="action_id" value="<?=$row['id'];?>" />
				<select name="action">
					<option value="0" selected="selected"></option>
					<option value="profile">view profile</option>
					<option value="edit">edit account</option>
					<option value="invoices">invoices</option>
					<option value="subscriptions">subscriptions</option>
					<option value="products">product orders</option>
					<option value="logins">login history</option>
				</select>
				&nbsp;
				<input type="submit" rel="admincp/users/user_actions" class="action button" name="go_action" value="Go" />
			</td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="4">No members match your filters.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>