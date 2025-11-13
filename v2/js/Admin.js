var addChar = null;
function adminInitialise(reInitialize = false) {
	let linksLinks = $('.links-links');

	addChar = $('#add-char');

	$.each(linksLinks, function (index, item) {
		if (linksLinks !== undefined) {
			let stringLength = $(this).html().split(':')[0].length;

			let elementHtml = 'https' + $(this).html().substr(stringLength, 999);
			let links = elementHtml ? elementHtml.split('|?!|') : [];

			links = links.filter(function (el) {
				return el != null && el !== 'https';
			});
			$(this).html(links.join('\n'));
		}
	});

	$('#submit').click(function (e) {
		if (document.getElementsByClassName('developer_select').length === 0 &&
			document.getElementsByClassName('developers').length === 0) {
			e.preventDefault();
			alert('No developer');
			return;
		}
		let object = {EntryAction: 'insert'};
		post('?v=2', object)
	});

	$('#insert').click(function () {
		let object = {EntryAction: 'insert'};

		post('?v=2', object)
	});

	$('#invalid-links').on('click', function() {
        window.location.href = '?v=2&action=il';
    });

	$('#edit').click(function () {
		let id = window.location.href.split('=').pop();
		let object = {
			EntryAction: 'edit',
			entryId: id,
		};

		post('?v=2&_id=' + id, object)
	});

	$('#export').click(function () {
		let id = window.location.href.split('=').pop();
		let object = {
			action: 'exportAdvanced',
			ids: id,
		};

		post('?v=2', object)
	});

	$('#import-entry').click(function () {
		let object = {
			EntryAction: 'import',
		};

		post('?v=2', object)
	});

	$('#add-char').click(function () {
		let entryId = $('#info-title').attr('data-id');
		let object = {
			EntryAction: 'insertCharacter',
			entryId: entryId,
		};

		post('?v=2', object);
	});

	$('#import').click(function () {
		let object = {
			EntryAction: 'import',
			importText: $('#import-textarea').val(),
		};

		post('?v=2', object)
	});

	$('#import-button').click(function () {
		let object = {
			EntryAction: 'importEntry',
			importText: $('#import-textarea').val().trim(),
		};

		post('?v=2', object)
	});

	$('#download-info').click(function () {
		window.location.href = '?v=2&action=di';
	});

	$('#banned').click(function () {
		window.location.href = '?v=2&action=b';
	});

	$('#validate').click(function() {
		window.location.href = '?v=2&action=lv'
	});

	$('.move-to').click(function () {
		let id = $(this).attr('id');
		let infoElement = $('#info-title')
		let entryId = infoElement.attr('data-id');
		let entryType = infoElement.attr('data-type');

		let moveTo = id.split('-').pop();

		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: {
				v: 2,
				EntryAction: 'moveTo',
				moveTo: moveTo,
				entryId: entryId,
				entryType: entryType,
			},
			dataType: "json",
			success: function (response) {
				if (response.success) {
					$('body').css('background', '#213820');

				} else {
					alert('changing (entry)type failed');
				}
			}
		});
	});

	function post(path, params, method = 'post') {
		const form = document.createElement('form');
		form.method = method;
		form.action = path;

		for (const key in params) {
			if (params.hasOwnProperty(key)) {
				const hiddenField = document.createElement('input');
				hiddenField.type = 'hidden';
				hiddenField.name = key;

				hiddenField.value = params[key];
				form.appendChild(hiddenField);
			}

		}
		document.body.appendChild(form);
		form.submit();
	}

	$('#export-button').click(function () {
		const el = document.createElement('textarea');
		el.value = $('#export-text').html().trim();
		el.setAttribute('readonly', '');
		el.style.position = 'absolute';
		el.style.left = '-9999px';
		document.body.appendChild(el);
		el.select();
		document.execCommand('copy');
		document.body.removeChild(el);

		$(this).css('background', '#38a751')
	});

	$('#edit-developer').click(() => {
		let developerId = $('#data').attr('data-developer');
		window.location.href = '/?v=2&_did=' + developerId;
	});

	$('#delete').click(function () {
		let entryId = window.location.href.split('=').pop();
		let object = {
			action: 'delete',
			entity: 'entry',
			entry: entryId,
		};

		let answer = confirm('Do you really want to delete this entry?');

		if (answer) {
			post('?v=2', object);
		} else {
			return;
		}
	});

	$('#delete-developer').click(function () {
		if (!confirm('Are you sure you want to fully delete this developer?')) {
			return;
		}

		let developerId = $('#data').attr('data-developer');

		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'delete',
				entity: 'developer',
				developer: developerId,
			},
			dataType: "json",
		})
			.done(function (response) {
				reInitialize = true;
				window.location.href = '?v=2&l=d';
			});
	});

	$('.char-edit').click(function() {
		let charId = $(this).attr('char-id');
		let entryId = $('#info-title').attr('data-id');

		window.location.href = '/?v=2&id=' + entryId + '&_cid=' + charId;
	});

	$('.char-delete').click(function() {
		let charId = $(this).attr('char-id');
		let entryId = document.getElementById('info-title').getAttribute('data-id');
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'delete',
				entity: 'entryCharacter',
				entry: entryId,
				character: charId,
			},
			dataType: "json",
		})
		.done(function(response) {
			if (response.success) {
				$('div[character-box="' + charId + '"]').remove();
			}
		});
	});

	$('.remove-relation').click(function () {
		let relationId = $(this).attr('relation-id');
		let entryId = $('#entry-id').attr('entry-id');

		$(this).parent().parent().remove();

		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'delete',
				entity: 'entryRelation',
				entry: entryId,
				relation: relationId,
			},
			dataType: "json",
		})
			.done(function (response) {
			});

		adminInitialise();
	});

	$('.remove-developer').click(function () {
		if ($(this).attr('developer-id')) {
			$.ajax({
				url: 'index.php',
				type: 'POST',
				data: {
					v: 2,
					action: 'delete',
					entity: 'entryDeveloper',
					entry: $('#entry-id').attr('entry-id'),
					developer: $(this).attr('developer-id'),
				},
				dataType: "json",
			})
				.done(function (response) {
				});
		}
		$(this).parent().parent().remove();

		adminInitialise();
	});

	$('.remove-links').click(function () {
		let comment = $(this).parent().find('input').val();

		let entryId = $('#entry-id').attr('entry-id');

		$(this).parent().parent().remove();

		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'delete',
				entity: 'link',
				entry: entryId,
				comment: comment,
			},
			dataType: "json",
		})
			.done(function (response) {
			});

		adminInitialise();
	});

	$('#clear-downloads').click(function () {
		var date = $('#download-date').val();
		var hasEntry = window.location.href.includes('entry');
		var deleteAll = document.getElementById('delete-all').checked;

		if (! date && ! deleteAll && ! hasEntry) {
			alert('No date or entry entered');
			return;
		}

		let data = {
			v: 2,
			action: 'delete',
			entity: 'download',
			date: date,
		};

		if (hasEntry) {
			data['entry'] = window.location.href.split('=').pop();
		}

		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: data,
			dataType: "json",
		})
		.done(function (response) {
			if (response.success) {
				alert('Downloads cleared');
				if (hasEntry) {
					window.location.href = '?v=2&id=' + window.location.href.split('=').pop();
				} else {
					window.location.href = '?v=2&action=di';
				}
			} else {
				alert('Failed to clear downloads');
			}
		});
	});

	$('#search-character-submit').click((e) => {
		e.preventDefault();

		let search = $('#search-character').val();
		if (search.length >= 3) {
			$.ajax({
				url: 'index.php',
				type: 'POST',
				data: {
					v: 2,
					action: 'searchCharacters',
					search: search,
				},
				dataType: "json",
			})
			.done(function (response) {
				let html = '<option value=""></option>';
				response.forEach((character) => {
					html += `<option value="${character.id}">${character.romanji} - ${character.kanji} - ${character.entries}</option>`
				});
				$('#existing-select').html(html);
				$('#existing-select').css('display', 'unset')
			});
		}
	});

	$('#downloads-div').click(() => {
		let entryId = $('#info-title').attr('data-id')
		window.location.href = '/?v=2&action=di&entry=' + entryId;
	});

	if (reInitialize === true) {
		$('.save-sharing-url').click(function () {
			var $parent = $(this).parent();
			alert(2)

			$.ajax({
				url: 'index.php',
				type: 'POST',
				data: {
					v: 2,
					EntryAction: 'updateSharingUrl',
					entryId: $parent.find('.sharing-entry-id').first().val(),
					author: $parent.find('.sharing-author').first().val(),
					url: $parent.find('.sharing-url').first().val(),
				},
				dataType: "json",
			})
				.done(function (response) {
					var $textarea = $parent.find('textarea').first();
					if (response.success === true) {
						$textarea.css('background', '#93d798');
					} else {
						$textarea.css('background', '#e35858');
					}
					var id = parseInt($('.sharing-entry-id').first().val()) + 1;
					window.location.href = '/?v=2&id=' + id;
					initialise();
				});
		});
	}
}

