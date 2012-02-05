<?=$this->head_assets->javascript('js/data.user_fields.js');?>

<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage Member Data Fields</h1>
<?=$this->dataset->table_head();?>

<tr>
	<td></td>
	<td>n/a</td>
	<td>First Name</td>
	<td>first_name</td>
	<td>text</td>
	<td><select name="filler" disabled="disabled"><option value="">First Name</option></select></td>
	<td><input type="checkbox" name="filler" disabled="disabled" /></td>
	<td><input type="checkbox" name="filler" disabled="disabled" checked="checked" /></td>
	<td class="options"></td>
</tr>
<tr>
	<td></td>
	<td>n/a</td>
	<td>Last Name</td>
	<td>last_name</td>
	<td>text</td>
	<td><select name="filler" disabled="disabled"><option value="">Last Name</option></select></td>
	<td><input type="checkbox" name="filler" disabled="disabled" /></td>
	<td><input type="checkbox" name="filler" disabled="disabled" checked="checked" /></td>
	<td class="options"></td>
</tr>
<tr>
	<td></td>
	<td>n/a</td>
	<td>Email</td>
	<td>email</td>
	<td>text</td>
	<td><select name="filler" disabled="disabled"><option value="">Email</option></select></td>
	<td><input type="checkbox" name="filler" disabled="disabled" /></td>
	<td><input type="checkbox" name="filler" disabled="disabled" checked="checked" /></td>
	<td class="options"></td>
</tr>
<tr>
	<td></td>
	<td>n/a</td>
	<td>Username</td>
	<td>username</td>
	<td>text</td>
	<td><select name="filler" disabled="disabled"><option value="">No</option></select></td>
	<td><input type="checkbox" name="filler" disabled="disabled" /></td>
	<td><input type="checkbox" name="filler" disabled="disabled" checked="checked" /></td>
	<td class="options"></td>
</tr>
<tr>
	<td></td>
	<td>n/a</td>
	<td>Password</td>
	<td>password</td>
	<td>password</td>
	<td><select name="filler" disabled="disabled"><option value="">No</option></select></td>
	<td><input type="checkbox" name="filler" disabled="disabled" /></td>
	<td><input type="checkbox" name="filler" disabled="disabled" checked="checked" /></td>
	<td class="options"></td>
</tr>

<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr rel="<?=$row['custom_field_id'];?>">
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['custom_field_id'];?></td>
			<td><?=$row['friendly_name'];?></td>
			<td><?=$row['name'];?></td>
			<td><?=$row['type'];?></td>
			<td><? if (module_installed('billing')) { ?>
				<?=form_dropdown('billing_equiv',array(
									'' => 'No',
									'address_1' => 'Address Line 1',
									'address_2' => 'Address Line 2',
									'city' => 'City',
									'state' => 'State/Province',
									'country' => 'Country',
									'postal_code' => 'Postal Code',
									'phone' => 'Phone Number'
								), (isset($row['billing_equiv'])) ? $row['billing_equiv'] : '');?>
				<? } else { ?>
					n/a
				<? } ?>
			</td>
			<td><?=form_checkbox('admin_only', '1', (isset($row['admin_only']) and $row['admin_only'] == TRUE) ? TRUE : FALSE);?></td>
			<td><?=form_checkbox('registration_form', '1', (isset($row['registration_form']) and $row['registration_form'] == FALSE) ? FALSE : TRUE);?></td>
			<td class="options"><a href="<?=site_url('admincp/users/data_edit/' . $row['custom_field_id']);?>">edit</a></td>
		</tr>
	<?
	}
}
?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>