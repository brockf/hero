<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Shipping Rates</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=$row['name'];?></td>
			<td><?=$row['state'];?></td>
			<td><?=$row['country'];?></td>
			<td>
				<? if ($row['type'] == 'weight') { ?><?=setting('currency_symbol');?><?=$row['rate'];?> per <?=setting('weight_unit');?><? } ?>
				<? if ($row['type'] == 'product') { ?><?=setting('currency_symbol');?><?=$row['rate'];?> per product<? } ?>
				<? if ($row['type'] == 'flat') { ?><?=setting('currency_symbol');?><?=$row['rate'];?> flat fee<? } ?>
			</td>
			<td><a href="<?=site_url('admincp/store/shipping_edit/' . $row['id']);?>">edit</a></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="7">No shipping rates match your filters.  <a href="<?=site_url('admincp/store/shipping_add');?>">Add a new shipping rate</a>.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>