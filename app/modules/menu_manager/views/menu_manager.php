<?=$this->load->view(branded_view('cp/header'), array('head_files' => '<link href="' . branded_include('css/menu_manager.css') . '" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="' . branded_include('js/jquery-ui-1.8.2.min.js') . '"></script>
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
					<?=$possible_links;?>
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
<div style="clear:both"></div>
<?=$this->load->view(branded_view('cp/footer'));?>