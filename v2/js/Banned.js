$(document).ready(function() {
    $('.unban').click(function (e) {
        var id = $(this).attr('data-id');
        $.ajax({
            url: 'index.php',
            type: 'POST',
            data: {
                v: 2,
                action: 'delete',
                entity: 'banned',
                id: id,
            },
            dataType: "json",
        })
        .done(response => {
            if (response.success === true) {
                $(this).closest('tr').remove();
            }
        });
    });

    $('#ban').click(function (e) {
		var ip = $('#ip').val().trim();
		var entry = $('#entry').val().trim();
		var location = $('#location').val().trim();
		var postal = $('#postal').val().trim();

		if (entry && ip === '' && location === '' && postal === '') {
			alert('Combination with only entry is not possible.');
			return;
		}
		if (location && ip === '' && entry === '' && postal === '') {
			alert('Combination with only location is not possible.');
			return;
		}

		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'ban',
				ip: ip,
				entry: entry,
				location: location,
				postal: postal
			},
			dataType: "json",
		})
		.done(response => {
			if (response.success === false) {
				if (response.exists) {
					alert('ip, entry, location combination already exist in database');
					return;
				} else {
					alert('an error occured while saving');
					return;
				}
			}
			$('#ip').val('');
			$('#entry').val('');
			$('#location').val('');

            window.location.href = '?v=2&action=b';
		});
	});

    var ip = '';
    $('.ip').click(function(e) {
		ip = $(this).attr('data-ip');
        if (ip == '') {
            return;
        }
		var url = 'https://ipinfo.io/' + ip + '/json';
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
					var url = 'https://ipinfo.io/' + ip + '/json';
					window.open(url, '_blank');
					break;
			}
		},

		mouseleave: function () {
			$(this).hide();
		}
	});

});