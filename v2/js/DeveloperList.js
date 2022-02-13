$(document).ready(function () {
	if (isMobile) {
		$('#wrapper').css('height', window.innerHeight + 'px');
	}
	let currentUrl = window.location.href;
	if (currentUrl.includes('s=') ||
		currentUrl.includes('p='))
	{
		$(document).scrollTop(143);
	}
	if ($('.next').length === 0) {
		$('.developer-buttons').each(function() {
			$(this).css('margin-right', '47px');
		});
	}

	if ($('.previous').length === 0) {
		$('.developer-buttons').each(function() {
			$(this).css('margin-left', '47px');
		});
	}

	$('.developer-type-button').click(function () {
		let type = $(this).attr('id');

		move('&t=' + type);
		// return;
		let page = $('#search-data').attr('data-loaded');

		let dataObject = {
			v: 2,
			l: 'd',
			t: type,
			p: page,
		};

		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: dataObject,
			dataType: "json",
		})
		.done(function(response)
		{
			if (response.success === true) {
				return response;
			} else {
				return false;
			}
		});
	});

	$('#g').click(function () {
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				u:'ja',
			},
			dataType: "json",
		})
		.done(function(response)
		{
			if (response.success === true) {
				return response;
			} else {
				return false;
			}
		});
	});
});