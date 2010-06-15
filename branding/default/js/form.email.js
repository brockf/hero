$(document).ready(function () {
	// to_address: move check on textbox focus
	$('#to_address_email').focus(function() {
		$('[name="to_address"][value="email"]').attr('checked',true);
	});
	
	// bcc_address: move check on textbox focus
	$('#bcc_address_email').focus(function() {
		$('[name="bcc_address"][value="email"]').attr('checked',true);
	});
	
	// html toggle
	// do toggle if is_html == 1
	if ($('#is_html').val() == '1') {
		$('#email_body').wysiwyg();
	}
	
	// make it HTML if they click the link
	$('#make_html').click(function() {
		$('#email_body').wysiwyg();
		$('#is_html').val('1');
		
		$(this).remove();
		return false;
	});
	
	// pull available variables on trigger toggle
	$('#trigger').change(function() {
		if ($(this).val() != '') {
			$.get($('#base_url').html()+'settings/show_variables/'+$(this).val(),
			  function(data){
			    $('#email_variables').html(data);
			  });
		}	  
	});
	
	// handle preset trigger
	if ($('#trigger').val() != '') {
		$.get($('#base_url').html()+'settings/show_variables/'+$('#trigger').val(),
		  function(data){
		    $('#email_variables').html(data);
		  });
	}
});