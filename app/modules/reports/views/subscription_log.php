<?=$this->load->view(branded_view('cp/header'));?>
<h1>Subscription Log</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><?=date('M d, Y g:ia',strtotime($row['date']));?></td>
			<td>
				<? if (!empty($row['data'])) { ?>
					<a href="#" onclick="javascript:$.modal('<pre><?=trim(htmlspecialchars(json_encode(print_r($row['data'],true))));?>');return false;">
				<? } ?>
				<?=$row['event'];?>
				<? if (!empty($row['data'])) { ?>
					</a>
				<? } ?>
			</td>
			<td><?=$row['ip'];?></td>
			<td><?=$row['browser'];?></td>
			<td><?=$row['file'];?>; line <?=$row['line'];?></td>
		</tr>
	<?
	}
}
else {
?>
<tr><td colspan="5">Empty data set.</td></tr>
<?
}
?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>