<?=$this->load->view(branded_view('cp/header'));?>
<h1>Subscriptions</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><?=$row['id'];?></td>
			<td><?=$row['user_last_name'];?>, <?=$row['user_first_name'];?></td>
			<td><?=$row['plan']['name'];?></td>
			<td><?=setting('currency_symbol');?><?=$row['amount'];?></td>
			<td><?= date('d-M-Y', strtotime($row['start_date']));?></td>
			<td><? if ($row['next_charge_date']) { ?><?=date('d-M-Y',strtotime($row['next_charge_date']));?><? } ?></td>
			<td><?= date('d-M-Y', strtotime($row['end_date'])); ?></td>
			<td>
				<? if ($row['is_recurring'] == TRUE) { ?>
					Recurring
				<? } elseif ($row['is_renewed'] == TRUE) { ?>
					Renewed (#<?=$row['renewing_subscription_id'];?>)
				<? } elseif ($row['is_updated'] == TRUE) { ?>
					Updated CC (#<?=$row['updating_subscription_id'];?>)
				<? } elseif ($row['is_active'] == TRUE) { ?>
					Will Expire
				<? } elseif ($row['is_active'] == FALSE) { ?>
					Expired
				<? } ?>
			</td>
			<td>
				<input type="hidden" name="action_id" value="<?=$row['id'];?>" />
				<select name="action">
					<option value="0" selected="selected"></option>
					<option value="related_charges">view related charges</option>
					<? if ($row['is_recurring'] == TRUE) { ?>
						<option value="cancel">cancel</option>
						<option value="change_plan">change plan</option>
						<? if ((float)$row['amount'] != 0) { ?><option value="change_price">change recurring amount</option><? } ?>
					<? } ?>
					<option value="profile">view member profile</option
				</select>
				&nbsp;
				<input type="submit" rel="admincp/reports/subscription_actions" class="action button" name="go_action" value="Go" />
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