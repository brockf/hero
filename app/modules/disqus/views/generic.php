<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="disqus" method="post" action="<?=$form_action;?>">

<ul class="form">
<?=$form;?>
</ul>

<div class="submit">
	<input type="submit" class="button" name="go_disqus" value="<?=$form_button;?>" />
</div>
</form>

<? if (!empty($disqus_shortname)) { ?>
<br /><br />
<h3>Your Disqus comments section is configured properly.</h3>

<p>Paste the following code in your templates where you would like to add a comments section.</p>

<pre class="code">
{disqus_comments}
</pre>

<? } ?>

<?=$this->load->view(branded_view('cp/footer'));?>