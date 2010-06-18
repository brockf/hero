<?

foreach ($custom_fields as $field) {
	$values[$field['name']] = (isset($values[$field['name']])) ? $values[$field['name']] : '';
	
	$required = ($field['required'] == TRUE) ? ' required' : '';
	?>
	<li>
		<label for="<?=$field['name'];?>"><?=$field['friendly_name'];?></label>
	<?
	if ($field['type'] == 'text') {
	?>
		<input type="text" class="text<?=$required;?>" id="<?=$field['name'];?>" name="<?=$field['name'];?>" value="<?=$values[$field['name']];?>" />
	<? } ?>
	
	</li>
	<?
}
?>