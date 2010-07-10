<?=$this->load->view(branded_view('cp/header'), array('head_files' => '<link href="' . branded_include('css/menu_manager.css') . '" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="' . branded_include('js/menu_manager.js') . '"></script>'));?>
<h1>Menu Manager</h1>
<div id="menu_manager">
	<div id="list_menus">
		<h2>Site Menus</h2>
		<ul>
			<? foreach ($menus as $menu) { ?>
			<li><a class="<? if ($menu['id'] == $active_menu) { ?> active<? } ?>" href="<?=site_url('admincp/menu_manager/switch_active/' . $menu['id']);?>" rel="<?=$menu['id'];?>"><?=$menu['name'];?></a></li>
			<? } ?>
		</ul>
		<input type="button" class="button" id="add_menu" name="go" value="&#43; Create New Menu" />
	</div>
	<div id="active_menu">
		<div id="active_menu_wrapper">
			<div id="list_items">
				<h2><?=$title;?></h2>
				<div id="list_items_wrapper">
					
				</div>
			</div>
			<div id="link_creator">
				<h2>Add Link(s) to Menu</h2>
				<div id="link_creator_wrapper">
					<table class="links" cellspacing="0" cellpadding="0">
						<tbody>
							<? foreach ($possible_links as $link) { ?>
								<tr>
									<td class="title"><?=$link['name'];?></td>
									<td class="type"><?=$link['module'];?></td>
									<td class="button"><input rel="<?=$link['code'];?>" type="button" class="button add_link" name="go" value="Add this Link" /></td>
								</tr>
							<? } ?>
						</tbody>
					</table>
				</div>
				<div id="external_form">
					<input type="text" class="text mark_empty" rel="http://www.yahoo.com" id="external_link" name="external_link" />
					<input type="text" class="text mark_empty" rel="Yahoo!" id="external_link_name" name="external_link_name" />
					<input type="button" class="button" id="add_external" value="Add External Link" />
				</div>
			</div>
		</div>
	</div>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>