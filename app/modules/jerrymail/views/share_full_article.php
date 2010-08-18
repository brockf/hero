<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
</head>
<body>
<table style="width:100%; background-color: #EDEDED; border: 2px solid #ccc; font-family: lucida grande, helvetica, arial, sans-serif" cellpadding="0" cellspacing="0">
	<tr>
		<td style="padding: 10px">
			<table style="width:100%">
				<tr>
					<td style="text-align: left; padding: 30px 15px 15px 0">
						<img src="<?=site_url('themes/orchard/images/logo.gif');?>" alt="<?=setting('site_name');?>" />
					</td>
				</tr>
				<tr>
					<td>
						<table style="width: 100%; background-color: #fff; border: 1px solid #ccc">
							<tr>
								<td style="padding: 15px; font-family: lucida grande, helvetica, arial, sans-serif; line-height: 1.8em; color: #555; font-size: 11pt">
									<div style="background-color: #fffce4; padding: 8px 12px">
										This email was sent to you because a friend thought you would like to read this article.
										<a href="<?=base_url();?>">Inside Music Media</a> is the web home of Jerry Del Colliano.<br /><br />
										<a href="<?=$url;?>">Click here to read the article at Inside Music Media</a>.
									</div>
									<h1 style="font-size: 22pt; font-weight: bold; letter-spacing: -1px; font-family: lucida grande, helvetica, arial, sans-serif; color: #000"><?=$title;?></h1>
									<?=$body;?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>