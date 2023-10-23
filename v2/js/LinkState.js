$(document).ready(function() {
	$('#validate-links').click(function() {
		$('#spinner').css('display', 'unset');
		window.location.href = '/?v=2&action=lv&from=' + $('#from').val()+ '&to=' + $('#to').val()
	});
	$('#link-status-form').submit(function() {
		$('#spinner').css('display', 'unset');
	});
});