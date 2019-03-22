<?=$this->head_assets->stylesheet('css/menu_manager.css');?>
<?=$this->head_assets->javascript('js/jquery-ui-1.8.2.min.js');?>
<?=$this->head_assets->javascript('js/jquery.quicksearch.js');?>
<?=$this->head_assets->javascript('js/menu_manager.js');?>

<?=$this->load->view(branded_view('cp/header'));?>

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
				<h2><?=$title;?> <span><a href="<?php echo site_url('admincp/menu_manager/delete_menu/'. $active_id) ?>" onclick="return confirm('Are you sure you want to delete this menu?');">Delete Menu</a></span></h2>
				<div id="list_items_wrapper">
					
				</div>
			</div>
			<div id="link_creator">
				<h2>Add Link(s) to Menu&nbsp;&nbsp;<input id="items_search" name="items_search" class="mark_empty" rel="search link items" style="width: 250px" /></h2>
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