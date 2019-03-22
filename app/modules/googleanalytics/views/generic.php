<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="twitter" method="post" action="<?=$form_action;?>">

<?=$form;?>

<div class="submit">
	<input type="submit" class="button" name="go_twitter" value="<?=$form_button;?>" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>