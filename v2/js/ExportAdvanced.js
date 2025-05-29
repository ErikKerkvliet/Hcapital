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
				multiple: $('input[name="multiple"]').is(':checked'),
			},
			dataType: "json",
		}).done(response => {
			if (response) {
				$('#spinner').css('display', 'none');
				let color = 'red';
				if (! response.state) {
					$('#export-errors').html(response.errors.replace(/,/g, '</br>'));
				} else {
					color = '#38a751';
					setTimeout(switch_source, 50);
				}
			
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

				colorTimer($('#get-export'), color, 5000);
			}
		});
	});
});

$('#link-status-form').submit(function() {
	$('#spinner').css('display', 'unset');
});

function switch_source() {
	$('#switch_source').click();
}

function colorTimer($element, color, time) {
	$element.css('background', color);
	if (time === 0) {
		return;
	}
	setTimeout(colorTimer, time, $element, 'unset', 0);
}