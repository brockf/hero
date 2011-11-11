$(document).ready(function(){

	/* Add a New Row */
	$('input#add_value').click(function () {
		var count = $('input.values').size() + 1;
	
		$(this).parent().before('<li><label>Value #' + count + '</label><input type="text" name="option[]" class="values mark_empty text" style="width:16em" rel="Label" placeholder="Label" />&nbsp;&nbsp;<input type="text" name="price[]" rel="Price (optional)" style="margin: 0" placeholder="Price(optional)" /> <input type="button" id="" class="button delete-option" style="margin-left: 30px;" value="- Delete Value" /></li>');
			
		return false;
	});
	
	/* Delete Row */
	$('.delete-option').live('click', function(e){
		e.preventDefault();

		$(this).parent('li').remove();
	});

});