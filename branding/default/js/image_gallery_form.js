$(document).ready(function () {
	$('div.image_gallery_form input.button').click(function () {
		var file = $('div.image_gallery_form input[type="file"]:visible');
		
		if (file.val() != '') {
			var image_count = file.attr('rel');
			var input_name = file.attr('name');
			
			// hide no images text
			$('#no_images').hide();
			
			// hide this file input for a secret upload later
			file.hide();
			
			// increment image_count and put new file input
			var next_image_count = +image_count + 1;
			if (next_image_count >= 15) {
				file.after('<span>Maximum number of files uploaded</span>');
			}
			else {
				file.after('<input type="file" name="' + input_name + '" rel="' + next_image_count + '" />');
			}
			
			$('div.image_gallery_form div.images ul').append('<li rel="' + image_count + '">' + file.val() + ' (<a class="remove_image" href="#">remove</a>)</li>');
			
			BindRemove();
		}
		else {
			// no file in input
		}
	});
});

function BindRemove () {
	$('a.remove_image').click(function () {
		var parent_row = $(this).parent();
		// get image_number
		var image_number = parent_row.attr('rel');
		
		// get rid of input file box
		$('input[rel="' + image_number + '"]').remove();
		
		if (parent_row.siblings().length == 0) {
			// no more files
			$('#no_images').show();
		}
		
		// remove this filename from list
		$(this).parent().remove();
		
		return false;
	});
}