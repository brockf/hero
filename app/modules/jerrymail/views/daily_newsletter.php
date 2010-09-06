<?=$this->load->view('jerrymail/email_header');?>

<h1 style="font-size: 19pt; font-weight: bold; letter-spacing: -1px; font-family: lucida grande, helvetica, arial, sans-serif; color: #000"><?=$lead_article['title'];?></h1>
<div style="color: #666; padding: 5px 0px; margin-bottom: 10px; border-bottom: 1px solid #f0f0f1"><?=date('l, F j, Y', strtotime($lead_article['date']));?></div>

<? if (!empty($lead_article['feature_image'])) { ?>
	<img style="float: left; margin-right: 15px; margin-bottom: 15px; margin-top: 7px" src="<?=image_thumb($lead_article['feature_image'],$lead_article['image_size'],$lead_article['image_size']);?>" alt="" />
<? } ?>

<span style="color: #666; padding: 5px 0px; font-weight: bold; font-style: italic">By <?=$lead_article['author_first_name'];?> <?=$lead_article['author_last_name'];?></span>

<?=$lead_article['summary'];?>
<p><b><a href="<?=$lead_article['url'];?>">Read more at <?=setting('site_name');?></a></b></p>

<h1 style="font-size: 19pt; font-weight: bold; letter-spacing: -1px; margin-top: 30px; font-family: lucida grande, helvetica, arial, sans-serif; color: #000"><?=$promo['title'];?></h1>
<?=$promo['body'];?>

<div style="background-color: #fffce4; padding: 8px 12px">
	You are receiving this email because you are a subscriber to <a href="<?=base_url();?>"><?=setting('site_name');?></a><br /><br />
	<a href="<?=site_url('user');?>">Click here to unsubscribe or manage your account</a>.
</div>

<?=$this->load->view('jerrymail/email_footer');?>
