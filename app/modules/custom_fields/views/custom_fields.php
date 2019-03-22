<?=$this->load->view(branded_view('cp/header'));?>
<h1>Custom Fields</h1>

<ul class="inner_nav">	
	<li><a href="<?=site_url('admincp/users/data');?>">Member Data</a></li>
	<? if (module_installed('store')) { ?>
		<li><a href="<?=site_url('admincp/store/data');?>">Product Data</a></li>
		<li><a href="<?=site_url('admincp/store/collection_data');?>">Store Collection Data</a></li>
	<? } ?>
</ul>

<h3>What are custom fields?</h3>
<p>Custom fields extend existing datasets within <?=setting('app_name');?>.  For example, if you want to collect
additional information from members when they register (e.g., about their occupation), you may add the custom fields,
"Current Employer", and "Position".</p>

<p>Custom fields cover all of the standard web form input types:</p>
<ul>
	<li>Text fields</li>
	<li>Textarea fields</li>
	<li>WYSIWYG editor textarea fields</li>
	<li>Select/dropdown menus</li>
	<li>Radio buttons</li>
	<li>Checkboxes</li>
	<li>File upload boxes</li>
</ul>

<h3>Integrating Custom Fields</h3>

<p>Most custom fields will require someone with beginner level HTML experience to utilize this custom data into your
frontend web design (i.e., templates).  For information on customizing your site's design and using custom fields in
templates, visit the <a href="<?=site_url('app_support');?>">support site</a>.</p>
<?=$this->load->view(branded_view('cp/footer'));?>