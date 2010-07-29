<?=$this->load->view(branded_view('cp/header.php'), array('head_files' => '<script type="text/javascript" src="' . branded_include('js/settings.js') . '"></script>'));?>

<?=$this->load->helper('shorten');?>

<h1>Settings Manager</h1>

<p class="warning"><span>Take caution when editing settings as they may be critical to the proper function of your website.</span></p>

<? foreach ($groups as $group_id => $group) { ?>
	<div class="setting_group">
		<a class="cat" href="#"><?=$group['name'];?></a>
		<table class="settings" cellpadding="0" cellspacing="0">
			<? $count = 1;
			   foreach ($settings[$group_id] as $setting) { ?>
				<tr class="<? if ($count % 2 == 0) { ?>odd<? } else { ?>even<? } ?>">
					<td class="name" valign="top"><?=$setting['name'];?></td>
					<td class="help"><?=$setting['help'];?></td>
					<td class="edit">
					<? if ($setting['type'] == 'text') { ?>
						<a class="text edit" href="#">edit</a></td>
					<? } elseif ($setting['type'] == 'textarea') { ?>
						<a class="textarea edit" href="#">edit</a></td>
					<? } elseif ($setting['type'] == 'toggle') { ?>
						<a class="toggle" href="#">toggle</a></td>
					<? } ?>
					<td class="value">
						<? if ($setting['type'] == 'text') { ?>
							<?=$setting['value'];?>
						<? } elseif ($setting['type'] == 'textarea') { ?>
							<?=nl2br(shorten($setting['value'],250));?>
							<textarea style="display:none; height: 150px" class="value" id="<?=$setting['name'];?>"><?=$setting['value'];?></textarea>
						<? } elseif ($setting['type'] == 'toggle') { ?>
							<?=$setting['toggle_value'];?>
						<? } ?></td>
				</tr>
			
			<? $count++; } ?>
		</table>
	</div>
<? } ?>

<?=$this->load->view(branded_view('cp/footer.php'));?>