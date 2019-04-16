// IE doesn't natively support trim()...
if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, ''); 
  }
}

$(document).ready(function () {
	$('div.setting_group a.cat').click(function () {
		$('table.settings').hide();
		
		$('div.setting_group a.cat').removeClass('open');

		$(this).addClass('open');
		$(this).parent().children('table.settings').slideDown();
		
		return false;
	});

	$('a.text').click(function () {
		if ($(this).hasClass('edit')) {
			var post_url = $('#current_url').html() + '/save';
		
			// get value td object
			var value_td = $(this).parent().parent().children('td.value');
			
			// setting name
			var setting_name = $(this).parent().parent().children('td.name').html();
			
			var current_value = value_td.html().trim();
			value_td.html('<form class="validate form mark_empty" rel="new setting" method="post" action="' + post_url + '"><input type="hidden" name="name" value="' + setting_name + '" /><input class="text required" style="width:100%" name="value" value="' + current_value + '" /></form>');
			
			$(this).removeClass('edit');
			$(this).addClass('save');
			$(this).html('save');
			
			return false;
		}
		else if ($(this).hasClass('save')) {
			// get form object
			var form = $(this).parent().parent().find('form');
			
			// get value td object
			var value_td = $(this).parent().parent().children('td.value');
			
			var post_url = form.attr('action');
			
			$.post(post_url, form.serialize(), function (data) {
				value_td.html(data);
				
				notice_ok('Setting saved successfully.');
			});
			
			$(this).removeClass('save');
			$(this).addClass('edit');
			$(this).html('edit');
			
			return false;
		}
	});
	
	$('a.textarea').click(function () {
		if ($(this).hasClass('edit')) {
			var post_url = $('#current_url').html() + '/save';
			
			// get value td object
			var value_td = $(this).parent().parent().children('td.value');
		
			// setting name
			var setting_name = $(this).parent().parent().children('td.name').html();
			
			var current_value = $('textarea#' + setting_name).val().trim();
			
			value_td.html('<form class="validate form mark_empty" rel="new setting" action="' + post_url + '"><input type="hidden" name="name" value="' + setting_name + '" /><textarea class="text required" style="width:100%" name="value">' + current_value + '</textarea></form>');
			
			$(this).removeClass('edit');
			$(this).addClass('save');
			$(this).html('save');
			
			return false;
		}
		else if ($(this).hasClass('save')) {
			// get form object
			var form = $(this).parent().parent().find('form');
			
			// get value td object
			var value_td = $(this).parent().parent().children('td.value');
			
			var post_url = form.attr('action');
			
			// setting name
			var setting_name = $(this).parent().parent().children('td.name').html();
			
			$.post(post_url, form.serialize(), function (data) {
				value_td.html(constrain(data, 250) + '<textarea style="display:none" class="value" name="'+setting_name+'" id="' + setting_name + '">' + data + '</textarea>');
				
				notice_ok('Setting saved successfully.');
			});
			
			$(this).removeClass('save');
			$(this).addClass('edit');
			$(this).html('edit');
			
			return false;
		}
	});
	
	$('a.toggle').click(function () {
		var post_url = $('#current_url').html() + '/save_toggle';
		
		var setting_name = $(this).parent().parent().children('td.name').html();
		
		var value_td = $(this).parent().parent().children('td.value');
		
		$.post(post_url, { 'name' : setting_name }, function (data) {
			value_td.html(data);
			
			notice_ok('Setting saved successfully.');
		});
		
		return false;
	});
	
	function constrain(str,n){ 
	  if(str.length > n){  
	    var s = str.substr(0, n);
	    var words = s.split(' '); 
	    words[words.length-1] = '';
	    str = words.join(' ') + '&hellip;'
	  }
	return str;
	}
});