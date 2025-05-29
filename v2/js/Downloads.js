$(document).ready(function ()
{
	$('#clear-old').click(function (e) {
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'removeOldDownloads',
			},
			dataType: "json",
		})
			.done(response => {
				if (response.success) {
					alert('success');
				}
			});
	});

	$('.ban-ip').click(function (e) {
		var ip = $(this).attr('data-ip');
		var action = $(this).attr('data-ban').toLowerCase();
		action = action === 'unban' ? 'delete' : action;
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: action,
				entity: 'banned',
				ip: ip,
			},
			dataType: "json",
		})
		.done(response => {
			$(this).html(action === 'ban' ? 'Unban ip' : 'Ban ip');
			$(this).attr('data-ban', (action === 'ban' ? 'unban' : 'ban'));

			$parent = $(this).parent().parent().parent();
			if (action === 'delete') {
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

	var ip = ''
	$('.td_4').click(function(e) {
		ip = $(this).attr('data-ip');
		var url = 'https://ipapi.co/' + ip + '/json';
		let x = event.pageX - 30;
		let y = event.pageY + 25;
		y -= window.scrollY;

		$.ajax({
			url: url,
			type: 'GET',
			dataType: "json",
		})
		.done(response => {
			$.ajax({
				url: '/?',
				type: 'GET',
				dataType: "json",
				data: {
					action: 'add',
					type: 'ipData',
					ipData: response
				}
			}).done(resp => {
				$('#ip-model').html(resp.content);
				$('#ip-model').show();
			});


			$('#ip-model').css('left', x + 'px');
			$('#ip-model').css('top', y + 'px');
			$('#ip-model').css({
				"-webkit-transform": 'translate(0%, -100%)',
				"-ms-transform": 'translate(0%, -100%)',
				"transform": 'translate(0%, -100%)'
			});
		});
	});

	$('#ip-model').on({
		mousemove: function (event) {
			$(this).show();

			let x = event.pageX - 30;
			let y = event.pageY + 25;
			y -= window.scrollY;

			$(this).css('left', x + 'px');
			$(this).css('top', y + 'px');

			$(this).css({
				"-webkit-transform": 'translate(0%, -100%)',
				"-ms-transform": 'translate(0%, -100%)',
				"transform": 'translate(0%, -100%)'
			});
		},

		mousedown: function(e) {
			switch (event.which) {
				case 1:
					$(this).hide();
					break;
				case 3:
					var url = 'https://ipapi.co/' + ip + '/json';
					window.open(url, '_blank');
					break;
			}
		},

		mouseleave: function () {
			$(this).hide();
		}
	});
});