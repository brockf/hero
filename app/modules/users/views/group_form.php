<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" enctype="multipart/form-data" id="form_user" method="post" action="<?=$form_action;?>">

<?=$form;?>

<div class="submit">
	<input type="submit" class="button" name="go_member_group" value="Save Member Group" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>