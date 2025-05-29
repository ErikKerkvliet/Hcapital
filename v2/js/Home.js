let pageType = '';
let pageScrollTop = 250;
let navigator = null;
$(document).ready(function()
{
	if ($('#names')) {
		$('#names').focus();
	}
	
	$('.item_image').click(function(e){
		var entry = "#" + $(this).attr('id');

		localStorage.setItem('entry', entry)
	});

	navigator = $('#navigator');
	pageType = navigator.attr('type');
	pageScrollTop = pageType === 'upcoming' ? 100 : 250;

	if (pageType === 'home') {
		//navigator.css('top', '566px');
	} else {
		navigator.css('top', '346px');
	}

	if (pageType === 'home' && localStorage.getItem('last-games') === null) {
		localStorage.setItem('last-games', 1);
		localStorage.setItem('last-ovas', 1);

		localStorage.setItem('latest-games', 1);
		localStorage.setItem('latest-ovas', 1);
	} else if (pageType === 'upcoming' && localStorage.getItem('upcoming-games') === null) {
		localStorage.setItem('upcoming-games', 1);
		localStorage.setItem('upcoming-ovas', 1);
	}

	firstLoad();

	$('.last-added-navigation').click(function (e) {
		let type = $(this).attr('entry-type');

		if ($(this).hasClass('button-left')) {
			firstLoad('last', type, 'min');
		} else {
			firstLoad('last', type, 'plus');
		}

		$('#arrow-top').addClass('last');
	});

	$('.change-page').click(function (e) {
		let type = $(this).attr('entry-type');
		let entryType =  $('#navigator').attr('type') === 'upcoming' ? 'upcoming' : 'latest';

		let buttons = $('#navigator-' + type).find('.number-button');
		$.each(buttons, function(key, button) {
			if ($(this).hasClass('selected')) {
				$(this).removeClass('selected');
			}
			$(this).attr('style', '');
		});

		if ($(this).hasClass('arrow-up')) {
			firstLoad(entryType, type, 'min');
		} else {
			firstLoad(entryType, type, 'plus');
		}
	});

	$('.number-button').click(function () {
		let entryType =  $('#navigator').attr('type') === 'upcoming' ? 'upcoming' : 'latest';
		let type = $(this).parent().attr('id').split('-')[1];

		let pageButtonId = 'button-' + type + '-id';
		let page = $(this).attr(pageButtonId).split('-')[1];

		let buttons = $('#navigator-' + type).find('.number-button');

		$.each(buttons, function() {
			if ($(this).hasClass('selected')) {
				$(this).removeClass('selected');
			}
			$(this).attr('style', '');
		});

		firstLoad(entryType, type, '', parseInt(page)-1);

		let storageItem = entryType + '-' +  type + 's';

		localStorage.setItem(storageItem, parseInt(page));

		$(this).addClass('selected');
	});

	$('.arrow-top').click(function(){
		if (($('#navigator').attr('type') === 'home' && $(document).scrollTop() > 256) ||
			(($('#navigator').attr('type') === 'upcoming') && $(document).scrollTop() > 110)) {
			$(document).scrollTop(0);
			$('#navigator').css('top', 208 + 'px');
		}
	});
});

