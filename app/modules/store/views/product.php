<?=$this->head_assets->javascript('js/jquery-ui-1.8.2.min.js');?>
<?=$this->head_assets->javascript('js/image_gallery.js');?>
<?=$this->head_assets->javascript('js/image_gallery_form.js');?>

<?=$this->load->view(branded_view('cp/header'));?>

<h1>Product: <?=$product['name'];?></h1>
<div class="product_details">
	<h3>Product Details</h3>
	<ul class="data">
		<li><span class="tag">Price</span><?=setting('currency_symbol');?><?=$product['price'];?></li>
		<? if ($product['member_tiers'] != FALSE) { ?>
			<? foreach ($product['member_tiers'] as $group => $price) { ?>
				<li><span class="tag">Price (<?=$usergroups[$group];?>)</span><?=setting('currency_symbol');?><?=$price;?></li>
			<? } ?>
		<? } ?>
		<li><span class="tag">Weight</span><?=$product['weight'];?> <?=setting('weight_unit');?></li>
		<li><span class="tag">Taxable</span><? if ($product['is_taxable'] == TRUE) { ?>Yes<? } else { ?>No<? } ?></li>
		<li><span class="tag">Require Shipping</span><? if ($product['requires_shipping'] == TRUE) { ?>Yes<? } else { ?>No<? } ?></li>
		<? if ($product['sku'] != '') { ?><li><span class="tag">SKU</span><?=$product['sku'];?></li><? } ?>
		<? if ($product['track_inventory'] == TRUE) { ?>
		<li><span class="tag">Quantity in Stock</span><?=$product['inventory'];?></li>
		<li><span class="tag">Allow Overselling?</span><? if ($product['inventory_allow_oversell'] == TRUE) { ?>Yes<? } else { ?>No<? } ?></li>
		<? } ?>
		<? if ($product['is_download'] == TRUE) { ?>
		<li><span class="tag">Product File</span><?=$product['download_name'];?> (<?=format_size($product['download_size']);?>)</li>
		<? } ?>
		<? if ($product['promotion'] != FALSE) { ?>
		<li><span class="tag">Purchase Group</span><?=$usergroups[$product['promotion']];?></li>
		<? } ?>
		<? if (is_array($custom_fields)) { ?>
			<? foreach ($custom_fields as $field) { ?>
				<? if (@is_array(unserialize($product[$field['name']]))) { ?>
					<? $product[$field['name']] = implode(', ', unserialize($product[$field['name']])); ?>
				<? } ?>
			
				<li><span class="tag"><?=$field['friendly_name'];?></span> <?=$product[$field['name']];?></li>
			<? } ?>
		<? } ?>
	</ul>
</div>

<?=$product['description'];?>

<h2 class="cat" style="clear:both">Product Images</h2>

<form class="form" method="post" enctype="multipart/form-data" action="<?=site_url('admincp/store/product_images/' . $product['id']);?>">
<?=$gallery;?>
<? if (!empty($product['images'])) { ?>
<fieldset>
	<label>Current Images</label>
	<div class="product_images">
		<ul class="image_gallery" rel="<?=site_url('admincp/store/save_image_order/' . $product['id']);?>">
		<? foreach ($product['images'] as $image) { ?>
			<li id="image_<?=$image['id'];?>">
				<? if ($image['featured'] == TRUE) { ?><span class="featured">Featured</span><? } ?>
				<img src="<?=image_thumb($image['path'], 100, 100);?>" alt="image" />
				<a class="move" href="#"><img src="<?=branded_include('images/arrow.png');?>" title="drag to re-order" /></a>
				<? if ($image['featured'] == FALSE) { ?><a class="feature" href="<?=site_url('admincp/store/product_image_feature/' . $product['id'] . '/' . $image['id']);?>"><img src="<?=branded_include('images/star.png');?>" title="make featured image" /></a><? } ?>
				<a class="delete" href="<?=site_url('admincp/store/product_image_delete/' . $product['id'] . '/' . $image['id']);?>"><img src="<?=branded_include('images/bin.png');?>" title="delete" /></a>
			</li>		
		<? } ?>
		</ul>
		<div style="clear:both"></div>
	</div>
</fieldset>
<? } ?>
</form>


<?=$this->load->view(branded_view('cp/footer'));?>