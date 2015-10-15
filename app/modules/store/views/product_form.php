<?php

if (!isset($product) or empty($product)) {
	$product = array(
						'download' => FALSE
					);
}

?>

<?=$this->head_assets->javascript('js/product.js');?>
<?=$this->head_assets->javascript('js/image_gallery_form.js');?>

<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" enctype="multipart/form-data" id="form_product" method="post" action="<?=$form_action;?>">

<?=$form;?>

<fieldset>
	<legend>Product Options</legend>
	<ul class="form">
		<li>
			<label>&nbsp;</label>
			<b>Product Options</b>
		</li>
		<li>
			<div class="help">Specify customizable options for this product. Optionally, specify a price adjustment for each (e.g., "&#043;$5.00 for a Large").</div>
		</li>
		<li>
			<ul id="product_options">
			
			</ul>
		</li>
		<li class="indent">
			<label>&nbsp;</label>
			<input type="button" class="button" id="add_product_option" value="&#043; Add Product Option" />
		</li>
	</ul>
</fieldset>

<fieldset>
	<legend>Downloadable Options</legend>
	<ul class="form">
		<li>
			<label>&nbsp;</label>
			<input type="checkbox" value="1" id="download" name="download" <? if ($product['download'] == TRUE) { ?>checked="checked"<? } ?> />&nbsp;<b>Downloadable Product - Send purchasing customers a file download after purchase</b>
		</li>
		<li class="file_options">
			<label>File</label>
			Upload file (maximum size: <?=setting('upload_max');?>): <input type="file" name="file" />&nbsp;&nbsp;&nbsp;<i>or</i> Select an uploaded file: <select name="file_uploaded" id="file_uploaded"><? foreach ($files as $file) { ?><option value="<?=$file;?>"><?=$file;?></option><? } ?></select> (<a href="#" id="refresh_files">refresh file listing</a>)
		</li>
		<li class="file_options">
			<div class="help">To upload a file via FTP, upload the file to <?=setting('path_product_files');?> and select "refresh file listing" to select the file without losing your form data.</div>
		</li>
	</ul>
</fieldset>

<fieldset>
	<legend>Membership Options</legend>
	<ul class="form">
		<li>
			<label>&nbsp;</label>
			<input type="checkbox" value="1" id="group_move" name="group_move" />&nbsp;<b>Upon purchase, add the member into a new member group</b>
		</li>
		<li class="group_move_options">
			<label>Member Group</label>
			<?=form_dropdown('promotion', $usergroups, '0');?>&nbsp;&nbsp;
		</li>
	</ul>
</fieldset>

<fieldset>
	<legend>Membership Pricing</legend>
	<ul class="form">
		<li>
			<label>&nbsp;</label>
			<input type="checkbox" value="1" id="membership_tiers" name="membership_tiers" />&nbsp;<b>Specify pricing for specific member groups</b>
		</li>
		<li class="membership_tiers">
			<label>&nbsp;</label>
			<?=form_dropdown('membership_tier[]', $usergroups, '0');?>&nbsp;&nbsp;Members' Price (<?=setting('currency_symbol');?>): <input type="text" id="membership_tier_price" class="text number" name="membership_tier_price[]" placeholder="9.95" /><label for="membership_tier_price" style="display:none">Membership Tier Price</label>
		</li>
		<li class="membership_tiers">
			<label>&nbsp;</label>
			<input id="add_membership_tier" type="button" class="button" value="&#043; Add new membership tier" />
		</li>
	</ul>
</fieldset>

<?=$gallery;?>

<? if (!empty($custom_fields)) { ?>
<fieldset>
	<legend>Custom Fields</legend>
	<ul class="form">
		<?=$custom_fields;?>
	</ul>
</fieldset>
<? } ?>

<fieldset>
<div class="submit">
	<input type="submit" class="button" name="go_product" value="Save Product" />
</div>
</fieldset>

<p class="warning"><span>If you are not receiving a server response after submitting this form, you are likely surpassing the total file upload
	limits of your server.  To bypass this: (1) upload your product downloads via FTP and, (2) add your images after submitting the product, in the "image gallery editor".<br /><br />
	You can also contact your server administrator to increase these upload limits.</span></p>
</form>

<div class="modal" id="add_option_dialog">
	<? if (!empty($shared_product_options)) { ?>
	<h3>Existing Product Options</h3>
	<form class="form validate" id="use_existing_option_dialog_form" style="margin-bottom: 15px" method="post" action="">
		<ul class="form">
			<li>
				<label>Select Option</label>
				<select name="option">
					<? foreach ($shared_product_options as $option) { ?>
						<option value="<?=$option['id'];?>"><?=$option['name'];?></option>
					<? } ?>
				</select>
			</li>
			<li>
				<label>&nbsp;</label>
				<input type="submit" class="button" value="Use This Option" />
			</li>
		</ul>
	</form>
	<? } ?>
	
	<h3>New Product Option</h3>
	<form class="form validate" id="add_option_dialog_form" method="post" action="<?=site_url('admincp/store/post_product_option');?>">
		<ul class="form">
			<li>
				<label class="full">Option Name</label>
			</li>
			<li>
				<input type="text" name="name" class="mark_empty text full" rel="e.g, Color" value="" />
			</li>
			<li>
				<label>Value #1</label>
				<input type="text" name="value1" class="values mark_empty text" style="width:130px" rel="Label" />&nbsp;&nbsp;
				<input type="text" name="price1" class="prices mark_empty text" style="width: 100px" rel="Price (optional)" />
			</li>
			<li>
				<label>&nbsp;</label>
				<input type="button" id="add_value" class="button" name="" value="&#043; Add Another Value" />
			</li>
			<li>
				<input type="checkbox" name="save" value="1" />&nbsp;<b>Save for use with other products</b>
			</li>
		</ul>
		<div class="submit">
			<input type="submit" class="button" value="Save Product Option" />&nbsp;&nbsp;<span class="response"></span><img class="loading" src="<?=branded_include('images/loading.gif');?>" alt="Loading..." style="display: none" title="Loading..." />
		</div>
	</form>	
</div>

<?=$this->load->view(branded_view('cp/footer'));?>