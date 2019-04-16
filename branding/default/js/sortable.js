$(document).ready(function(){
	$('ul.sortable li').each(function() {
		$(this).prepend('<a style="display:block;float: left" class="handle" href="#"><img src="' + $('#base_url').html() + '../branding/default/images/arrow.png" alt="Drag to Move" title="Drag to Move" /></a>');
	});

    $("ul.sortable").sortable({ 
	    handle : '.handle', 
	    update : function () { 
	      var original_colour = $("ul.sortable li").css("background-color"); 
	      $("ul.sortable li").css({ 'background-color' : '#ddf8a9'});
	      var order = $('ul.sortable').sortable('serialize'); 
	      
	      var field_group_id = $('form#form_arrange').attr('rel');
	      
	      $.post($('#base_url').html() + 'custom_fields/save_order/' + field_group_id, order);
	      
	      $("ul.sortable li").animate({backgroundColor:original_colour},750)
	    } 
	  }); 
});
