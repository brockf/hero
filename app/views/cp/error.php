<?=$this->load->view(branded_view('cp/header'));?>
<h1>System Error</h1>
<p>The system has returned an unexpected error from a core method.</p>
<p>Error Number: <b><?=$error;?></b></p>
<p>Error Text: <b><?=$error_text;?></b></p>
<p>Possible Causes:</p>
<ul>
	<li>You completed a form improperly and the form validators failed to detect the malformed input.</li>
	<li>You attempted to access an object that was outside of your ownership.</li>
	<li>A method was called improperly.</li>
</ul>
<?=$this->load->view(branded_view('cp/footer'));?>