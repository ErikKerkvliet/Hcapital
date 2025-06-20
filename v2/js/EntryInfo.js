var imageIsUp = false;
var isMobile;

$(document).ready(function()
{
	let entryId = $('#info-title').attr('data-id');
	let imageCount = $('.image-table').find('td').length;
	let singleRowCount = $('#single').find('td').length;

	$('.link-button').mouseup(function(e) {
		let id = $(this).attr('data-link-id');
		if (e.which === 1) {
			goToUrl(id, 1)
		} else if (e.which === 2) {
			goToUrl(id, 2)
		}
	});
	
	$('.char-imgs img').each(function (e) {
		if (this.height >= this.width) {
			var width = this.width / this.height;

			$(this).width(57 * width);
			$(this).height(57);
		}
		else {
			var height = this.height / this.width;

			$(this).width(57);
			$(this).height(57 * height);
		}
	});

	$('div[image-id="1"]').find('#arrow-previous').hide();
	$('div[image-id="6"]').find('#arrow-previous').hide();
	$('#multiple').hide();

	$('#single').find('div[image-id="' + singleRowCount + '"]').find('#arrow-next').hide();
	$('div[image-id="' + imageCount + '"]').find('#arrow-next').hide();


	$('#show-more').click(function () {
		if ($(this).html() === "- less images") {
			$(this).html("+ more images");
			$('#single').show();
			$('#multiple').hide();
		} else {
			$(this).html("- less images");
			$('#single').hide();
			$('#multiple').show();
		}
	});

	$('#show-chars').click(function () {
		if ($(this).html() === "- characters") {
			$(this).html("+ characters");
			$('#characters-table').hide()
			$('#links').css('margin-right', '0px');
		} else {
			$(this).html("- characters");
			$('#characters-table').show();
			$('#links').css('margin-right', '17px');
		}

		$(this).parent().find('.chars').toggle();
		$('.div_img').hide();
	});

	$('.arrow-button').click(function (e) {
		let side = $(this).attr('id').split('-').pop();
		let imageId = $(this).parent().parent().attr('image-id');

		if (side === 'previous') {
			if (imageId >= 0) {
				imageId--;
			}
		} else {
			if (imageId <= imageCount - 1) {
				imageId++;
			}
		}
		let otherImage = 'div[image-id="' + imageId + '"]';
		$(this).parent().parent().hide();
		$(otherImage).show();

		position($(otherImage), true);
	});

	$('.div_img').bind('mousewheel', function (e) {
		if ($(this).attr('id') !== 'div_img') {
			return;
		}
		e.preventDefault();
		let imageId = $(this).attr('image-id');
		if (e.originalEvent.wheelDelta / 120 > 0) {
			if (imageId >= 2) {
				imageId--;
			}
		} else {
			let visible = $('#single').is(':visible');
			if (visible && imageId <= singleRowCount - 1) {
				imageId++;
			} else if (! visible && imageId <= imageCount - 1) {
				imageId++;
			}
		}
		let otherImage = 'div[image-id="' + imageId + '"]';
		if (! ($('#multiple').is(':visible') && imageId === 5)) {
			$(this).hide();
		}
		$(otherImage).show();

		position($(otherImage), true);
	});

	$('.static_img').click(function() {
		$('.div_img').hide();

		element = $(this).parent().find('.div_img')
		element.show();

		imageIsUp = true;
		position(element);
	});

	$('.static_img').mouseover(function () {
		$(this).css({"border": "3px solid #2B5A7B"});
	});

	$('.static_img').mouseout(function () {
		$(this).css({"border": "3px solid #232424"});
	});

	$('.char_imgs').click(function() {
		$('.div_img').hide();

		element = $(this).parent().find('.div_img')
		element.show();

		imageIsUp = true;
		position(element);
	});

	$('.char_imgs img').each(function (e)
	{
		if (this.height >= this.width)
		{
			var width = this.width / this.height;

			$(this).width(57 * width);
			$(this).height(57);
		}
		else
		{
			var height = this.height / this.width;

			$(this).width(57);
			$(this).height(57 * height);
		}
	});
});

let topSpace = 0;
function position(element, arrow = false) {
	let elementWidth = element.width();
	let elementHeight = element.height();

	let halfWidth = Math.floor((elementWidth / 2) + 50);
	let scrolledDown = $(window).scrollTop();

	topSpace = arrow ? topSpace : scrolledDown + 125;

	topSpace = elementHeight < 580 ? topSpace : scrolledDown + 50;
	element.css('top', topSpace + 'px');
	element.css('left', 'calc(50% - ' + halfWidth + 'px)');
}

function goToUrl(id, mouseClickType)
{
	$.ajax({
		url: 'index.php',
		type: 'POST',
		data: {
			v: 2,
			a: 'link',
			lid: id,
		},
		dataType: "json",
	})
	.done(function(response)
	{
		if (response.success === true) {
			if (mouseClickType === 1) {
				window.open(response.link, '_self');
			} else {
				window.open(response.link, '_blank');
			}
		} else {
			alert(response.comment);
		}
	});
}

$(document).on("mousedown", function (e1) {
	imageIsUp = false;
	$(document).one("mouseup", function (e2) {
		$('.div_img').hide();
	});
});

window.onscroll = function () {
	if (addChar) {
		addChar.css('top', '319px');

		addChar.css('left', 'calc(50% - 604px)');
		addChar.css('position', 'fixed');
	}
};


