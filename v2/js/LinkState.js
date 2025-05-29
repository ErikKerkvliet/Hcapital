$(document).ready(function() {
	$('.link-state.fail').attr('checked', true);

	$('#validate-links').click(function() {
		$('#spinner').css('display', 'unset');
		let state = 0
		$('.link-state').each(function() {
			if ($(this).is(':checked')) {
				state += parseInt($(this).val());
			}
		});
		window.location.href = '/?v=2&action=lv&from=' + $('#from').val()+ '&to=' + $('#to').val() + '&state=' + state;
	});
	$('#link-status-form').submit(function() {
		$('#spinner').css('display', 'unset');
	});
});