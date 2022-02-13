const STATE_DEFAULT = 0;
const STATE_REFRESH = 1;
const STATE_BACK = 2;

var state = 0;

function getDecodedIds(idsString) {
	let steps = idsString.split('.');

	let ids = [];
	steps.forEach(function(step) {
		ids.push(step.split(','))
	});

	return ids;
}

function getAllIds() {
	let encodedString = localStorage.getItem('random');
	let decodedString = atob(encodedString);

	let sides = decodedString.split('|');

	return {
		back: getDecodedIds(sides[0]),
		next: getDecodedIds(sides[1]),
	};
}

function store(type, ids) {
	let idString;
	if (type === 'back') {
		let back = localStorage.getItem('random-back');
		idString = atob(back);
		idString.split('|');
	}
}

function setState() {
	let back = localStorage.getItem('random-back') ? localStorage.getItem('random-back') : '';

	if (back) {
		$('#navigator-back').css('opacity', '1');
	} else {
		$('#navigator-back').css('opacity', '0.2');
	}
}

function refresh(entries) {
	if (typeof entries == 'string') {
		entries = entries.split(',');
	}

	entries.forEach((entry, index) => {
		var id = typeof entry === 'string' ? entry : entry.id;
		var div = $('#' + index).find('div');
		var img = div.find('img');

		div.attr('id', id);

		let src = '/entry_images/entries/' + id + '/cover/_cover_m.jpg';
		img.attr('src', src)
	});
}

function saveIds(ids) {
	let idsInStorage = localStorage.getItem('random-back') ? localStorage.getItem('random-back') : '';
	let storedIds = [];
	if (idsInStorage) {
		storedIds.push(atob(idsInStorage));
	}
	storedIds.push(ids.join(','));

	let idsToStore = btoa(storedIds.join('.'));

	localStorage.setItem('random-back', idsToStore);
}

function getStoredIds(pop = false) {
	if (! localStorage.getItem('random-back')) {
		return [];
	}
	let idsString = atob(localStorage.getItem('random-back'));

	let split =  idsString.split('.');

	if (pop) {
		split.pop();
	}
	let ids = split[split.length - 1];
	// }

	localStorage.setItem('random-back', btoa(split.join('.')));

	return ids;
}

function getIds(data) {
	let ids = getStoredIds();
	if (! ids) {
		return '';
	}

	if (data[0].tagName === 'DIV') {
		$.each(data, function() {
			ids.push($(this).attr('id'));
		});
	} else {
		ids = data.map((item) => {
			return item.id;
		});
	}
	return ids;
}


$(document).ready(function () {
	state = STATE_DEFAULT;
	let ids = getStoredIds();

	if (ids.length === 0) {
		ids = getIds($('.random_entry'));

		saveIds(ids);
	} else {
		refresh(ids);
	}

	$('.random_entry').click(function () {
		var entryId = $(this).attr('id');
		window.location.href = '?v=2&id=' + entryId;
	});


	$('#navigator-refresh').click(function () {
		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: {
				v: 2,
				action: 'random',
				refresh: 'true',
			},
			dataType: "json",
			success: function (response) {
				if (response.success) {
					refresh(response.entries);
					saveIds(getIds(response.entries));

					state = STATE_REFRESH;
					setState();
				}
			}
		});
	});

	$('#navigator-back').click(function() {
		state = STATE_BACK;
		setState();

		ids = getStoredIds(true).split(",");
		refresh(ids);
	});

	$('#navigator-next').click(function() {
		setButtonState(STATE_NEXT);


	});
});

function logKey(e) {
	switch (`${e.code}`) {
		case 'KeyR':
			$('#navigator-refresh').click();
			break;
		default:
			return;
	}
}

document.addEventListener('keypress', logKey);

