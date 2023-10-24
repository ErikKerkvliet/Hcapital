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

	$('.admin-button').mousedown(function () {
		// $(this).css('margin', '8px 7px 9px 4px');
		// .admin-button:active {
		// margin-top: 9px;
		// margin-bottom: 7px;
		// margin-left: 4px;
		// margin-right: 9px;
		//
		// /*margin: 8px 7px 9px 4px;*/
		// color: rgb(0, 0, 0);
		// box-shadow: 0px 0px 0px #000;
		// }
	});

	$('#threads').click(function () {
		let object = {
			EntryAction: 'threads',
			page: 1,
		};

		post('?v=2', object)
	});

	$('#insert').click(function () {
		let object = {EntryAction: 'insert'};

		post('?v=2', object)
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
			entryIds: id,
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

	$('#validate').click(function() {
		window.location.href = '?v=2&action=lv'
	});

	$('#clear-downloads').click(function () {
		var date = $('#download-date').val();
		var hasEntry = window.location.href.includes('entry');
		var deleteAll = document.getElementById('delete-all').checked;

		if (! date && ! deleteAll && ! hasEntry) {
			alert('No date or entry entered');
			return;
		}
		var url = '/?v=2&action=clearDownloads';
		if (date) {
			url += '&date=' + date;
		}
		if (deleteAll) {
			url += '&all=true';
		}
		if (hasEntry) {
			var entryId = window.location.href.split('=').pop();
			url += '&entry=' + entryId
		}
		window.location.href = url;
	});

	$('#delete').click(function () {
		let id = window.location.href.split('=').pop();
		let object = {
			EntryAction: 'delete',
			entryId: id,
		};

		let answer = confirm('Do you really want to delete this entry?');

		if (answer) {
			post('?v=2', object);
		} else {
			return;
		}
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

	$('#wrapper').scroll(function(e) {

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

	$('#remove-developer').click(function () {
		if (!confirm('Are you sure you want to fully delete this developer?')) {
			return;
		}
		let developerId = $('#data').attr('data-developer');

		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'removeDeveloper',
				developerId: developerId,
			},
			dataType: "json",
		})
			.done(function (response) {
				reInitialize = true;
				window.location.href = '?v=2&l=d';
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
				action: 'removeRelation',
				entryId: entryId,
				relationId: relationId,
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
					action: 'removeEntryDeveloper',
					entryId: $('#entry-id').attr('entry-id'),
					developerId: $(this).attr('developer-id'),
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
				action: 'removeLinks',
				entryId: entryId,
				comment: comment,
			},
			dataType: "json",
		})
			.done(function (response) {
			});

		adminInitialise();
	});

	if (reInitialize === true) {
		$('.save-sharing-url').click(function () {
			var $parent = $(this).parent();
			$.ajax({
				url: 'index.php',
				type: 'POST',
				data: {
					v: 2,
					EntryAction: 'updateSharingUrl',
					entryId: $parent.find('.sharing-entry-id').first().val(),
					type: $parent.find('.sharing-type').first().val(),
					author: $parent.find('.sharing-author').first().val(),
					url: $parent.find('.sharing-url').first().val(),
					threadId: $(this).attr('thread-id'),
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

$(document).ready(function()
{
	adminInitialise();
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
					user: 'public.rapidgator@gmail.com',
					password: '1I^uDckm$d92PEaE*1Z',
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
