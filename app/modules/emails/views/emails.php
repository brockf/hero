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
			<td><?=$row['trigger'];?></td>
			<td><?=$row['to_address'];?></td>
			<td><?=$row['email_subject'];?></td>
			<td><? if ($row['is_html'] == 0) { ?>plaintext<? } else { ?>HTML<? } ?></td>
			<td><? if (isset($plans[$row['plan']])) { ?><?=$plans[$row['plan']];?><? } ?></td>
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