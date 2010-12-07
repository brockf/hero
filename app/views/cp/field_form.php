<?

/* Default Values */

if (!isset($field)) {
	$field = array();
}

$field['friendly_name'] = (isset($field['friendly_name'])) ? $field['friendly_name'] : '';
$field['type'] = (isset($field['type'])) ? $field['type'] : 'text';
$field['required'] = (isset($field['required']) and $field['required'] == '1') ? TRUE : FALSE;
$field['validators'] = (isset($field['validators'])) ? $field['validators'] : array();
$field['help'] = (isset($field['help'])) ? $field['help'] : '';
$field['default'] = (isset($field['default'])) ? $field['default'] : '';
$field['width'] = (isset($field['width'])) ? $field['width'] : '250px';
$field['data'] = (isset($field['data'])) ? $field['data'] : array();

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

$this->load->library('custom_fields/fieldtype');
$fieldtype_options = $this->fieldtype->get_fieldtype_options();

?>
<li>
	<label for="type" class="full">Type</label>
	<?=form_dropdown('type', $fieldtype_options, $field['type'], 'id="type"');?>
</li>