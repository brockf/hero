<?=$this->head_assets->javascript('js/product_option.js');?>
<?=$this->head_assets->javascript('js/form.js');?>

<?=$this->load->view(branded_view('cp/header'));?>

<h1>Product Option: <?=$option_name;?></h1>

<form class="form validate" id="form_product_option" method="post" action="<?=$form_action;?>">

<input type="hidden" name="product_option_id" value="<?php echo $product_option['id'] ?>" />

<?=$form?>

<fieldset>
	<legend>Values</legend>
	
	<?php $count = 1; ?>
	<ul class="form">
		<li style="margin-bottom: 1em">
			<label>Values</label>
			<span style="width: 14em; display: inline-block"><b>Label</b></span>
			<span><b>Price(optional)</b></span>
		</li>
	<?php foreach ($product_option['options'] as $option) : ?>
		<li>
			<label>Value #<?php echo $count ?></label>
			<input type="text" class="values" style="width: 16em" name="option[]" rel="Label" value="<?php echo $option['label'] ?>" />
			<input type="text" name="price[]" rel="Price(optional)" value="<?php echo !empty($option['price']) ? $option['price'] : null; ?>" />
			<input type="button" id="" class="button delete-option" style="margin-left: 30px;" value="- Delete Value" />
		</li>
		<?php $count++; ?>
	<?php endforeach; ?>
		<li>
			<input type="button" id="add_value" class="button" style="margin-left: 150px;" name="" value="+ Add Another Value">
		</li>
	</ul>
</fieldset>

<fieldset>
<div class="submit">
	<input type="submit" class="button" name="go_product" value="Save Product Option" />
</div>
</fieldset>
</form>

<?=$this->load->view(branded_view('cp/footer'));?>