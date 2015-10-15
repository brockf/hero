<?=$this->load->view(branded_view('cp/header'));?>
<h1>Subscription Plans</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=$row['name'];?></td>
			<td><?=setting('currency_symbol');?><?=$row['amount'];?></td>
			<td><?=$row['interval'];?> days</td>
			<td><? if (!empty($row['free_trial'])) { ?><?=$row['free_trial'];?> days<? } else { ?>none<? } ?></td>
			<td><?=$row['active_subscribers'];?></td>
			<td><? if (!empty($row['promotion'])) { ?><?=$usergroups[$row['promotion']];?><? } ?></td>
			<td><? if (!empty($row['demotion'])) { ?><?=$usergroups[$row['demotion']];?><? } ?></td>
			<td class="options"><a href="<?=site_url('admincp/billing/subscription_edit/' . $row['id']);?>">edit</a></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="10">No subscription plans in this dataset.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>