<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Gateways</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=$row['gateway'];?></td>
			<td><?=$row['date_created'];?></td>
			<td class="options"><a href="<?=site_url('admincp/billing/edit_gateway/' . $row['id']);?>">edit</a> 
			<? if (setting('default_gateway') == $row['id']) { ?><b>default</b><? } else { ?>
			<a href="<?=site_url('admincp/billing/make_default_gateway/' . $row['id']);?>">make default</a><? } ?></td>
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
<?=$this->load->view(branded_view('cp/footer'));?>