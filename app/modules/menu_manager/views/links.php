<? if (empty($links)) { ?>
<p>This menu is currently empty.</p>
<? } else { ?>

<ul>
	<? foreach ($links as $link) { ?>
	<li rel="<?=$link['id'];?>">
		<span class="handle"><a href="#"><img src="<?=branded_include('images/arrow.png');?>" alt="drag to move" title="drag to move" /></a></span>
		<span class="name"><?=$link['name'];?></span>
		<span class="actions">
			<input type="button" class="button edit_link" name="go" value="Edit" />
			<input type="button" class="button remove_link" name="go" value="Remove" />
		</span>
	</li>
	<? } ?>
	<div style="clear:both"></div>
</ul>

<? } ?>