function firstLoad(timeType = 'first', type = 'all', buttonType = '', newPage = null) {
	let storageItem = timeType + '-' + type + 's';
	let currentPage = localStorage.getItem(storageItem);
	let current = currentPage + 1;

	let elementId = 'div[button-' + type + '-id="nr-' + current + '"]';

	$(elementId).addClass('selected');

	let page = buttonType === 'min' ? currentPage - 1 : parseInt(currentPage) + 1;
	page = buttonType === '' ? 1 : page;

	let dataObject = {
		v: 2,
		timeType: timeType,
		type: type,
	};

	if (timeType === 'first') {
		if (pageType === 'home') {
			dataObject.og = localStorage.getItem('last-games') - 1;
			dataObject.oo = localStorage.getItem('last-ovas') - 1;

			games = localStorage.getItem('latest-games');
			ovas = localStorage.getItem('latest-ovas');
		} else {
			dataObject.u = true;
			games = localStorage.getItem('upcoming-games');
			ovas = localStorage.getItem('upcoming-ovas');
		}

		$('div[button-game-id="nr-' + games + '"]').addClass('selected');
		$('div[button-ova-id="nr-' + ovas + '"]').addClass('selected');

		if (pageType === 'home') {
			dataObject.ng = games - 1;
			dataObject.no = ovas - 1;
		} else {
			dataObject.ug = games - 1;
			dataObject.uo = ovas - 1;
		}
	} else if (newPage === null) {
		let buttonsCount = $('#navigator-' + type).find('.number-button').length;

		page = page > 0 ? page : buttonsCount;
		page = page < buttonsCount + 1 ? page : 1;

		if (timeType === 'latest' || timeType === 'upcoming') {
			let elementId = 'div[button-' + type + '-id="nr-' + page + '"]';

			$(elementId).addClass('selected');

			$(elementId).css('background-color', '#405062');
		}
		dataObject.page = page - 1;
	}
	if (timeType !== 'first') {
		localStorage.setItem(storageItem, page);
	}
	dataObject.page = newPage ? newPage : dataObject.page;

	if (pageType === 'upcoming' && ! dataObject.ug) {
		dataObject.ug = 0;
	}
	$.ajax({
		url: 'index.php',
		type: 'POST',
		data: dataObject,
		dataType: "json",
	})
	.done(function(response)
	{
		if (response.success === true) {
			if (type === 'all') {
				$('#last-games').html(response.lastGames);
				$('#last-ovas').html(response.lastOvas);

				$('#latest-games').html(response.latestGames);
				$('#latest-ovas').html(response.latestOvas);

				return;
			}
			if (response.lastGames !== null) {
				$('#last-games').html(response.lastGames);
				return;
			}
			if (response.lastOvas !== null) {
				$('#last-ovas').html(response.lastOvas);
				return;
			}
			if (response.latestGames !== null) {
				$('#latest-games').html(response.latestGames);
				return;
			}
			if (response.latestOvas !== null) {
				$('#latest-ovas').html(response.latestOvas);
			}
		}
	});
}

function scroll_view(id){
	if (id !== 0) {
		$(document).scrollTop($('#' + id).parent().offset().top - 300)
	}
}

function homeInitialize() {
	let id = localStorage.getItem('entry');
	if (id != null && document.getElementById(id) !== null && ! $('#arrow-top').hasClass('last') &&
		window.performance && window.performance.navigation.type == window.performance.navigation.TYPE_BACK_FORWARD) {
		window.setTimeout(scroll_view, 250, id);
	}

	$('.href').mouseover(function () {
		var url = window.location.href.split('://')[1] + '?v=2&id=' + $(this).attr('name');
		var style = "position: fixed; " +
			"left: 0; " +
			"bottom: 0; " +
			"z-index: 1000000;" +
			"background:#ffffff; " +
			"height:22px; " +
			"padding-bottom:3px;" +
			"padding-top:3px;" +
			"padding-right:5px;" +
			"padding-left:6px;" +
			"border-radius: 0 3px 0 0;" +
			"color: #323a3cc7;" +
			"font-weight: 500;" +
			"font-size:13px;";

		$('body').append("<b id='urlDisplay' style='" + style + "'>" + url + "</b>");
	});
	$('.href').mouseout(function () {
		$('#urlDisplay').remove();
	});

	$('.href').click(function () {
		localStorage.setItem('entry', $(this).attr('id'));
		window.location.href = $(this).attr('href');
	});
}

let fixed = false;
window.onscroll = function () {
	let scrollTop = window.document.documentElement.scrollTop;

	if (! navigator) {
		return;
	}
	if (scrollTop > pageScrollTop && ! fixed) {
		if (pageType === 'home') {
			navigator.css('top', '316px');
		} else {
			navigator.css('top', '238px');
		}

		navigator.css('left', 'calc(50% - 42.4px)');
		navigator.css('position', 'fixed');

		fixed = true;
	} else if (scrollTop < pageScrollTop && fixed) {
		if (pageType === 'home') {
			navigator.css('top', '566px');
		} else {
			navigator.css('top', '346px');
		}
		navigator.css('left', 'calc(50% - 42.4px)');
		navigator.css('position', 'absolute');

		fixed = false;
	}
};
