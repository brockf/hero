<?=$this->head_assets->javascript('js/jquery-ui-1.8.2.min.js');?>
<?=$this->head_assets->javascript('js/image_gallery.js');?>
<?=$this->head_assets->javascript('js/image_gallery_form.js');?>

<?=$this->head_assets->stylesheet('app/modules/gallery/stylesheets/gallery.css', FALSE);?>

<?=$this->load->view(branded_view('cp/header'));?>
<form class="form validate" id="form_create_post" enctype="multipart/form-data" method="post" action="<?=$form_action;?>">
<?=form_hidden('content_id',$gallery['id']);?>

<h1>Gallery Images</h1>

<div class="sidebar">
	<h2>Upload File(s)</h2>
	
	<div class="sidebar_content">
		<input type="file" name="image" width="100%" />
		
		<p>Upload a ZIP, JPG, PNG, or GIF, file to the gallery.
		ZIP archives will be unpacked and added individually.</p>
		
		<input type="submit" class="button" name="" value="Upload Now" />
	</div>
</div>
<div style="float: left; width: 70%;">
	<? if (!empty($gallery['images'])) { ?>
		<div class="gallery_images">
			<ul class="image_gallery" rel="<?=site_url('admincp/gallery/save_image_order/' . $gallery['id']);?>">
			<? foreach ($gallery['images'] as $image) { ?>
				<li id="image_<?=$image['id'];?>">
					<? if ($image['featured'] == TRUE) { ?><span class="featured">Featured</span><? } ?>
					<img src="<?=image_thumb($image['path'], 100, 100);?>" alt="image" />
					<a class="move" href="#"><img src="<?=branded_include('images/arrow.png');?>" title="drag to re-order" /></a>
					<? if ($image['featured'] == FALSE) { ?><a class="feature" href="<?=site_url('admincp/gallery/image_feature/' . $gallery['id'] . '/' . $image['id']);?>"><img src="<?=branded_include('images/star.png');?>" title="make featured image" /></a><? } ?>
					<a class="delete" href="<?=site_url('admincp/gallery/image_delete/' . $gallery['id'] . '/' . $image['id']);?>"><img src="<?=branded_include('images/bin.png');?>" title="delete" /></a>
				</li>		
			<? } ?>
			</ul>
			<div style="clear:both"></div>
		</div>
	<? } else { ?>
		<p>No images, yet.</p>
	<? } ?>
</div>	

<div style="clear:both"></div>

<?=$this->load->view(branded_view('cp/footer'));?>