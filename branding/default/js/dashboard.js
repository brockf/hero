$(document).ready(function () {
	// update live activity feed every 20 seconds
	update_activity();
	setInterval("update_activity()", 20000);
	
	// stats & dates
	$('input[name="date_select"]').click(function() {
		// we may be getting this via a <li> click
		$(this).attr('checked','checked');
		
		// set to active
		$('div#date_selector li').removeClass('active');
		$(this).parent().addClass('active');
		
		// show these stats
		$('span.stat').hide();
		$('span.stat.' + $(this).val()).fadeIn(250).css('display','block');
		
		// show these charts
		$('span.chart').hide();
		$('span.chart.' + $(this).val()).fadeIn(250).css('display','block');
	});
	
	// allow them to click the entire line
	$('div#date_selector li span').click(function() {
		$(this).siblings('input').trigger('click');
	});
	
	// initiate sparkline, then hide
	$('span.chart').sparkline( 'html', { height: '25px', width : '125px', lineColor : '#12727b', fillColor : '#79d6df', spotColor : false } ).hide(); 
		
	// trigger initial stats
	$('input[value="week"]').trigger('click').attr('checked','checked');
});

function update_activity () {
	var base_url = $('#base_url').html();
	var site_url = $('#site_url').html();
	
	var update_status = $('div#update_status');
	var activity_box = $('ul#activity_list');
	
	update_status.html('<img src="' + site_url + 'branding/default/images/loading.gif" alt="Loading..." />');
	
	$.post(base_url + 'dashboard/activity', function (response) {
		activity_box.html(response);	
	});
	
	$.post(base_url + 'dashboard/activity_time', function (response) {
		update_status.html(response);
	});
}