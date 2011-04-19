<?=$this->load->view(branded_view('cp/header'));?>
<h1>Refund unable to be processed</h1>

<p>This refund was unable to be processed automatically.  This is likely due to the limitations of your payment processor.
Please login to your payment processor's control panel and issue a refund manually.</p>

<p>If you would like to manually mark this invoice as refunded,
<a href="<?=site_url('admincp/reports/mark_refunded/' . $invoice['id']);?>">click here</a>.</p>

<?=$this->load->view(branded_view('cp/footer'));?>