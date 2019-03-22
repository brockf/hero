<?=$this->load->view(branded_view('cp/header'));?>
<h1>Registrations</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><?=$row['id'];?></td>
			<td><?=$row['first_name'];?></td>
			<td><?=$row['last_name'];?></td>
			<td><?=$row['email'];?></td>
			<td><?=date('d-M-Y', strtotime($row['signup_date']));?></td>
			<td>
				<? foreach ($row['usergroups'] as $group) { ?><?=$usergroups[$group];?><br /><? } ?>
			</td>
			<td>
				<input type="hidden" name="action_id" value="<?=$row['id'];?>" />
				<select name="action">
					<option value="0" selected="selected"></option>
					<option value="invoices">view invoices</option>
					<option value="subscriptions">view subscriptions</option>
					<option value="profile">view profile</option
				</select>
				&nbsp;
				<input type="submit" rel="admincp/reports/user_actions" class="action button" name="go_action" value="Go" />
			</td>
		</tr>
	<?
	}
}
else {
?>
<tr><td colspan="8">Empty data set.</td></tr>
<?
}	
?>
<?=$this->dataset->table_close();?>

<?=$this->load->view(branded_view('cp/footer'));?>