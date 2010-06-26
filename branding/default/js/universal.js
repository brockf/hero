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
	
	// menu
	
	// click functions
	$('div#navigation ul a.parent').click(function() {
		// clear the slate
		$('div#navigation ul.children').hide();
		
		if ($(this).parent().children('ul.children').length == 0) {
			$('div#nav_children').animate({height: '8px'}, 100);
		}	
		
		if ($(this).parent().children('ul.children').length != 0) {
			$('div#nav_children').animate({height: '40px'}, 100);
			$(this).parent().children('ul.children').slideDown(100);
			return false;
		}
	});
	
	// show active menu on load
	$('div#navigation ul a.active').each(function () {
		$('div#nav_children').animate({height: '40px'}, 100);
		$(this).parent().children('ul.children').slideDown(100);
	});
	
	// table row colours
	
	$('table.dataset tr:even').addClass('odd');
	
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
	
	// filters
	
	$('input#reset_filters').click(function () {
		window.location.href = $('#base_url').html()+$('#class').html()+'/'+$('#method').html()+'/'+$('#page').html();
	});
	
	$('#dataset_form tr.filters input.text').not('.datepick').each(function () {
		$(this).addClass('mark_empty');
		$(this).attr('rel','filter results');
	});
	
	if (typeof $.fn.datePicker == 'function') {
		Date.format = 'yyyy-mm-dd';
		// general datepick fields
		$('input.datepick').datePicker({clickInput:true,startDate:'2009-01-01'});
		
		$('#dataset_form input.date_start').datePicker({clickInput:true,startDate:'2009-01-01'});
		$('#dataset_form input.date_end').datePicker({clickInput:true,startDate:'2009-01-01'});
		
		$('#dataset_form input.date_start').bind(
			'dpClosed',
			function(e, selectedDates)
			{
				var d = selectedDates[0];
				if (d) {
					d = new Date(d);
					$('#dataset_form input.date_end').dpSetStartDate(d.addDays(1).asString());
				}
			}
			
		);
		$('#dataset_form input.date_end').bind(
			'dpClosed',
			function(e, selectedDates)
			{
				var d = selectedDates[0];
				if (d) {
					d = new Date(d);
					$('#dataset_form input.date_start').dpSetEndDate(d.addDays(-1).asString());
				}
			}
		);
	}
	
	$('#dataset_form').submit(function () {
		if ($(this).attr('rel') != $(this).attr('action')) {
			// we have customized the form action, likely with an embedded options drop down
			// none of this applies
			return true;
		}
	
		var serialized_filters = $('#dataset_form tr.filters input.text, tr.filters select').serialize();
		
		$.post($('#base_url').html()+'dataset/prep_filters', { filters: serialized_filters },
		  function(data){
		    window.location.href = $('#base_url').html()+$('#class').html()+'/'+$('#method').html()+'/'+data+'/'+$('#page').html();
		  });
		return false;
	});
	
	$('#dataset_export_button').click(function () {
		var serialized_filters = $('#dataset_form tr.filters input.text, tr.filters select').serialize();
		
		$.post($('#base_url').html()+'dataset/prep_filters', { filters: serialized_filters },
		  function(data){
		    window.location.href = $('#base_url').html()+$('#class').html()+'/'+$('#method').html()+'/'+data+'/'+$('#page').html()+'/export';
		  });
		return false;
	});
	
	$('input.action_button').click(function () {
		var serialized_items = $('#dataset_form input.action_items').serialize();
		
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