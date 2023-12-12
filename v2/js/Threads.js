$(document).ready(function () {
	$('#link-part').focus();

	$('.thread-url').click(function () {
		$(this).focus();
		$(this).select();
	});

	$('.entry-id-input').click(function () {
		$(this).focus();
		$(this).select();
	});

	$('.author-input').click(function () {
		$(this).focus();
		$(this).select();
	});

	$('.thread-url').bind("paste", function (e) {
		e.preventDefault();
		let pastedData = e.originalEvent.clipboardData.getData('text');

		let links = pastedData.split('\n');

		let sortArray = [];
		$.each(links, function (index, link) {
			let arr = [];
			let fileName = link.split('/').pop();

			arr.push(link);
			arr.push(fileName);

			sortArray.push(arr);
		});
		sortArray.sort((a, b) => a[1].localeCompare(b[1]));

		let areaText = '';
		$.each(sortArray, function (index, item) {
			let pos = item[0].indexOf('http');
			areaText += pos !== 0 ? item[0] : '\n' + item[0];
		});

		if (areaText.substring(0, 1) !== 'h') {
			areaText = areaText.substring(1, areaText.length);
		}

		$(this).val(areaText);
	});

	$('.checkbox-holder').click(function () {
		let state = $(this).find('.thread-check').is(':checked');

		if (state) {
			$(this).find('.thread-check').prop("checked", false);
		} else {
			$(this).find('.thread-check').prop("checked", true);
		}
		let data = {
			action: 'confirm',
			threadId: $(this).attr('thread-id'),
			state: $(this).is(':checked') ? 'on' : 'off',
		};

		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: data,
		});
	});

	$('.thread-check').off().on('click', function() {
		$(this).parent().click()
	});

	$('.thread-edit').off().on('click', function() {
		let threadId = $(this).attr('thread-id');
		let entryId = $(this).closest('tr').find('input')[0].value;
		let author = $(this).closest('tr').find('input')[1].value;

		let url = $('#field_' + threadId).val();
		let data = {
			action: 'threadEdit',
			entryId: entryId,
			threadId: threadId,
			author: author,
			url: url,
		};

		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: data,
		});

		$('#go_' + threadId).attr('href', url)
	});

	$('.copy').click(function () {
		let title = $(this).parent().find('.title-title').html();

		let forCopy = document.getElementById('for-copy');
		forCopy.type = 'text';
		forCopy.tagName = 'INPUT';
		forCopy.value = title;

		$('#hidden').css('display', 'block');
		copyToClipboard(forCopy);
		$('#hidden').css('display', 'none');

		let self = $(this);
		let color = $(this).closest('tr').attr('class') === 'row-color-0' ? '#788c9a' : '#98b2c5';

		$(this).css('background', color);
		setTimeout(function() {self.css('background', '');}, 25);
	});

	$('#link-part').change(function() {
		let text = $('#link-part').val();

		window.location.href = '?v=2&EntryAction=threads&link-part=' + text;
	});

	$('.copy-entry-id').click(function() {
		let forCopy = document.getElementById('for-copy');
		forCopy.type = 'text';
		forCopy.tagName = 'INPUT';
		forCopy.value = $(this).attr('entry-id');

		$('#hidden').css('display', 'block');
		copyToClipboard(forCopy);
		$('#hidden').css('display', 'none');

		$('#link-part').focus();
	});
});

function copyToClipboard(elem) {
	target = elem;

	var currentFocus = document.activeElement;
	target.focus();
	target.setSelectionRange(0, target.value.length);

	var succeed;
	try {
		succeed = document.execCommand("copy");
	} catch(e) {
		succeed = false;
	}

	if (currentFocus && typeof currentFocus.focus === "function") {
		currentFocus.focus();
	}

	elem.setSelectionRange(0, target.value.length);

	return succeed;
}