<?=$this->load->view(branded_view('cp/header'));?>
<h1>Enable custom product data fields?</h1>
<form class="form validate" id="form_field" method="post" action="<?=site_url('admincp/store/enable_data');?>">
<p style="width:600px">Custom product data fields allow you to store unique data for each product.  Each datum is represented by a custom field
that is seen during the adding and editing of products.  Developers can then use this custom field data in your
templates or as part of a larger customization.</p>
<p>Would you like to enable custom product data?</p>
<div class="submit">
	<input type="submit" class="button" name="go_field" value="Yes, enable custom product data fields" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>