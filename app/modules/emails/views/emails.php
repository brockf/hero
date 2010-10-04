<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Emails</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=$row['hook'];?></td>
			<td><?=$row['parameters_string'];?></td>
			<td><?=implode(', ', $row['recipients']);?></td>
			<td><?=$row['subject'];?></td>
			<td><? if ($row['is_html'] == TRUE) { ?>HTML<? } else { ?>plaintext<? } ?></td>
			<td class="options"><a href="<?=site_url('admincp/emails/edit_email/' . $row['id']);?>">edit</a></td>
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