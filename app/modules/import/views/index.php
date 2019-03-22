<?=$this->load->view(branded_view('cp/header'));?>
<h1>Import Members</h1>

<p>Import members into your website's member database by uploading a CSV file of member information.</p>

<p><b>Every imported record must have,
at minimum, an email address, first name, and last name.  Passwords can be auto-generated, or imported as well</b>.</p>

<p>To get started, select the CSV file of member information to upload.</p>

<form class="form validate" enctype="multipart/form-data" id="form_user" method="post" action="<?=$form_action;?>">

<?= $form ?>

<div class="submit">
	<input type="submit" class="button" name="submit" value="Upload CSV File" />
</div>
</form>


<?=$this->load->view(branded_view('cp/footer'));?>