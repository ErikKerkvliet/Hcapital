$(document).ready(() => {
	let characterId = $('#data').attr('data-character');

	$('.delete-char').click(() => {
		if (! confirm('Are you sure you want to fully delete this developer?')) {
			return;
		}
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'deleteCharacter',
				character: characterId,
			},
			dataType: "json",
		})
		.done(function(response) {
			if (response.success) {
				window.location.href = '/';
			}
		});
	});

	$('.edit-char').click(function() {
		window.location.href = '/?v=2&_cid=' + characterId;
	});
});