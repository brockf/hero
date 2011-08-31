<?=$this->load->view(branded_view('cp/header'));?>
<h1>Coupons</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><a href="<?=site_url('admincp/coupons/edit/' . $row['id']);?>"><?=$row['name'];?></a></td>
			<td><?=$row['code'];?></td>
			<td><?=$row['subscription_usages'];?></td>
			<td><?=$row['order_usages'];?></td>
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