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
		  bind_link_sorting();
		  bind_link_editing();
		  bind_js_effects();
	});
	
	// add item to menu
	bind_add_link();
	
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
		
		$.post(base_url + 'menu_manager/add_link', { external : 'true', url : $('#external_link').val(), text : $('#external_link_name').val() }, function(data) {
		  $('div#list_items_wrapper').html(data);
		  bind_remove_links();
		  bind_link_sorting();
		  bind_link_editing();
		  bind_js_effects();
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
		$('div#link_creator_wrapper').html('<img src="' + base_url + '../branding/default/images/refreshing.gif" alt="loading..." title="loading.." />');
	
		$.post(base_url + 'menu_manager/remove_link', { menu_link_id : $(this).parent().parent().attr('rel') }, function(data) {
			  $('div#list_items_wrapper').html(data);
			  
			  bind_remove_links();
			  bind_link_sorting();
			  bind_link_editing();
			  bind_js_effects();
			  
			  $.post(base_url + 'menu_manager/show_possible_links', function(data) {
				  $('div#link_creator_wrapper').html(data);
				  
				  bind_add_link();
				});
			});
	});
}

function bind_js_effects () {
	$('ul.current_links li').hover(function () {
		$(this).toggleClass('no_hover');
	});
}

function bind_add_link () {
	// bind search
	$('input#items_search').quicksearch('table.links tbody tr');

	$('input.add_link').click(function () {
		var base_url = $('div#base_url').html();
	
		$('div#list_items_wrapper').html('<img src="' + base_url + '../branding/default/images/refreshing.gif" alt="loading..." title="loading.." />');
		
		$.post(base_url + 'menu_manager/add_link', { code : $(this).attr('rel') }, function(data) {
		  $('div#list_items_wrapper').html(data);
		  bind_remove_links();
		  bind_link_sorting();
		  bind_link_editing();
		  bind_js_effects();
		});
		
		// get rid of this row
		$(this).parent().parent().remove();
	});
}

function bind_link_sorting () {
	var base_url = $('#base_url').html();
	
	$("ul.current_links").sortable({ 
	    handle : '.handle', 
	    axis : 'y',
	    update : function () { 
	      var original_colour = $("ul.current_links li").css("background-color"); 
	      $("ul.current_links li").css({ 'background-color' : '#ddf8a9'});
	      var order = $('ul.current_links').sortable('serialize'); 
	      $.post(base_url + 'menu_manager/save_order', order, function (data) {
	      	$("ul.current_links li").animate({backgroundColor:original_colour},750)
	      
	        notice_ok('Link order saved successfully.');
	      });
	    } 
	  }); 
}

function bind_link_editing () {
	var base_url = $('#base_url').html();

	$('input.edit_link').click(function () {
		if ($(this).data('editing') == 'true') {
			var this_link_id = $(this).parent().parent().attr('rel');
			var this_text = $(this).parent().parent().find('input#text').val();
			var this_privileges = $(this).parent().parent().find('select#privileges').val();
			var this_external_url = $(this).parent().parent().find('input#external_url').val();
			var this_class = $(this).parent().parent().find('input#class').val();
			
			// update text
			$(this).parent().parent().children('span.text').html(this_text);
			
			// get rid of form
			$(this).parent().parent().children('div.editing').slideUp();
			
			$.post(base_url + 'menu_manager/edit_link', { 'link_id' : this_link_id, 'text' : this_text, 'privileges' : this_privileges, 'class' : this_class, 'external_url' : this_external_url } , function (data) {
		      	notice_ok('Link edited successfully.');
		      });
				
			$(this).removeClass('save_changes');
			$(this).attr('value','Edit');
			
			$(this).data('editing','false');
		}
		else {
			$(this).parent().parent().children('div.editing').slideDown(100);
			
			$(this).addClass('save_changes');
			$(this).attr('value','Save Changes');
		
			$(this).data('editing','true');
		}
	});
	
	$('input.manage_children').click(function () {
		var link_id = $(this).parent().parent().attr('rel');
		
		window.location = base_url + 'menu_manager/switch_parent/' + link_id;
	});
	
	$('input.button').hover(function () {
		$(this).toggleClass('hover');
	});
}