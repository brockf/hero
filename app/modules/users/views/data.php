<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Member Data Fields</h1>
<?=$this->dataset->table_head();?>

<tr>
	<td></td>
	<td>n/a</td>
	<td>First Name</td>
	<td>first_name</td>
	<td>text</td>
	<td class="options"></td>
</tr>
<tr>
	<td></td>
	<td>n/a</td>
	<td>Last Name</td>
	<td>last_name</td>
	<td>text</td>
	<td class="options"></td>
</tr>
<tr>
	<td></td>
	<td>n/a</td>
	<td>Email</td>
	<td>email</td>
	<td>text</td>
	<td class="options"></td>
</tr>
<tr>
	<td></td>
	<td>n/a</td>
	<td>Username</td>
	<td>username</td>
	<td>text</td>
	<td class="options"></td>
</tr>
<tr>
	<td></td>
	<td>n/a</td>
	<td>Password</td>
	<td>password</td>
	<td>password</td>
	<td class="options"></td>
</tr>

<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><?=$row['friendly_name'];?></td>
			<td><?=$row['name'];?></td>
			<td><?=$row['type'];?></td>
			<td class="options"><a href="<?=site_url('admincp/users/data_edit/' . $row['id']);?>">edit</a></td>
		</tr>
	<?
	}
}
?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>