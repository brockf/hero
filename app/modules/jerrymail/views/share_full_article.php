<?=$this->load->view('jerrymail/email_header');?>

<div style="background-color: #fffce4; padding: 8px 12px">
	This email was sent to you because a friend thought you would like to read this article.
	<a href="<?=base_url();?>">Inside Music Media</a> is the web home of Jerry Del Colliano.<br /><br />
	<a href="<?=$url;?>">Click here to read the article at Inside Music Media</a>.
</div>
<h1 style="font-size: 22pt; font-weight: bold; letter-spacing: -1px; font-family: lucida grande, helvetica, arial, sans-serif; color: #000"><?=$title;?></h1>
<?=$body;?>

<?=$this->load->view('jerrymail/email_footer');?>
