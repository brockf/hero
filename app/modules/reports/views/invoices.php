<?=$this->load->view(branded_view('cp/header'));?>
<h1>Invoices</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><?=$row['id'];?></td>
			<td><?=$row['user_last_name'];?>, <?=$row['user_first_name'];?></td>
			<td>
				<? if ($row['refunded'] == TRUE) { ?>
					<strike><?=$this->config->item('currency_symbol');?><?=$row['amount'];?></strike> (refunded)
				<? } else { ?>
					<?=$this->config->item('currency_symbol');?><?=$row['amount'];?>
				<? } ?>
			</td>
			<td><?=$row['date'];?></td>
			<td><?=$row['gateway'];?></td>
			<td><? if (!empty($row['subscription_id'])) { ?><?=$row['subscription_id'];?><? } ?></td>
			<td>
				<input type="hidden" name="action_id" value="<?=$row['id'];?>" />
				<select name="action">
					<option value="0" selected="selected"></option>
					<option value="details">view full invoice</option>
					<? if ($row['refunded'] != TRUE) { ?>
						<option value="refund">refund payment</option>
					<? } ?>
					<option value="profile">view member profile</option
					<? if ($row['subscription_id'] != FALSE) { ?>
						<option value="related_charges">view related subscription charges</option
					<? } ?>
				</select>
				&nbsp;
				<input type="submit" rel="admincp/reports/invoice_actions" class="action button" name="go_action" value="Go" />
			</td>
		</tr>
	<?
	}
}
else {
?>
<tr><td colspan="7">Empty data set.</td></tr>
<?
}	
?>
<?=$this->dataset->table_close();?>

<div class="total">
	<h2>Total Amount</h2>
	<p><?=$this->config->item('currency_symbol');?><?=money_format("%!^i",$total_amount);?></p>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>