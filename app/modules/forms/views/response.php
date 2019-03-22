<?php $this->load->view(branded_view('cp/header')); ?>
<h1><?=$form['title'];?>: Viewing Submission</h1>

<?
foreach ($lines as $header => $value) {
?>
	<p><b><?=$header;?></b><br />
	<?=$value;?></p>
<?	
}
$this->load->view(branded_view('cp/footer'));
?>