$(document).ready(function()
{
	$('#validate-links').click(function() {
		let state = 0
		$('.link-state').each(function() {
			if ($(this).is(':checked')) {
				state += parseInt($(this).val());
			}
		});

		let hosts = [];
		$('.host-checkbox').each(function() {
			if ($(this).is(':checked')) {
				hosts.push($(this).val());
			}
		});

		const fromVal = $('#from').val();
		const toVal = $('#to').val();

		// Parse inputs into numbers, specifying base 10 (important!).
		const fromNum = parseInt(fromVal, 10);
		const toNum = parseInt(toVal, 10);

		if (Number.isNaN(fromNum) || fromNum < 1 || Number.isNaN(toNum) || toNum < 1) {
			alert("Invalid 'from' or 'to' value. Must be a number greater than 0.");
			return;
		}

		const params = new URLSearchParams({
			v: '2',
			action: 'lv',
			from: fromNum,
			to: toNum,
			state: state,
			hosts: (hosts || []).join(','), 
		});

		$('#spinner').css('display', 'unset');
		window.location.href = `/?${params.toString()}`;	
	});

	$('#link-status-form').submit(function() {
		$('#spinner').css('display', 'unset');
	});
});