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
					initialiseComponents();
					sharingUrlActions(length);
				} else {
					alert('loading component failed');
				}
			}
		});
	});
}

function sharingUrlActions(componentCount)
{
	$('.save-sharing-url').click(() => {
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
		.done((response) => {
			var $textarea = $parent.find('textarea').first();
			if (response.success === true) {
				$textarea.css('background', '#93d798');
			} else {
				$textarea.css('background', '#e35858');
			}
			var id = parseInt($('.sharing-entry-id').first().val()) + 1;
			window.location.href = '/?v=2&id=' + id;
		});
	});

	$('.delete-sharing-url').click(() => {
		let nr = $(this).attr('nr');
		if (! $('#sharing-url-textarea-' + nr).val()) {
			console.log('#sharing-url-' + nr)
			$('#sharing-url-' + nr).remove();
			return;
		}
		$.ajax({
			url: 'index.php',
			type: 'POST',
			data: {
				v: 2,
				EntryAction: 'deleteSharingUrl',
				threadId: $(this).attr('thread-id'),
			},
			dataType: "json",
		});
		$('#sharing-url-' + nr).html('');
	});

	$('#downloads-div').click(() => {
		window.location.href = '/?v=2&action=di&entry=' + entryId;
	});
}