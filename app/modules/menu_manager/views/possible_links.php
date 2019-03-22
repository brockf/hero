<table class="links" cellspacing="0" cellpadding="0">
	<tbody>
		<? foreach ($possible_links as $link) { ?>
			<tr>
				<td class="title"><?=$link['text'];?></td>
				<td class="type"><?=$link['type'];?></td>
				<td class="button"><input rel="<?=$link['code'];?>" type="button" class="button add_link" name="go" value="Add this Link" /></td>
			</tr>
		<? } ?>
	</tbody>
</table>