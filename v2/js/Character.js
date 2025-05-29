$(document).ready(() => {
	let characterId = $('#data').attr('data-character');
	let entryId = $('')
	$('.delete-char').click(() => {
		if (! confirm('Are you sure you want to fully delete this character?')) {
			return;
		}
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'delete',
				entity: 'character',
				characterId: characterId,
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