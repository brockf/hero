$(document).ready(function() {
	$('a#sharing').toggle(function() {
		$('div.sharing').slideDown();
		return FALSE;
	},
	function () {
		$('div.sharing').slideUp();
		return FALSE;
	});
});