$(document).ready(function () {
	$('.item-button').click(function (event) {
		const button = event.currentTarget;

		if (location.href.includes('_id') || location.href.includes('_cid')) {
			$.ajax({
				url: 'index.php',
				type: 'POST',
				data: {
					v: 2,
					action: 'getenv',
					keys: ['ROOT_PATH', 'LOCAL_PC_PASSWORD'],
				},
				dataType: "json",
			})
			.done((response) => {
				if (response.success === true) {
					let rootPath = response.envs.ROOT_PATH;
					let localPcPassword = response.envs.LOCAL_PC_PASSWORD;

					const el = document.createElement('textarea');
					let type = button.getAttribute('type') === 'entry' ? 'entries' : 'char';
					let path = rootPath + '/entry_images/' + type + '/' + button.getAttribute('item-id');

					if (button.getAttribute('action-type') === 'folder') {
						el.value = 'echo ' + localPcPassword + ' | sudo -S thunar ' + path;
					}
					else if (button.getAttribute('action-type') === 'permission') {
						el.value = 'echo ' + localPcPassword + ' | sudo -S chmod -R 777 ' + path;
					}
					el.setAttribute('readonly', '');
					el.style.position = 'absolute';
					el.style.left = '-9999px';
					document.body.appendChild(el);
					el.select();
					document.execCommand('copy');
					document.body.removeChild(el);
				}
			});
		}
	});
});

