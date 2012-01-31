<? if (empty($links)) { ?>
<p>This menu is currently empty.</p>
<? } else { ?>

<ul class="current_links">
	<? foreach ($links as $link) { ?>
	<li class="no_hover" id="link_<?=$link['id'];?>" rel="<?=$link['id'];?>">
		<span class="handle"><img src="<?=branded_include('images/arrow.png');?>" alt="drag to move" title="drag to move" class="handle" /></span>
		<span class="text"><?=$link['text'];?></span>
		<span class="actions">
			<input type="button" class="button edit_link" name="go" value="Edit" />
			<input type="button" class="button remove_link" name="go" value="Remove" />
			<? if ($parent_id == 0) { ?>
				<? if ($link['children'] == 0) { ?>
					<? if ($link['type'] != 'special') { ?>
					<input type="button" class="button manage_children" name="go" value="Create Submenu" />	
					<? } ?>
				<? } else { ?>
				<input type="button" class="button manage_children" name="go" value="Manage <?=$link['children'];?> Sublinks" />	
				<? } ?>
			<? } ?>
		</span>
		<div class="editing">
			<form class="validate" method="post" action="<?=site_url('menu_manager/post_edit_link');?>">
				<table>
					<tr>
						<td valign="top">
							<label for="text">Display Text</label><br />
							<input type="text" class="text required" name="text" id="text" style="width: 97%" value="<?=$link['text'];?>" />
							
							<?php if (!empty($link['external_url'])) : ?>
								<label for="external_url">URL</label>
								<input type="text" class="text required" name="external_url" id="external_url" style="width: 97%" value="<?= $link['external_url'] ?>" />
							<?php endif; ?>
						</td>
						<td valign="top">
							<label for="privileges">Visibility</label><br />
							<select name="privileges" id="privileges" multiple="multiple" style="height: 63px">
								<option value="0" <? if ($link['privileges'] == FALSE or in_array(0,$link['privileges'])) { ?>selected="selected"<? } ?>>Public / All Member Groups</option>
								<option value="-1" <? if ($link['privileges'] != FALSE and in_array('-1',$link['privileges'])) { ?>selected="selected"<? } ?>>Logged Out Visitors Only</option>
								<? foreach ($groups as $group) { ?>
									<option value="<?=$group['id'];?>" <? if ($link['privileges'] != FALSE and in_array($group['id'], $link['privileges'])) { ?>selected="selected"<? } ?>><?=$group['name'];?></option>
								<? } reset($groups); ?>
							</select>
						</td>
						<td valign="top">
							<label for="class">CSS Classes</label>
							<input type="text" class="text" name="class" id="class" style="width: 97%" value="<?=$link['class'];?>" />
						</td>
					</tr>
				</table>
			</form>
		</div>
		<div style="clear:both"></div>
	</li>
	<? } ?>
	<div style="clear:both"></div>
</ul>

<? } ?>