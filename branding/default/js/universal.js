$(document).ready(function() {
	// notices
	$('#notices div').animate({opacity: 1.0},4000).fadeOut('slow');
	$(window).scroll(function() {
	  $('#notices div').animate({top:$(window).scrollTop()+5+"px" },{queue: false, duration: 0});
	});
	
	// menu
	
	// click functions
	$('div#navigation ul a.parent').click(function() {
		// clear the slate
		$('div#navigation ul.children').hide();
		$('div#nav_children').animate({height: '8px'}, 100);
		
		if ($(this).parent().children('ul.children').length != 0) {
			$('div#nav_children').animate({height: '40px'}, 100);
			$(this).parent().children('ul.children').slideDown(100);
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
	
	$('#dataset_form tr.filters input.text').each(function () {
		$(this).addClass('mark_empty');
		$(this).attr('rel','filter results');
	});
	
	if (typeof datePicker == 'function') {
		Date.format = 'yyyy-mm-dd';
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
	
	// universal forms
	$('form.form').submit(function() {
		var errors_in_form = false;
		
		// check for empty required fields
		var field_names = '';
		$('.required').each(function() {
			// radio button mod
			if ($(this).attr('type') == 'radio' && $('input[name=\''+$(this).attr('name')+'\']:checked').length == 0) {
				field_label = $('label[for="'+$(this).attr('id')+'"]').text();
				// adds the label contents to the list of required fields
				if (field_names.indexOf(field_label) == -1) {
					field_names = field_names + '"'+field_label + '", ';
				}
				errors_in_form = true;
			}
			if ($(this).attr('type') != 'radio' && ($(this).val() == '' || $(this).hasClass('emptyfield'))) {
				field_label = $('label[for="'+$(this).attr('id')+'"]').text();
				// adds the label contents to the list of required fields
				field_names = field_names + '"'+field_label + '", ';
				errors_in_form = true;
			}
		});
		
		if (field_names != '') {
			field_names = rtrim(field_names,', '); // trim commas
			form_error('Required fields are empty: '+field_names+'.');
			return false;
		}
		
		// validate emails
		$('.email').each(function() {
			if ($(this).val() != '' && !isValidEmail($(this).val())) {
				field_label = $('label[for="'+$(this).attr('id')+'"]').text();
				form_error('"'+field_label + '" must be a valid email address.');
				errors_in_form = true;
				return false;
			}
		});
		
		// validate input.number fields
		$('input.number').each(function() {
			if ($(this).val() != '' && !isNumeric($(this).val())) {
				field_label = $('label[for="'+$(this).attr('id')+'"]').text();
				form_error('"'+field_label + '" must be in valid numeric format.');
				errors_in_form = true;
			}
		});
		
		if (errors_in_form == true) {
			return false;
		}
	});
	
	// mark empty fields with text and a class
	MarkEmpty();
});

// form functions

function form_error(message) {
	$('#notices').append('<div class="error">'+message+'</div>');
	$('#notices div').each(function () {
		$(this).animate({top:$(window).scrollTop()+5+"px" },{queue: false, duration: 0});
		$(this).animate({opacity: 1.0},4000).fadeOut('slow');
	});
	
	$(window).scroll(function() {
	  $('#notices div').animate({top:$(window).scrollTop()+5+"px" },{queue: false, duration: 0});
	});
}

function rtrim ( str, charlist ) {
    charlist = !charlist ? ' \\s\u00A0' : (charlist+'').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\\$1');
    var re = new RegExp('[' + charlist + ']+$', 'g');    return (str+'').replace(re, '');
}

function isValidEmail(str) {
   return (str.indexOf(".") > 2) && (str.indexOf("@") > 0);
}	

function isNumeric(sText) {
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;
 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
   { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
      {
         IsNumber = false;
      }
   }
   return IsNumber;
}

function MarkEmpty () {
	$('.mark_empty').each(function () {
		var field_name = $(this).attr('rel');
		
		if ($(this).val() == '') {
			$(this).val(field_name);
			$(this).addClass('emptyfield');
		}
		else if ($(this).val() == field_name) {
			$(this).addClass('emptyfield');
		}
		
		$(this).focus(function () {
			if ($(this).val() == field_name) {
				$(this).removeClass('emptyfield');
				$(this).val('');
			}
		});
		
		$(this).blur(function () {
			if ($(this).val() == '') {
				$(this).val(field_name);
				$(this).addClass('emptyfield');
			}
		});
	});
}