function logKey(e) {
	switch (`${e.code}`) {
		case 'NumpadAdd':
			if (window.location.href.includes('_id')) {
				window.location.href = window.location.href.replace('_id', 'id');
			} else {
				window.location.href = window.location.href.replace('id', '_id');
			}
			break;
		case 'Numpad0':
			window.location.href = $('#switch_source').attr('url');
			break;
		case 'Numpad3':
			var splitted = window.location.href.split('=');
			var id = parseInt(splitted[splitted.length - 1]);
			id++;
			window.location.href =  window.location.href.replace(/id=.*/gm, 'id=' + id);
			break;
		case 'Numpad2':
			splitted = window.location.href.split('=');
			id = parseInt(splitted[splitted.length - 1]);
			id--;
			window.location.href =  window.location.href.replace(/id=.*/gm, 'id=' + id);
			break;
		default:
			return;
	}
}

document.addEventListener('keypress', logKey);

if (window.location.href.includes('&id=')) {
	var checked = false;
	document.addEventListener('mousewheel', () => {
		if (! checked && window.pageYOffset > 400) {
			checked = true;
			var buttons = document.getElementsByClassName('link-button');

			var ids = [];
			Array.from(buttons).forEach((button, index) => {
				ids.push(button.getAttribute('data-link-id'));
			});

			$.ajax({
				url: 'index.php',
				type: 'POST',
				data: {
					v: 2,
					action: 'fileInfo',
					linkIds: ids.join(','),
					dataType: "json",
				}
			})
			.done(function (response) {
				items = JSON.parse(response);
				items.forEach((item, index) => {
					var split = item.split('-');
					console.log($('div[data-link-id="' + split[0] + '"]'));
					if (split[1] === 'success') {
						$('div[data-link-id="' + split[0] + '"]').css('background', '#598d59');
					} else if (split[1] === 'fail') {
						$('div[data-link-id="' + split[0] + '"]').css('background', '#b75555');
					}
				})
			});
		}
	}, {passive: true});
}
