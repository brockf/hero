$(document).ready(function () {
	// bind add
	$('ul.recipients input.member').live('click', function() {
		$('ul.final_recipients').append('<li>Member: ' + $(this).attr('rel') + ' (<a rel="' + $(this).attr('rel') + '" href="#" class="remove member">remove</a>) <input type="hidden" name="recipient_members[]" value="' + $(this).val() + '" /></li>');
		$(this).parent().remove();
		
		$('ul.final_recipients li.empty').hide();
	});
	
	$('ul.recipients input.group').live('click', function() {
		$('ul.final_recipients').append('<li>Member Group: ' + $(this).attr('rel') + ' (<a rel="' + $(this).attr('rel') + '" href="#" class="remove group">remove</a>) <input type="hidden" name="recipient_groups[]" value="' + $(this).val() + '" /></li>');
		$(this).parent().remove();
		
		$('ul.final_recipients li.empty').hide();
	});
	
	$('input#add_email').click(function () {
		var email_address = $('input[name="email_address"]').val();
		
		if (email_address == '') {
			return false;
		}
	
		$('ul.final_recipients').append('<li>Email: ' + email_address + ' (<a rel="' + email_address + '" href="#" class="remove email">remove</a>) <input type="hidden" name="recipient_emails[]" value="' + email_address + '" /></li>');
		$(this).find('input[name="email_address"]').val('');
		
		$('ul.final_recipients li.empty').hide();
		
		return false;
	});
	
	// bind remove
	$('a.remove').live('click',function() {
		if ($(this).hasClass('member')) {
			var id = $(this).parent().children('input').val();
			$('ul.members').prepend('<li><input type="checkbox" class="member" rel="' + $(this).attr('rel') + '" value="' + id + '" /> ' + $(this).attr('rel') + '</li>');
			
			$(this).parent().remove();
		}
		else if ($(this).hasClass('group')) {
			var id = $(this).parent().children('input').val();
			$('ul.groups').prepend('<li><input type="checkbox" class="group" rel="' + $(this).attr('rel') + '" value="' + id + '" /> ' + $(this).attr('rel') + '</li>');
			
			$(this).parent().remove();
		}
		else if ($(this).hasClass('email')) {
			$(this).parent().remove();
		}
		
		if ($('ul.final_recipients li:visible').length == 0) {
			$('ul.final_recipients li.empty').show();
		}
		
		return false;
	});
	
	$('input#member_search').keyup(function () {
		var query = $(this).val();
		var base_url = $('#base_url').html();
		var site_url = $('#site_url').html();
		
		if (query == '') {
			return false;
		}
		
		// check query length
		if (query.length < 3) {
			return false;
		}
		
		// show loading
		$('ul.members').html('<li><img src="' + site_url + 'branding/default/images/loading.gif" alt="Loading..." /></li>');
		
		// set timeout (we do this after a 100ms delay from typing)
		if (typeof(window['timeout']) != "undefined") {
			window.clearTimeout(timeout);
		}
		timeout = window.setTimeout(function () {
			// do query
			$.post(base_url + 'emails/member_search', { keyword : query }, function(data) {
				var members = data;
				
				var ul_members = '';
				$.each(members, function (intIndex, objValue) {
					intIndex = intIndex.replace('key_','');
					ul_members += '<li><input type="checkbox" name="recipient" class="member" rel="' + objValue + '" value="' + intIndex + '" /> ' + objValue + '</li>';
				});
				
				// load results into list
				$('ul.members').html(ul_members);
				
				// is it empty?
				if ($('ul.members li').length == 0) {
					$('ul.members').html('<li>No results.</li>');
				}
			},
			'json');
		}, 100);
	});
	
	// save as template
	if ($('input[name="new_template"]:checked').length > 0) {
		$('input[name="new_template_name"]').show();
	}
	else {
		$('input[name="new_template_name"]').hide();
	}
	
	$('input[name="new_template"]').click(function () {
		if ($('input[name="new_template"]:checked').length > 0) {
			$('input[name="new_template_name"]').show();
		}
		else {
			$('input[name="new_template_name"]').hide();
		}
	});
	
	$('select[name="templates"]').change(function () {
		if ($(this).val() != '') {
			var base_url = $('#base_url').html();
			var template_id = $(this).val();
			
			$(this).after('<span id="load_template_loading">Loading...</span>');
			
			$.post(base_url + 'emails/load_template_subject', { "template_id" : template_id }, function(data) {
				$('input[name="subject"]').val(data);
				$('span#load_template_loading').remove();
			});
			
			$.post(base_url + 'emails/load_template_body', { "template_id" : template_id }, function(data) {
				$('textarea[name="body"]').val(data);
				$('span#load_template_loading').remove();
			});
		}
	});
});