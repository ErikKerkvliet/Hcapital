$(document).ready(function() {
	$('#switch_source').attr('url', $('#switch_source').attr('url') + '&EntryAction=import');
	$('#get-export').click(() => {
		$('#spinner').css('display', 'unset');
		$.ajax({
			url: '/?',
			type: 'GET',
			data: {
				v: 2,
				action: 'getExportCode',
				entryIds: $('#entry-ids').val(),
				type: $('input[name="type"]:checked').val(),
				all: $('input[name="all"]').is(':checked'),
			},
			dataType: "json",
		}).done(response => {
			$('#spinner').css('display', 'none');
			if (response.success) {
				$('#export-text').html(response.exportCode);

				const el = document.createElement('textarea');
				el.value = $('#export-text').html().trim();
				el.setAttribute('readonly', '');
				el.style.position = 'absolute';
				el.style.left = '-9999px';
				document.body.appendChild(el);
				el.select();
				document.execCommand('copy');
				document.body.removeChild(el);

				colorTimer($('#get-export'), '#38a751', 5000);
			}
		});
	});
});

$('#link-status-form').submit(function() {
	$('#spinner').css('display', 'unset');
});

function colorTimer($element, color, time) {
	$element.css('background', color);
	if (time === 0) {
		return;
	}
	setTimeout(colorTimer, time, $element, 'unset', 0);
}