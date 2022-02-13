$(document).ready(function () {
	$('#add-developer').click(function () {
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

	$('#add-relation').click(function () {
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
				} else {
					alert('loading component failed');
				}
			}
		});
	});

	$('#add-other').click(function () {
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

	$('.add-sharing-url').click(function () {
		let length = $('.sharing-url').length + 1;
		let $parent = $(this).parent();

		$.ajax({
			url: '/index.php',
			type: 'POST',
			data: {
				action: 'add',
				type: 'sharingUrl',
				length: length,
				entryId: $parent.find('.sharing-entry-id').first().val(),
				entryType: $parent.find('.sharing-type').first().val(),
				author: $parent.find('.sharing-author').first().val(),
				threadId: $(this).attr('thread-id'),
			},
			dataType: "json",
			success: function (response) {
				if (response.success) {
					let html = $('#more-sharing-urls').html();

					let newHtml = html + response.content;
					$('#more-sharing-urls').html(newHtml);
					initialise();
				} else {
					alert('loading component failed');
				}
			}
		});
	});
});