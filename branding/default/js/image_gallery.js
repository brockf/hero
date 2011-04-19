$(document).ready(function(){
	$("ul.image_gallery").sortable({ 
	    handle : '.move', 
	    update : function () { 
	      var order = $('ul.image_gallery').sortable('serialize'); 
	      $.post($('ul.image_gallery').attr('rel'), order, function (data) {
	      	notice_ok('Image gallery order saved.');
	      });
	    } 
	  }); 
});