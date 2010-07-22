$(document).ready(function () {
	var box_count = $('div.container div.box').size();
	
	if (box_count == 1) {
		return FALSE;
	}
	else {
		var padding = box_count * 4; // 4px of padding for each element
		var width = (100 - padding) / box_count;
		
		count = 1;
		$('div.container div.box').each(function () {
			$(this).css('width',width + '%');
			$(this).css('float','left');
			
			if (count != box_count) {
				$(this).css('border-right','1px solid #ccc');
			}
			else {
				$(this).css('padding-right','1%');
			}
			
			count++;
		});
	}
});