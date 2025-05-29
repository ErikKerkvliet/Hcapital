$(document).ready(() => {
	initialiseComponents();
});

function initialiseComponents() {
	$('#add-developer').click(() => {
		let type = $('#type').val();

		let length = $('.new-developer').length;

		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: {action: 'add', type: 'developer', length: length, developerType: type},
			dataType: "json",
			success: function (response) {
				if (response.success) {
					let html = $('#more_developers').html();

					let data = response.content;

					let newHtml = html + data;
					$('#more_developers').html(newHtml);

					adminInitialise();

					$.each($('.component-developer'), function (index) {
						$(this).find('#description').html('Developer ' + index);
					});

					$('#developer_select_' + length).focus().select();
				} else {
					alert('loading component failed');
				}
			}
		});
	});

	$('#add-relation').click(() => {
		let relations = $('.relations');

		let length = $('.component-relation').length;


		let type = $('#type').children("option:selected").val();

		$.each(relations, function () {
			let options = $(this).find('option');
			let selected = $(this).val();
			$.each(options, function () {
				$(this).removeAttr('selected');
			});
			let element = $(this).find('option[value="' + selected + '"]');
			element.attr('selected', 'selected');
		});
		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: {action: 'add', type: 'relation', length: length, entryType: type},
			dataType: "json",
			success: function (response) {
				if (response.success) {
					let html = $('#more-relations').html();

					let newHtml = html + response.content;
					$('#more-relations').html(newHtml);
					$('#relation-' + length).focus().select();
				} else {
					alert('loading component failed');
				}
			}
		});
	});

	$('#add-other').click(() => {
		let textFields = $('#more-links').find('input');
		let textAreas = $('#more-links').find('textarea');

		$.each(textFields, function () {
			let text = $(this).val();
			$(this).attr('value', text);
		});

		$.each(textAreas, function () {
			let text = $(this).val();
			$(this).html(text);
		});

		let length = $('.links-comment').length + 1;
		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: {action: 'add', type: 'links', length: length},
			dataType: "json",
			success: function (response) {
				if (response.success) {
					let html = $('#more-links').html();

					let newHtml = html + response.content;
					$('#more-links').html(newHtml);
				} else {
					alert('loading component failed');
				}
			}
		});
	});

	$('.add-sharing-url').click(() => {
		let length = $('.sharing-url').length;
		let $parent = $(this).parent();

		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: {
				action: 'add',
				type: 'sharingUrl',
				length: length,
				entryId: $parent.find('.sharing-entry-id').first().val(),
				author: $parent.find('.sharing-author').first().val(),
			},
			dataType: "json",
			success: function (response) {
				if (response.success) {
					let html = $('#more-sharing-urls').html();

					let newHtml = html + response.content;
					$('#more-sharing-urls').html(newHtml);
					initialiseComponents();
				} else {
					alert('loading component failed');
				}
			}
		});
	});
}

document.addEventListener('click', function(event) {
	let parts = event.target.id.split('-');
	let nr = parts[parts.length - 1];
	let entry = parseInt($('#info-title').attr('data-id'));
    if (event.target.matches('.save-sharing-url')) {
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				EntryAction: 'updateSharingUrl',
				entry: entry,
				number: nr,
				author: $('#user-select-' + nr).first().val(),
				url: $('#sharing-textarea-' + nr).val().trim(),
			},
			dataType: "json",
		})
		.done(function(response) {
			if (response.success === true) {
				if (nr == 0) {
					window.location.href = "/?v=2&id=" + (entry + 1);
				}
				event.target.style.backgroundColor = '#93d798';
			} else {
				event.target.style.backgroundColor = '#e35858';
			}
		});
    } else if (event.target.matches('.open-sharing-url')) {
		var url = $('#sharing-textarea-' + nr).val();
		window.open(url, '_blank');
	} else if (event.target.matches('.update-sharing-url')) {
		const type = event.target.textContent === 'Create' ? 'full' : 'update';
		const username = document.querySelector(`#user-select-${nr}`).value;

		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'getenv',
				keys: ['PYTHON_PATH', 'APP_MAIN_PATH'],
			},
			dataType: "json",
		})
		.done(function(response) {
			if (response.success === true) {
				const pythonPath = response.envs.PYTHON_PATH;
				const appMainPath = response.envs.APP_MAIN_PATH;
				const command = [
					pythonPath,
					appMainPath,
					type,
					username,
					entry,
					'headless'
				].join(' ');
				
				copyToClipboard(command);
				highlightSuccess(event.target);
			}
		});
	} else if (event.target.matches('.add-sharing-url')) {
	} else if (event.target.matches('.delete-sharing-url')) {
		if (! $('#sharing-textarea-' + nr).val()) {
			$('#sharing-url-' + nr).remove();
			return;
		}
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'delete',
				entity: 'thread',
				entry: entry,
				number: nr
			},
			dataType: "json",
		});
		$('#sharing-url-form-' + nr).html('');
	}
});

function colorTimer($element, color, time) {
	$element.css('background', color);
	if (time === 0) {
		return;
	}
	setTimeout(colorTimer, time, $element, 'unset', 0);
}

function copyToClipboard(text) {
    const tempElement = document.createElement('textarea');
    tempElement.value = text;
    tempElement.setAttribute('readonly', '');
    tempElement.style.cssText = 'position: absolute; left: -9999px;';
    
    document.body.appendChild(tempElement);
    tempElement.select();
    document.execCommand('copy');
    document.body.removeChild(tempElement);
}

function highlightSuccess(element) {
    const SUCCESS_COLOR = '#38a751';
    const HIGHLIGHT_DURATION = 5000;
    
    colorTimer(element, SUCCESS_COLOR, HIGHLIGHT_DURATION);
}