<?=$this->load->view(branded_view('cp/header'));?>
<h1>Are you sure you want to uninstall this module?</h1>

<p>All data will be deleted for the module.  You will not be able to recover this data through any means provided by this software (though you
may have backups, elsewhere).</p>

<p>If you are simply trying to upgrade the module, this is done automatically just by uploading the new files.  Do NOT uninstall to upgrade.</p>

<p>You will be able to re-install at anytime.</p>

<p><b>Are you sure you want to uninstall this module?</b></p>

<form method="post" action="<?=site_url('admincp/settings/module_uninstall_confirm');?>">
	<input type="hidden" name="module" value="<?=$module;?>" />
	
	<input type="submit" name="" value="I have read the above notes. Uninstall Now!" />
</form>

<?=$this->load->view(branded_view('cp/footer'));?>