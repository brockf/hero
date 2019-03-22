<?php $this->load->view(branded_view('cp/header')); ?>

<h1>Paywall Configuration</h1>

<form class="form validate" method="post" action="admincp/paywall/save">

<h2>Auto-Paywall</h2>
<p>The Auto-Paywall will redirect your users to a "paywall" page whenever they come upon a page on your site that is restricted
from their access.  By default, this paywall will contain a login form, link to register/subscribe, and a small marketing pitch for
your website.  However, you can modify the template file (specified below) to include or say whatever you would like.</p>	
	
	<ul class="form">
		<li>
			<label>Auto-Paywall</label> <?=form_radio('paywall_auto','1', ($paywall_auto == '1') ? TRUE : FALSE);?> On&nbsp;&nbsp;&nbsp; <?=form_radio('paywall_auto','0', ($paywall_auto == '0') ? TRUE : FALSE);?> Off
		</li>
		<li>
			<label>Paywall Template</label> <?=form_dropdown('paywall_template', $template_files, $paywall_template);?>
		</li>
		<li class="indent">
			<label>&nbsp;</label><input type="submit" class="submit button" name="" value="Save Configuration" />
		</li>
	</ul>
</form>
<br />
<h2>Manual Paywall &amp; Permissions Control</h2>
<p>If you choose to not use the Auto-Paywall and manually deal with access permissions problems, that is fine.  There are template
function plugins which will help you deal with permissions problems right in the template.  For example, in your <span class="code">content.thtml</span>
template, you may have the following:</p>
<pre class="code">
	{extends file="layout.thtml"}
	{restricted in_group=$privileges}
		&lt;h1&ht;{$title}&lt;/h1&gt;
		{$body}
	{/restricted}
	
	{restricted not_in_group=$privileges}
		You do not have access to see this content.  <a href="{url path="users/login"}">Login</a> or <a href="{url path="subscriptions"}">Subscribe</a>
		to gain access today!
	{/restricted}
</pre>
<?=$this->load->view(branded_view('cp/footer'));?>