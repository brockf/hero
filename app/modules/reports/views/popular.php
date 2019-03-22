<?=$this->load->view(branded_view('cp/header'));?>
<h1>Popular Content</h1>
<?=$this->dataset->table_head();?>
<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><?=$row['id'];?></td>
			<td><a href="<?=$row['url'];?>"><?=$row['title'];?></a></td>
			<td><?=$row['type_name'];?></td>
			<td><?=$row['hits'];?></td>
			<td><?=date('d-M-Y',strtotime($row['date']));?></td>
			<td>
				<input type="hidden" name="action_id" value="<?=$row['id'];?>" />
				<select name="action">
					<option value="0" selected="selected"></option>
					<option value="edit">edit content</option>
					<option value="view">view content</option>
				</select>
				&nbsp;
				<input type="submit" rel="admincp/reports/content_actions" class="action button" name="go_action" value="Go" />
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
<?=$this->load->view(branded_view('cp/footer'));?>