$(document).ready(function() {
	let selectedTab = '';

	$('#wrapper').css('min-height', window.innerHeight + 'px');
	if (isMobile) {
		$('.list-item').css('border', 'none');
	}

	$('.selected').css('border-bottom', 'inset 1px #405062');
	$('.selected').css('background', '#405062');
	$('div[data-type="all"]').css('width', '40px');

	$('.search-tab').on({
		click: function () {
			selectedTab = $(this).attr('data-type');

			let tabs = $('.search-tab');

			tabs.removeClass('selected')
			tabs.css('border-bottom', 'inset 0px #405062');
			tabs.css('background', '#637787');

			$(this).addClass('selected');
			$(this).css('border-bottom', 'inset 1px #405062');
			$(this).css('background', '#405062');
		},

		mouseleave: function () {
			if (selectedTab === '') {
				return;
			}
			let tab = $('.search-tab');
			tab.css('border-bottom', 'inset 0px #405062');
			tab.css('background', '#637787');

			let selected = $('div[data-type="' + selectedTab + '"]');
			selected.css('border-bottom', 'inset 1px #405062');
			selected.css('background', '#405062');
		},

		mouseenter: function () {
			if (selectedTab === '') {
				return;
			}
			$(this).css('border-bottom', 'inset 1px #405062');
			$(this).css('background', '#405062');
		},
	});

	$('#search-form').submit(function(e) {
		e.preventDefault();

		let search = $('#search-input').val();

		let url = '?v=2&s=' + search;
		$('.selected').each(function () {
			url += '&l=' + $(this).attr('data-type')[0];

			direct(url);
		});
		direct(url);
	});

	$('.sort-char').click(function() {
		$('.selected').each(function () {
			type = $(this).attr('data-type');
		});

		let char = $(this).attr('data-char');
		let url = '?v2';
		url += '&l=' + type[0];
		url += '&c=' + char;

		move('&c=' + char);
	});

	$('.order-button').click(function () {
		move($(this).attr('id'));
	});
});

function move(id = '')
{
	let splitUrl = window.location.href.split('?');

	let gets = splitUrl[1].split('&');

	let approved  = ['p', 'l', 'v', 's', 'c', 'did', 'cid', 't'];

	let getString = '?';

	gets.forEach(function(item) {
		let split = item.split('=');
		if (approved.includes(split[0])) {
			let add = getString === '?' ? '' : '&';
			getString += add + split[0] + '=' + split[1];
		}
	});
	let add = getString === '?' ? '' : '&';

	getString += getString.includes('v=2') ? '' : add + 'v=2';

	if (getString.includes('&p=')) {
		let splitUrl = getString.split('&p=');
		let page = parseInt(splitUrl[1].split('&')[0]);

		getString = getString.replace('&p=' + page, '&p=0');
	}

	if (id.includes('&c=')) {
		getString = getString.replace( new RegExp("(&c=.*)","gm"),"");
		window.location.href = splitUrl[0] + getString + id;
		return;
	}

	if (id.includes('&t=')) {
		getString = getString.replace( new RegExp("(&t=.*)","gm"),"");
		window.location.href = splitUrl[0] + getString + id;
		return;
	}

	let sortVars = id.split('-');

	getString += '&by=' + sortVars[0];
	getString += '&order=' + sortVars.pop();

	window.location.href = splitUrl[0] + getString;
}