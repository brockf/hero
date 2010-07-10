$(document).ready(function() {
	var base_url = $('div#base_url').html();
	
	// hover
	$('table.links td').hover(function () {
		$(this).parent().toggleClass('hover');
	});
	
	$('input#add_menu').click(function () {
		window.location = base_url + 'menu_manager/create';
	});
	
	// on load, get current nav items
	$('div#list_items_wrapper').html('<img src="' + base_url + '../branding/default/images/refreshing.gif" alt="loading..." title="loading.." />');
	
	$.get(base_url + 'menu_manager/get_links', function(data) {
		  $('div#list_items_wrapper').html(data);
		  
		  bind_remove_links();
	});
	
	// add item to menu
	$('input.add_link').click(function () {
		var base_url = $('div#base_url').html();
	
		$('div#list_items_wrapper').html('<img src="' + base_url + '../branding/default/images/refreshing.gif" alt="loading..." title="loading.." />');
		
		$.post(base_url + 'menu_manager/add_link', { code : $(this).attr('rel') }, function(data) {
		  $('div#list_items_wrapper').html(data);
		  bind_remove_links();
		});
		
		// get rid of this row
		$(this).parent().parent().remove();
	});
	
	// add external link
	$('input#add_external').click(function () {
		var base_url = $('div#base_url').html();
		
		// validate
		if ($('#external_link').val() == '' || $('#external_link').hasClass('highlight_empty')) {
			notice_error('A URL is required for external links.');
			return false;
		}
		if ($('#external_link_name').val() == '' || $('#external_link_name').hasClass('highlight_empty')) {
			notice_error('Display text is required for external links.');
			return false;
		}
	
		$('div#list_items_wrapper').html('<img src="' + base_url + '../branding/default/images/refreshing.gif" alt="loading..." title="loading.." />');
		
		$.post(base_url + 'menu_manager/add_link', { code : 'external[|]0[|]' + $('#external_link').val() + '[|]' + $('#external_link_name').val() }, function(data) {
		  $('div#list_items_wrapper').html(data);
		  bind_remove_links();
		});
		
		$('#external_link').val('');
		$('#external_link_name').val('');
	});						
});

function bind_remove_links () {
	var base_url = $('div#base_url').html();
	
	// remove link
	$('input.remove_link').click(function () {
		$('div#list_items_wrapper').html('<img src="' + base_url + '../branding/default/images/refreshing.gif" alt="loading..." title="loading.." />');
	
		$.post(base_url + 'menu_manager/remove_link', { menu_link_id : $(this).parent().parent().attr('rel') }, function(data) {
			  $('div#list_items_wrapper').html(data);
			  
			  bind_remove_links();
			});
	});
}