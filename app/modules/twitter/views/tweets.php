<?=$this->load->view(branded_view('cp/header'));?>
<h1>Tweet Records</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><?=$row['tweet_id'];?></td>
			<td><?=$row['sent_time'];?></td>
			<td><?=$row['tweet'];?></td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="6">No tweets match your filters.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>