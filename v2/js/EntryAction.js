$(document).ready(function () {
	let action = type;

	$.each($('textarea'), function(key, item) {
		setTimeout(function() {
			var links = $('input[name="hidden-' + key + '-links"]').val();
			var newline = String.fromCharCode(13, 10);

			links = links.replace(/splitter/g, newline);
			console.log('input[name="hidden-' + key + '-links"]');
			$(item).text(links);
		}, 100);
	});

	let textValue = $('input[name="hidden-99-links"]').val();
	if (textValue) {
		let text = textValue.replace(/splitter/g, String.fromCharCode(13, 10));
		$('textarea[name="links-99-links"]').text(text)
	}

	if (action === 'edit') {
		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: {action: 'getEntryData'},
			dataType: "json",
			success: function (response) {
				let data = response;

				if (data.success) {
					$('#type').val(data.type);
					$('#time_type').val(data.timeType);
					$('#title').val(data.title);
					$('#romanji').val(data.romanji);
					$('#website').val(data.website);
					$('#information').val(data.information);
					$('#size').val(data.size);
					$('#password').val(data.password);

					data.developers.forEach(function (developer, index) {
						let elementId = '#developer-' + index;
						$(elementId).val(developer);
					});

					data.relations.forEach(function (relation) {
						addRelation(relation)
					});

					data.otherLinks.forEach(function (otherLink) {
						addOtherLinks(otherLink);
					});
				} else {
					alert('loading entry data failed');
				}
			}
		});
	}

	$('.link-area').click(function () {
		$(this).focus();
		$(this).select();
	});

	$('.link-area').bind("paste", function(e){
		e.preventDefault();
		let pastedData = e.originalEvent.clipboardData.getData('text');
		pastedData = pastedData.replace(/\[\/?URL\]/g, '');

		let links = pastedData.split('\n');

		let sortArray = [];
		$.each(links, function(index, link) {
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

	$('#released').click(function () {
		$(this).focus();
		$(this).select();
	});

	$('#released').bind("paste", function(e){
		e.preventDefault();
		let pastedData = e.originalEvent.clipboardData.getData('text');

		$(this).val(pastedData.replace(/-/g, '/'));
		$(this).val(pastedData.replace(/\./g, '/'));
	});

	$('#vndb-link').click(() => {
		let vndb = window.document.getElementById('vndb-link').getAttribute('data-vndb-id');
		let url = 'https://vndb.org/v' + vndb;
		window.open(url, '_blank')
	})
});

function addRelation(data) {

}

function addOtherLinks(data) {

}
