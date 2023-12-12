function listInitialise()
{
	let currentUrl = window.location.href;

	if (currentUrl.includes('s=') ||
		currentUrl.includes('c=') ||
		currentUrl.includes('t='))
	{
		$(document).scrollTop(139);
	}

	$('.list-item').on({
		mouseenter: function () {
			$(this).find('.bg-change').css('background', '#8195a3');
		},

		mouseleave: function () {
			$(this).find('.bg-change').css('background', '#6c7f8c');
		},
	});

	$('.small-tumbnail-img').on({
		mouseenter: function () {
			let link = $(this).parent().parent().parent().parent().parent().parent().parent();
			let entryId = $(this).attr('entry-id');

			link.attr('href', '?v=2&id=' + entryId);
		},

		mouseleave: function () {
			let link = $(this).parent().parent().parent().parent().parent().parent().parent();
			let entryId = link.attr('org-id');

			link.attr('href', '?v=2&id=' + entryId);
		}
	});

	$('.navigation-button').on({
		click: function () {
			let currentUrl = window.location.href;

			if (currentUrl.includes('&p=')) {
				let splitUrl = currentUrl.split('&p=');
				let page = parseInt(splitUrl[1].split('&')[0]);

				let newPage = $(this).hasClass('next') ? page + 1 : page - 1;
				newPage = newPage < 0 ? 0 : newPage;

				window.location.href = currentUrl.replace('&p=' + page, '&p=' + newPage);
				return;
			}
			window.location.href = window.location.href + '&p=1'
		}
	});
}

$(document).ready(function()
{
	if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
		isMobile = true;
	}
	listInitialise();
});

// window.onscroll = function () {
// 	let scrollHeight = document.documentElement.scrollHeight;
// 	let scrollTop = window.document.documentElement.scrollTop;
// 	let clientHeight = document.documentElement.clientHeight;
//
// 	let element = $('#data');
//
// 	let dataSearch = element.attr('data-search');
// 	let dataChar = element.attr('data-char');
// 	let dataDeveloper = element.attr('data-developer');
// 	let dataType = element.attr('data-type');
// 	let dataBy = element.attr('data-by');
// 	let dataOrder = element.attr('data-order');
// 	let dataLoaded = element.attr('data-loaded') ? element.attr('data-loaded') : 1;
//
// 	if (dataType !== 'd' && (scrollTop + clientHeight) >= scrollHeight && (($('.list-item').length % 25) === 0 ||
// 		(dataType === '3d' || dataType === 'ova')) && dataLoaded !== 'done') {
// 		let dataObject = {
// 			v: 2,
// 			a: 'listItems',
// 			s: dataSearch,
// 			char: dataChar,
// 			developer: dataDeveloper,
// 			loaded: dataLoaded,
// 			l: dataType[0],
// 			by: dataBy,
// 			order: dataOrder,
// 		};
// 		$.ajax({
// 			url: 'index.php',
// 			type: 'POST',
// 			data: dataObject,
// 			dataType: "json",
// 		})
// 			.done(function(response)
// 			{
// 				if (response.success === true && response.items !== '' && response.items !== 'null') {
// 					let list = $('#list');
// 					list.html(list.html() + response.items);
//
// 					dataLoaded = (parseInt(dataLoaded) + 1) + '';
//
// 					element.attr('data-loaded', dataLoaded);
// 				} else if (response.items !== 'null') {
// 					element.attr('data-loaded', 'done');
// 				}
// 			});
// 	}
// };
