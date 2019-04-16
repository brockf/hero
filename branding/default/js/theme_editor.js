$(document).ready(function () {
	var base_url = $('#base_url').html();
	var site_url = $('#site_url').html();
	
	$('select[name="theme_directory"]').change(function () {
		var file_list = $('div#file_list');
		var selected_theme = $('select[name="theme_directory"]').val();
		
		// loading graphic
		file_list.html('<p style="text-align:center"><img src="' + site_url + 'branding/default/images/loading.gif" alt="Loading..." /></p>');
		
		$.post(base_url + 'theme/file_list', { theme : selected_theme }, function (response) {
			file_list.html(response);
			
			// make all li items clickable
			$('div#file_list li').each(function () {
				if ($(this).hasClass('editable')) {
					var value = $(this).html();
					$(this).html('<a class="file_link tooltip editable" title="edit this file" href="javascript:void(0)">' + value + '</a>');
				}
			});
			
			// collapse folders
			$('li.folder ul').hide();
			
			// show folder when clicked
			$('li.folder a.folder_link').click(function () {
				var parent = $(this).parent();
				
				if (parent.hasClass('open')) {
					parent.children('ul').slideUp();
					parent.removeClass('open');
				}
				else {
					parent.children('ul').slideDown();
					parent.addClass('open');
				}
				
				return false;
			});
			
			// trigger file editing
			$('a.editable').click(function() {
				// we may have temporarily unbound "editable" as a class (such as when selecting a frontpage)
				if ($(this).hasClass('editable')) {
					var filename = $(this).parent().attr('rel');
					
					create_editor(filename);
				}
					
				return false;
			});
			
			$('a.tooltip').tipTip();
		});
	});
	
	$('a#recheck_writable').click(function () {
		var filename = $('input#old_filename').val();
		var selected_theme = $('select[name="theme_directory"]').val();
		
		$('div#editor_loading').show();
		
		// check writability
		$.post(base_url + 'theme/check_writeable', { theme : selected_theme, file : filename }, function (response) {			
			$('div#editor_loading').hide();
			
			if (response == 'fail') {
				$('div#not_writable').slideDown();
			}
			else {
				$('div#not_writable').hide();
				$('div#success_note').html('Success!  The file is now writeable.').show().delay(1000).slideUp();
			}
		});
		
		return false;
	});
	
	// save file
	$('input#save_file').click(function () {
		var filename = $('input#old_filename').val();
		var this_new_filename = $('input#current_file').val();
		var selected_theme = $('select[name="theme_directory"]').val();
		var this_contents = $('textarea#editor_body').val();
		
		$('div#editor_loading').show();
		
		$.post(base_url + 'theme/save_file', { new_filename : this_new_filename, contents : this_contents, theme : selected_theme, file : filename }, function (response) {
			$('div#editor_loading').hide();
			
			if (response == 'saved') {
				$('div#success_note').html('File saved successfully.').show().delay(1000).slideUp();
				
				// reload files if new filename
				if (this_new_filename != filename) {
					// also, set old_filename to the current filename
					$('input#old_filename').val(this_new_filename);
					$('select[name="theme_directory"]').trigger('change');
				}
			}
			else {
				alert('File save failed!  Your changes were not saved.  Please check the file permissions.\n\nAlso, your file must be located in your /themes/ folder and be a non-executable filetype.');
			}
		});
		
		return false;
	});
	
	// delete file
	$('input#delete_file').click(function () {
		var confirmed = confirm('Are you sure you want to delete this file?');
		if (confirmed != true) {
			return false;
		}
	
		var filename = $('input#current_file').val();
		var selected_theme = $('select[name="theme_directory"]').val();
		
		$('div#editor_loading').show();
		
		$.post(base_url + 'theme/delete_file', { theme : selected_theme, file : filename }, function (response) {
			$('div#editor_loading').hide();
			
			if (response == 'deleted') {
				// get rid of the theme editor
				$('div#theme_editor').hide();
				$('div#empty_editor').show();
				
				// reload files if new filename
				$('select[name="theme_directory"]').trigger('change');
				
				return false;
			}
			else {
				alert('File deletion failed!  Please check the file permissions.');
			}
			
			return false;
		});
	});
	
	// new file
	$('input#new_file').click(function () {
		// prep folder options
		var options = '<option value="">/</option>';
		$('a.folder_link').each(function () {
			options += '<option value="' + $(this).attr('rel') + '">' + $(this).attr('rel') + '</option>';
		});
		
		$('select[name="new_file_path"]').html(options);
		$('input[name="new_file_name"]').val('');
		$('span#path_theme').html($('select[name="theme_directory"]').val());
		
		$('select[name="filetype"]').change(function() {
			$('input[name="new_file_name"]').val('.' + $(this).val()).focus();
		});
		
		// place .thtml by default
		$('select[name="filetype"]').trigger('change');
	
		$('div#new_file_dialog').modal( { persist : true } );
	});
	
	// create editor upon submission
	$('input#new_file_create').click(function() {
		var new_filename = $('select[name="new_file_path"]').val() + '/' + $('input[name="new_file_name"]').val();
		
		create_editor(new_filename, '');	
		
		$.modal.close();
	});
	
	// map url
	$('input#map_url').click(function () {
		var this_template = $('input#current_file').val();
		
		$('input[name="new_url"]').val('');
			
		$('div#existing_maps').html('<p style="text-align:center"><img src="' + site_url + 'branding/default/images/loading.gif" alt="Loading..." /></p>');
		
		// get existing mappings
		$.post(base_url + 'theme/get_maps', { template : this_template }, function (response) {
			$('div#existing_maps').html(response);
			
			$('a.remove_map').click(function() {
				$(this).parent().remove();
				$.post(base_url + 'theme/unmap', { link_id : $(this).attr('rel') } );
			});
		});
	
		$('div#map_url_dialog').modal();
	});
	
	// create link upon submission
	$('input#new_url_create').click(function() {
		var this_url = $('input[name="new_url"]').val();
		var this_template = $('input#current_file').val();
		var this_title = $('input[name="new_url_title"]').val();
	
		$.post(base_url + 'theme/map_url', { url : this_url, template : this_template, title : this_title }, function (response) {
			if (response == 'success') {
				$.modal.close();
			}
			else if (response == 'duplicate') {
				alert('This URL is already mapped to another template or resource.  Please choose a new URL.');
			}
			else {
				alert('There was an unidentified error trying to map this URL to this template.');
			}
		});	
	});
	
	// set frontpage
	$('input#set_frontpage').click(function() {
		if ($(this).data('setting_frontpage') == true) {
			$(this).data('setting_frontpage',false);
			$('div#set_frontpage_help').hide();
				
			// show all files again, and unbind this functionality with a nice reload
			$('select[name="theme_directory"]').trigger('change');
		}
		else {
			$(this).data('setting_frontpage',true);
			
			$('div#set_frontpage_help').slideDown();
			
			// remove all non template/folder links
			$('div#file_list ul').slideDown();
			$('div#file_list li').not('.folder').not('.template').hide();
			
			$('div#file_list li.template').addClass('possible_frontpage').children('a').removeClass('editable');
			
			$('li.possible_frontpage a').click(function () {
				$('input#set_frontpage').data('setting_frontpage',false);
				
				var old_tip = $('div#set_frontpage_help').html();
				
				$('div#set_frontpage_help').html('<p style="text-align:center"><img src="' + site_url + 'branding/default/images/loading.gif" alt="Loading..." /></p>');
				
				$.post(base_url + 'theme/set_frontpage', { template : $(this).parent().attr('rel') }, function (response) {
					$('div#set_frontpage_help').html(old_tip).hide();
					
					// show all files again, and unbind this functionality with a nice reload
					$('select[name="theme_directory"]').trigger('change');	
				});
				
				return false;
			});
		}
		
		return false;
	});
	
	// trigger tooltips
	$('a.tooltip').tipTip();
	
	// triggers upon load
	$('select[name="theme_directory"]').trigger('change');
});

