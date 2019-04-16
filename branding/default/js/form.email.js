$(document).ready(function () {
	$('input#add_param').click(function() {
		$('li.no_params').hide();
		
		var param_options = $('select#parameter_options').html();
		var operator_options = $('select#operator_options').html();
		
		$(this).parent().before('<li><select class="param" name="param[]">' + param_options + '</select>&nbsp;&nbsp;<select name="operator[]">' + operator_options + '</select>&nbsp;&nbsp;' + '<input type="text" class="text value" name="param_value[]" />&nbsp;&nbsp;&nbsp;(<a class="delete_param" href="#">remove</a>)</li>');
		
		bind_params();
	});	
	
	bind_params();
});

function bind_params () {
	$('select.param').change(function() {
		if ($(this).val() == 'product') {
			var product_options = $('select#product_options').html();
			$(this).siblings('.value').replaceWith('<select name="param_value[]" class="value">' + product_options + '</select>');
		}
		else if ($(this).val() == 'subscription_plan') {
			var plan_options = $('select#plan_options').html();
			$(this).siblings('.value').replaceWith('<select name="param_value[]" class="value">' + plan_options + '</select>');
		}
		else {
			$(this).siblings('.value').replaceWith('<input type="text" class="value text" name="param_value[]" />');
		}
	});
	
	$('a.delete_param').click(function() {
		$(this).parent().remove();
		
		// are we out of params?
		if ($('select.param').length == 0) {
			$('li.no_params').show();
		}
		
		return false;
	});
}