<?=$this->load->view(branded_view('cp/header'));?>
<h1>Import Members</h1>

<p>Importing members allows you to bring any members that you have in a CSV file into your Caribou system.</p>

<p>To get started, select the CSV file to upload.</p>

<form class="form validate" enctype="multipart/form-data" id="form_user" method="post" action="<?=$form_action;?>">

<?= $form ?>

<div class="submit">
	<input type="submit" class="button" name="submit" value="Upload CSV File" />
</div>
</form>


<?=$this->load->view(branded_view('cp/footer'));?>