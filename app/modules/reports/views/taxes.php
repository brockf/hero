<?=$this->load->view(branded_view('cp/header'));?>
<h1>Taxes Received</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><?=$row['invoice_id'];?></td>
			<td><?=$row['user_last_name'];?>, <?=$row['user_first_name'];?></td>
			<td><?=$row['tax_name'];?></td>
			<td><?=$row['tax_rate'];?>%</td>
			<td><?=setting('currency_symbol');?><?=$row['amount'];?></td>
			<td><?=date('d-M-Y', strtotime($row['date']));?></td>
			<td>
				<input type="hidden" name="action_id" value="<?=$row['id'];?>" />
				<select name="action">
					<option value="0" selected="selected"></option>
					<option value="invoice">view full invoice</option>
					<option value="profile">view member profile</option
				</select>
				&nbsp;
				<input type="submit" rel="admincp/reports/tax_actions" class="action button" name="go_action" value="Go" />
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

<div class="total">
	<h2>Total Taxes Received</h2>
	<p><?=$this->config->item('currency_symbol');?><?=money_format("%!^i",$total_amount);?></p>
</div>

<?=$this->load->view(branded_view('cp/footer'));?>