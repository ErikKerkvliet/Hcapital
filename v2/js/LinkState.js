$(document).ready(function() {
	$('.link-state.fail').attr('checked', true);
	$('.host-checkbox.rapidgator').attr('checked', true);


	$('#validate-links').click(function() {
		$('#spinner').css('display', 'unset');
		let state = 0
		$('.link-state').each(function() {
			if ($(this).is(':checked')) {
				state += parseInt($(this).val());
			}
		});

		let hosts = [];
		let host_str = '';
		$('.host-checkbox').each(function() {
			if ($(this).is(':checked')) {
				hosts.push($(this).val());
			}
		});
		host_str = hosts.join(',');
		window.location.href = '/?v=2&action=lv&from=' + $('#from').val()+ '&to=' + $('#to').val() + '&state=' + state + '&hosts=' + host_str;
	});
	$('#link-status-form').submit(function() {
		$('#spinner').css('display', 'unset');
	});
});