$(document).ready(function ()
{
	$('.ban-ip').click(function (e) {
		var ip = $(this).attr('data-ip');
		var action = $(this).attr('data-ban').toLowerCase();
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: action,
				ip: ip,
			},
			dataType: "json",
		})
		.done(response => {
			$(this).html(action === 'ban' ? 'Unban ip' : 'Ban ip');
			$(this).attr('data-ban', (action === 'ban' ? 'unban' : 'ban'));

			$parent = $(this).parent().parent().parent();
			if (action === 'unban') {
				$parent.attr('class', $parent.attr('data-tr'))
			} else {
				$parent.attr('class', 'banned_tr')
			}
		});
	});

	$('#manual-ban-ip').click(function (e) {
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'ban',
				ip: $('#manual_ip').val(),
			},
			dataType: "json",
		})
		.done(response => {
			$('#manual_ip').val('');
		});
	});
});