$(document).ready(function () {

	bind_recipients();
	
	$('input#member_search').keyup(function () {
		var query = $(this).val();
		var base_url = $('#base_url').html();
		
		if (query == '') {
			return false;
		}
		
		// check query length
		if (query.length < 3) {
			return false;
		}
		
		// show loading
		$('ul.members').html('<li>Loading...</li>');
		
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
});

function bind_recipients () {
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
		
		if ($('ul.final_recipients li:visible').length == 0) {
			$('ul.final_recipients li.empty').show();
		}
		
		return false;
	});
}