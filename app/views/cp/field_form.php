<?

/* Default Values */

$field['friendly_name'] = (isset($field['friendly_name'])) ? $field['friendly_name'] : '';
$field['type'] = (isset($field['type'])) ? $field['type'] : 'text';

if (isset($field['options'])) {
	$options = '';
	foreach ($field['options'] as $option) {
		if ($option['value'] != $option['name']) {
			$options .= $option['name'] . '=' . $option['value'] . "\n";
		}
		else {
			$options .= $option['name'] . "\n";
		}
	}
}
else {
	$options = '';
}

$field['options'] = $options;
$field['required'] = (isset($field['required']) and $field['required'] == '1') ? TRUE : FALSE;
$field['validators'] = (isset($field['validators'])) ? $field['validators'] : array();
$field['help'] = (isset($field['help'])) ? $field['help'] : '';

?>
<li>
	<label for="friendly_name" class="full">Field Name</label>
</li>
<li>
	<input type="text" class="text full required" name="name" value="<?=$field['friendly_name'];?>" />
</li>
<li>
	<div class="help" style="margin-left:0">This name will appear to end users and throughout the control panel, e.g., "My Custom Field".</div>
</li>
<li>
	<label for="help" class="full">Help Text</label>
</li>
<li>
	<textarea name="help" class="text full" id="help" style="height: 65px"><?=$field['help'];?></textarea>
</li>
<li>
	<div class="help" style="margin-left:0">Help text is displayed below the field to give users assistance in entering the proper data.</div>
</li>
<li>
	<label for="type" class="full">Type</label>
</li>
<li>
	<?=form_dropdown('type', array('text' => 'Text', 'textarea' => 'Textarea', 'select' => 'Select Dropdown', 'radio' => 'Radio', 'checkbox' => 'Checkbox'), $field['type'], 'id="type"');?>
</li>
<li id="field_options">
	<label for="options" class="full">Options</label>
	<textarea class="text full" name="options" style="height: 80px"><?=$options;?></textarea><br />
	<div class="help" style="margin-left: 0">Enter each option on a newline.  If you want the option database value to be different than the
	option the user actually selects, enter it in the format of "Name=Value".</div>
</li>
<li>
	<label for="required" class="full">Validators</label>
</li>
<li>
	<?=form_checkbox('required','1',$field['required']);?> Required field<br />
	<?=form_checkbox('validate_email','1',(in_array('email',$field['validators'])) ? TRUE : FALSE);?> Valid email address<br />
	<?=form_checkbox('validate_whitespace','1',(in_array('whitespace',$field['validators'])) ? TRUE : FALSE);?> Trim surrounding whitespace<br />
	<?=form_checkbox('validate_alphanumeric','1',(in_array('alphanumeric',$field['validators'])) ? TRUE : FALSE);?> Must contain only alphanumeric (a-Z, 0-9) characters<br />
	<?=form_checkbox('validate_numeric','1',(in_array('numeric',$field['validators'])) ? TRUE : FALSE);?> Must contain only numeric characters<br />
	<?=form_checkbox('validate_domain','1',(in_array('domain',$field['validators'])) ? TRUE : FALSE);?> Must be a valid domain (e.g, "test.com", "sub.text.com")<br />
</li>