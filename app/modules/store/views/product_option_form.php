<?=$this->head_assets->javascript('js/product_option.js');?>
<?=$this->head_assets->javascript('js/form.js');?>

<?=$this->load->view(branded_view('cp/header'));?>

<h1>Create New Product Option</h1>

<form class="form validate" id="form_product_option" method="post" action="<?=$form_action;?>">

<?=$form?>

<fieldset>
	<legend>Values</legend>
	
	<input type="hidden" name="share_it" value="1" />

	<ul class="form">
		<li style="margin-bottom: 1em">
			<label>Values</label>
			<span style="width: 14em; display: inline-block"><b>Label</b></span>
			<span><b>Price(optional)</b></span>
		</li>
		<li>
			<label>Value #1</label>
			<input type="text" class="values" style="width: 16em" name="option[]" rel="Label" value="" />
			<input type="text" name="price[]" rel="Price(optional)" value="" />
			<input type="button" id="" class="button delete-option" style="margin-left: 30px;" value="Delete Value" />
		</li>
		<li>
			<input type="button" id="add_value" class="button" style="margin-left: 150px;" name="" value="+ Add Another Value">
		</li>
	</ul>
</fieldset>

<fieldset>
<div class="submit">
	<input type="submit" class="button" name="go_new_product" value="Save Product Option" />
</div>
</fieldset>
</form>

<?=$this->load->view(branded_view('cp/footer'));?>