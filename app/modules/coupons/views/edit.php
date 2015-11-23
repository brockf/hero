<?=$this->head_assets->javascript('js/form.coupon.js');?>

<?=$this->load->view(branded_view('cp/header')); ?>

<h1>Edit Coupon</h1>

<form class="form validate" id="form_coupon" method="post" action="<?=$form_action;?>">
<?= $form ?>

<div class="submit">
	<input type="submit" class="button" name="add_coupon" value="Save Coupon" />
</div>
</form>

<?= $this->load->view(branded_view('cp/footer')); ?>