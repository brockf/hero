$(document).ready(function () {
	// add numeric class
	$('#weight, #price, #inventory').addClass('number');
	
	// add membership tier rows
	$('input#add_membership_tier').click(function () {
		var newline = $(this).parent().prev().html();
		
		$(this).parent().before('<li>' + newline + '</li>');
		
		MarkEmpty();
		
		return false;
	});
	
	// add product option
	bind_option_remove_links();
	
	$('input#add_product_option').click(function () {
		$('div#add_option_dialog').modal();
		
		return false;
	});
	
	$('input#add_value').click(function () {
		var count = $('input.values').size() + 1;
	
		$(this).parent().before('<li><label>Value #' + count + '</label><input type="text" name="value' + count + '" class="values mark_empty text" style="width:130px" rel="Label" />&nbsp;&nbsp;<input type="text" name="price' + count + '" class="prices mark_empty text" style="width: 100px" rel="Price (optional)" /></li>');
			
		return false;
	});
	
	$('form#use_existing_option_dialog_form').submit(function () {
		var select = $(this).find('select[name="option"]');
		var option_id = select.val();
		var option_name = select.find('option[value="' + option_id + '"]').text();
		
		// add to list
		$('ul#product_options').append('<li><input type="hidden" name="options[]" value="' + option_id + '" />' + option_name + ' (<a href="#" class="remove_option">remove</a>)');
		
		// bind remove links
		bind_option_remove_links();
		
		return false;
	});
	
	$('form#add_option_dialog_form').submit(function () {
		$(this).find('.loading').show();
		
		$.post( 
			$(this).attr('ACTION'),
			$(this).serialize(), 
				function(data) { 
					$('form#add_option_dialog_form .loading').hide();
					
					var response_span = $('form#add_option_dialog_form span.response');
					
					if (data.error) {
						response_span.html(data.error);
					}
					else {
						// add to list
						$('ul#product_options').append('<li><input type="hidden" name="options[]" value="' + data.option_id + '" />' + data.option_name + ' (<a href="#" class="remove_option">remove</a>)');
						
						// bind remove links
						bind_option_remove_links();
						
						$('form#add_option_dialog_form input[name="name"]').val('');
						$('form#add_option_dialog_form input.values').parent().remove();
						$('input#add_value').click(); // adds an initial starting value again
						
						// close dialog
						$.modal.close();
					}
				},
			"json"
		);  
		
		return false;
	});
	
	// end product option
	
	InventoryOptions();
	FileOptions();
	MemberTiers();
	GroupOptions();
	
	$('#track_inventory').click(function () {
		InventoryOptions();
	});
	
	$('#download').click(function () {
		FileOptions();
	});
	
	$('#membership_tiers').click(function () {
		MemberTiers();
	});
	$('#group_move').click(function () {
		GroupOptions();
	});
	
	$('a#refresh_files').click(function () {
		var base_url = $('#base_url').html();
	
		var old_html = $(this).html();
		
		// display loading image
		$(this).html('<img src="' + base_url + '../branding/default/images/refreshing.gif" alt="refreshing..." />');
		
		$.getJSON(base_url + 'store/get_product_files', function(data) {
			var file_options = data;
  			
  			var file_options_options = '';
  			$.each(file_options, function (intIndex, objValue) {
				file_options_options = file_options_options + '<option value="' + objValue + '">' + objValue + '</option>';
			});
			
			$('#file_uploaded').html(file_options_options);
		});
		
		// clear loading iamge
		$(this).html(old_html);
		
		return false;
	});
	

});

function InventoryOptions () {
	if ($('#track_inventory:checked').length > 0) {
		$('#row_inventory').show();
		$('#row_inventory input').addClass('required');
		$('#row_inventory_allow_at_zero').show();
	}
	else {
		$('#row_inventory').hide();
		$('#row_inventory input').removeClass('required');
		$('#row_inventory_allow_at_zero').hide();
	}
}

function FileOptions() {
	if ($('#download:checked').length > 0) {
		$('li.file_options').show();
	}
	else {
		$('li.file_options').hide();
	}
}

function MemberTiers () {
	if ($('#membership_tiers:checked').length > 0) {
		$('li.membership_tiers').show();
	}
	else {
		$('li.membership_tiers').hide();
	}
}

function GroupOptions () {
	if ($('#group_move:checked').length > 0) {
		$('li.group_move_options').show();
	}
	else {
		$('li.group_move_options').hide();
	}
}

function bind_option_remove_links () {
	$('a.remove_option').click(function() {
		$(this).parent().remove();
		
		return false;
	});
}