function create_editor (filename, old_filename) {
	if (old_filename == null) {
		old_filename = filename;
	}
	
	filename = filename.replace(/^\//i,'');
	
	var base_url = $('#base_url').html();
	var site_url = $('#site_url').html();
	
	// scroll to top
	$(document).scrollTop(0);

	// hide/show elements
	$('div#empty_editor').hide();
	$('div#theme_editor').fadeIn(750);
	$('div#is_now_writable').hide();
	
	var selected_theme = $('select[name="theme_directory"]').val();
				
	$('input#current_file').val(filename);
	$('input#old_filename').val(old_filename);
	
	// show the map URL function?
	if (filename.indexOf('.thtml') > 0 || filename.indexOf('.txml') > 0) {
		$('input#map_url').show();
	}
	else {
		$('input#map_url').hide();
	}
	
	// load file into editor
	var editor_body = $('textarea#editor_body');
	editor_body.val('');
	
	if (old_filename != '') {
		// disable while we load
		$('div#editor_loading').show();
		
		$.post(base_url + 'theme/get_file', { theme : selected_theme, file : filename }, function (response) {
			$('div#editor_loading').hide();
			
			editor_body.val(response);
		});
		
		$.post(base_url + 'theme/check_writeable', { theme : selected_theme, file : filename }, function (response) {
			if (response == 'fail') {
				$('div#not_writable').slideDown();
			}
			else {
				$('div#not_writable').slideUp();
			}
		});
	}	
}