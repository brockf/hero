$(document).ready(function() {
	// notices
	$('#notices div').animate({opacity: 1.0},4000).fadeOut('slow');
	$(window).scroll(function() {
	  $('#notices div').animate({top:$(window).scrollTop()+5+"px" },{queue: false, duration: 0});
	});
	
	// buttons
	$('input.button').hover(function () {
		$(this).toggleClass('hover');
	});
	
	// tooltips
	$(".tooltip").addClass('tip_top').tipTip();
	
	// menu
	$(document).click(function() {
		$('div#navigation ul.children').hide();
	});
	
	$('div#navigation ul a.parent').click(function() {
		$('div#navigation ul.children').hide();
		
		if ($(this).parent().children('ul.children').length != 0) {
			$(this).parent().children('ul.children').slideDown(100);
			return false;
		}
	});
	
	// auto-complete URL paths
	// set this data to track whether we should still be automating the URL path or not
	$('input#url_path').data('automate','1');
	
	// turn off auto URL path building if we have touched the URL path even once
	$('input#url_path').click(function () {
		$('input#url_path').data('automate','0');
	});
	
	// automate the URL when we are typing a title
	if ($('input#url_path').length > 0 && $('input#title').length > 0) {
		$('input#title').keyup(function () {
			var url_path_field = $('input#url_path');
			if (url_path_field.data('automate') == '1') {
			   if ($('input#base_url').length > 0) {
			   	  // we have a base URL
			   	  var url = $('input#base_url').val();
			   }
			   else {
				  var url = '';
		       }
			   
			   url += $('input#title').val().toLowerCase();
			   
			   url = url.replace(/\s+/g,'_');
			   url = url.replace(/<(.*?)>/g, '');
			   url = url.replace(/\/{2,10}/g,'');
			   url = url.replace(/[^a-z0-9\/\-_\.]/ig,'');
			   
			   if (url.length > 0) {
				   // remove mark_empty class if it's there
				   url_path_field.removeClass('highlight_empty');
				   url_path_field.val(url);
			   }
			}
		});
	}
	
	/* DATASET LIBRARY ACCOMPANYING JAVASCRIPT */
	
	// sorting
	$('a.sort_column').click(function (e) {
		e.preventDefault();
		var column = $(this).attr('rel');
		$('input#sort_column').val(column);
		
		var direction = $(this).attr('direction');
		$('input#sort_dir').val(direction);
		
		$('form#dataset_form').submit();
	});
	
	// delete confirmations
	$('input[value="Delete"]').click(function() { 
		var confirmed = confirm('Are you sure you want to do this?');
		
		if (!confirmed) {
			$('input.action_items').attr('checked',false);
			$('input.action_items').parent().parent().removeClass('selected');
			return false;
		}
	});
	
	// table row colours
	$('table.dataset tr:even').addClass('odd');
	
	// table row mouseovers
	$('table.dataset tbody tr').hover(function() {
		if (!$(this).hasClass('selected') && !$(this).hasClass('filters')) {
			$(this).addClass('hovered');
		}
	}, function() {
		$(this).removeClass('hovered');
	});
	
	// handle row clicks/checkbox clicks
	$('table.dataset tbody tr').click(function(event) {
		if (event.target.type !== 'checkbox') {
			$(this).find('input.action_items').each(function () {
				if ($(this).is(':checked')) {
					$(this).parent().parent().removeClass('selected');
					$(this).attr('checked',false);
					return false;
				}
				else {
					$(this).parent().parent().addClass('selected');
					$(this).attr('checked','checked');
					return false;
				}
			});
		}
		else {
			$(this).find('input.action_items').each(function () {
				if ($(this).is(':checked')) {
					$(this).parent().parent().addClass('selected');
				}
				else {
					$(this).parent().parent().removeClass('selected');
				}
			});
		}
	});
	
	$('table.dataset #check_all').click(function() {
		if ($(this).is(':checked')) {
			$('input.action_items').each(function () {
				$(this).parent().parent().addClass('selected');
				$(this).attr('checked','checked');
			});
		}
		else {
			$('input.action_items').each(function () {
				$(this).parent().parent().removeClass('selected');
				$(this).attr('checked',false);
			});
		}
	});
	
	// we may have an embedded drop-down...
	/*
		example:
		
		<select name="action"><!-- options here --></select>
		<input type="hidden" name="action_id" value="" />
		<input type="submit" class="action button" value="Go" />
	*/
	$('form#dataset_form input.action').click(function () {
		var id = $(this).siblings('input[name="action_id"]').val();
		var action = $(this).siblings('select[name="action"]').val();
		
		if (action != '' && action != false) {
			var form_url = $('#site_url').html() + $(this).attr('rel') + '/' + action + '/' + id;
			
			$('form#dataset_form').attr("ACTION",form_url);
			$('form#dataset_form').submit();
		}
		else {
			return false;
		}
	});
	
	// mark empty fields with the mark_empty script
	$('form#dataset_form tr.filters input.text').not('.datepick').each(function () {
		$(this).addClass('mark_empty');
		$(this).attr('rel','filter results');
	});
	
	// filters
	
	if (typeof $.fn.datePicker == 'function') {
		Date.format = 'yyyy-mm-dd';
		// general datepick fields
		$('input.datepick').datePicker({clickInput:true,startDate:'2009-01-01'});
		
		$('form#dataset_form input.date_start').datePicker({clickInput:true,startDate:'2009-01-01'});
		$('form#dataset_form input.date_end').datePicker({clickInput:true,startDate:'2009-01-01'});
		
		$('form#dataset_form input.date_start').bind(
			'dpClosed',
			function(e, selectedDates)
			{
				var d = selectedDates[0];
				if (d) {
					d = new Date(d);
					$('form#dataset_form input.date_end').dpSetStartDate(d.addDays(1).asString());
				}
			}
			
		);
		$('form#dataset_form input.date_end').bind(
			'dpClosed',
			function(e, selectedDates)
			{
				var d = selectedDates[0];
				if (d) {
					d = new Date(d);
					$('form#dataset_form input.date_start').dpSetEndDate(d.addDays(-1).asString());
				}
			}
		);
	}
	
	// what happens when we click reset?
	$('input#reset_filters').click(function () {
		window.location.href = $(this).parents('form#dataset_form').attr('rel');
	});
	
	$('form#dataset_form').submit(function () {
		if ($('input#submit_ready').val() == 'true') {
			return true;
		}
		
		if ($(this).attr('rel') != $(this).attr('action')) {
			// we have customized the form action, likely with an embedded options drop down
			// none of this applies
			return true;
		}
		
		// clear "start date" and "end date" from date fields if empty
		$(this).find('input.text').each(function() {
			if ($(this).hasClass('highlight_empty')) {
				$(this).val('');
			}
		});
	
		var serialized_filters = $(this).find('tr.filters input.text, tr.filters select').serialize();
		
		// set form object
		var this_form = $(this);
		
		$.post($('#base_url').html()+'dataset/prep_filters', { filters: serialized_filters },
		  function(data){
		  	// set "filters" input as the serialized filters
		  	this_form.find('input#filters').val(data);
		  	this_form.find('input#submit_ready').val('true');
		  	// now we submit!
		  	this_form.submit();
		  });
		return false;
	});
	
	$('input#dataset_export_button').click(function () {
		if ($('input#submit_ready').val() == 'true') {
			return true;
		}
		
		// set form object
		var this_form = $(this).parents('form#dataset_form');
		
		var serialized_filters = this_form.find('tr.filters input.text, tr.filters select').serialize();
		
		$.post($('#base_url').html()+'dataset/prep_filters', { filters: serialized_filters },
		  function(data){
		    this_form.find('input#filters').val(data);
		    this_form.find('input#export').val('csv');
		    this_form.find('input#submit_ready').val('true');
		    
		    // no we submit!
		    this_form.submit();
		  });
		return false;
	});
	
	$('input.action_button').click(function () {
		var serialized_items = $('form#dataset_form input.action_items').serialize();
		
		if (serialized_items != '') {	
			var link = $(this).attr('rel');
			var return_link = $('#current_url').html();
			
			$.post($('#base_url').html()+'dataset/prep_actions', { items: serialized_items, return_url: return_link },
			  function(data){
			    window.location.href = link+'/'+data;
			  });
		}
		return false;
	});
});

/* notices functions */
function notice_error (message) {
	$('#notices').append('<div class="error">'+message+'</div>');
	notice_animate();
}

function notice_ok (message) {
	$('#notices').append('<div class="notice">'+message+'</div>');
	notice_animate();
}

function notice_animate () {
	$('#notices div').each(function () {
		$(this).animate({top:$(window).scrollTop()+5+"px" },{queue: false, duration: 0});
		$(this).animate({opacity: 1.0},4000).fadeOut('slow');
	});
	
	$(window).scroll(function() {
	  $('#notices div').animate({top:$(window).scrollTop()+5+"px" },{queue: false, duration: 0});
	});
}