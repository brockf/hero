<?=$this->load->view(branded_view('cp/header'));?>
<h1>Member List Configuration</h1>

<p>Select which user data fields you would like to display in the Manager Members list.  You can use any of the basic user fields
(username, email, name, etc.) and also any custom fields that are either text, select, multiselect, or radio fields.</p>

<form class="form validate" enctype="multipart/form-data" id="go_configure" method="post" action="<?=$form_action;?>">

<?
$this->load->helper('form');

$dropdown_options = array();
$dropdown_options[''] = 'empty';
foreach ($options as $key => $option) {
	$dropdown_options[$key] = $option['name'];
}

?>

<ul class="form">
	<? for ($i = 1; $i <= 7; $i++) { ?>
		<li>
			<label>Column #<?=$i;?></label> <?=form_dropdown('column_' . $i, $dropdown_options, isset($configuration[$i - 1]) ? $configuration[$i - 1] : FALSE);?>
		</li>
	<? } ?>
</ul>

<div class="submit">
	<input type="submit" class="button" name="go_configure" value="Save Configuration" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>