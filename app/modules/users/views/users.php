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
			
			<?
			
			foreach ($configuration as $field) {
				$field_key = $field;
				$field = $list_options[$field];
			
				if ($field_key == 'username') {
					$value = '<a href="' . $row['admin_link'] . '">' . $row['username'] . '</a>';
				}
				elseif ($field_key == 'email') {
					$value = '<a href="' . $row['admin_link'] . '">' . $row['email'] . '</a>';
				}
				elseif ($field_key == 'full_name') {
					$value = '<a href="' . $row['admin_link'] . '">' . $row['last_name'] . ', ' . $row['first_name'] . '</a>';
				}
				elseif ($field_key == 'groups') {
					$groups = array();
					foreach ($row['usergroups'] as $group) {
						$groups[] = $usergroups[$group];
					}
					
					$value = implode(', ', $groups);
				}
				elseif (@is_array(unserialize($row[$field['filter']]))) {
					$value = implode(', ', unserialize($row[$field['filter']]));
				}
				elseif (isset($field['options'])) {
					// we have a set of options
					$value = isset($field['options'][$row[$field['filter']]]) ? $field['options'][$row[$field['filter']]] : '';
				}
				else {
					$value = $row[$field['filter']];
				}
				
				echo '<td>' . $value . '</td>';
			}
			
			?>
			 
			<td class="options">
				<input type="hidden" name="action_id" value="<?=$row['id'];?>" />
				<select name="action">
					<option value="0" selected="selected"></option>
					<option value="profile">view profile</option>
					<option value="edit">edit profile</option>
					<option value="add_subscription">add subscription</option>
					<option value="invoices">invoices</option>
					<option value="subscriptions">subscriptions</option>
					<option value="products">product orders</option>
					<option value="logins">login history</option>
					<? if (!empty($row['validate_key'])) { ?>
						<option value="validate_email">resend validation email</option>
					<? } ?>
					<? if ($row['suspended'] == TRUE) { ?>
						<option value="unsuspend">unsuspend user</option>
					<? } else { ?>
						<option value="suspend">suspend user</option>
					<? } ?>
					<option value="login_to_account">login to account</option>
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
	<td colspan="8">No members match your filters.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>