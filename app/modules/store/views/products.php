<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Products</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=admin_link($row['url']);?></td>
			<td><? if ($row['is_download'] == TRUE) { ?><img src="<?=branded_include('images/download.png');?>" alt="Downloadable Product" /><? } ?><? if ($row['is_download'] == TRUE and $row['requires_shipping'] == TRUE) { ?>&nbsp;<? } ?><? if ($row['requires_shipping'] == TRUE) { ?><img src="<?=branded_include('images/shippable.png');?>" alt="Shippable Product" /><? } ?></td>
			<td><a href="<?=$row['admin_link'];?>"><?=$row['name'];?></a></td>
			<td><?=setting('currency_symbol');?><?=$row['price'];?></td>
			<td><?=$row['inventory'];?></td>
			<td>
			<?php 
				if (is_array($row['collections']))
				{
					$str = '';
				
					foreach ($row['collections'] as $cid)
					{
						if (isset($collections[$cid])) {
							$str .= $collections[$cid] .', ';
						}
					}
					
					echo trim($str, ', ');
				}
			?>
			</td>
			<td class="options">
				<input type="hidden" name="action_id" value="<?=$row['id'];?>" />
				<select name="action">
					<option value="0" selected="selected"></option>
					<option value="details">view product details</option>
					<option value="edit">edit product</option>
					<option value="images">manage product images</option>
					<option value="view_orders">report: view all orders</option>
				</select>
				&nbsp;
				<input type="button" class="button action" rel="admincp/store/product_actions" name="go_action" value="Go" />
			</td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="9">No products match your filters.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>