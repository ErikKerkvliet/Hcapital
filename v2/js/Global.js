var isMobile;

const HOST_RAPIDGATOR = 'rapidgator';
const HOST_MEXASHARE = 'mexashare';
const HOST_KATFILE = 'katfile';
const HOST_ROSEFILE = 'rosefile';
const HOST_DDOWNLOAD = 'ddownload';
const HOST_FIKPER = 'fikper';
const HOST_BIGFILE = 'bigfile';

const HOSTS = [
	HOST_RAPIDGATOR,
	HOST_MEXASHARE,
	HOST_KATFILE,
	HOST_ROSEFILE,
	HOST_DDOWNLOAD,
	HOST_FIKPER,
];

if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
	isMobile = true;
}

function setImage(image, size = null) {
	if (size == null) {
		parent = image.parentNode;

		newImage = new Image(75, 75);

		newImage.src = './images/No Images.jpg';
		newImage.style.border = '1px solid #258';

		parent.replaceChild(newImage, image);
	}
	else {
		image.src = './images/No Image' + size + '.jpg';
	}
}

function globalInitialise()
{
	$('.tumbnail-img').on({

		click: function() {
			let id = $(this).attr('entry-id');
			let url = '?id=' + id;

			direct(url);
		},

		mousemove: function (event) {
			let id = $(this).attr('entry-id');

			let span = $('span[entry-id="' + id + '"]');
			span.show();

			let x = event.pageX + 5;
			let y = event.pageY + 12;
			y -= window.scrollY;
			if (span.attr('id') === 'relation') {

			}


			span.css('left', x + 'px');
			span.css('top', y + 'px');

			span.css({
				"-webkit-transform": 'translate(0%, -105%)',
				"-ms-transform": 'translate(0%, -105%)',
				"transform": 'translate(0%, -105%)'
			});
		},

		mouseleave: function () {
			let id = $(this).attr('entry-id');

			$('span[entry-id="' + id + '"]').hide();
		}
	});

	$('#banner_btn').click(function() {
		url = '/';
		direct(url, {});
	});
}

$(document).ready(function() {
	if (isMobile && ($('.info-table').length > 0 || $('.character').length > 0)) {
		$('body').css('height', $(document).height() + 'px');
	}
	initialise();
});

function direct(url, extra = '')
{
	let form = '<form action="' + url + '" method="post" ' + extra + '>' +
		'<input type="text" name="v" value="2"/>' +
		'</form>';

	let htmlForm = $(form);

	$('body').append(htmlForm);

	htmlForm.submit();
}

function ajax(point, dataObject = {})
{
	let data = null;
	$.ajax({
		url: point + '.php',
		type: 'POST',
		data: dataObject,
		dataType: "json",
	})
	.done(function(response)
	{
		if (response.success === true) {
			data = response;
		}
	});

	return data;
}

function dd(var1=null, var2=null, var3=null, var4=null, var5=null, var6=null, var7=null, var8=null, var9=null, var10=null)
{
	$.each(arguments, function (index, value) {
		console.log(value);
	});
	console.log('----------------------------------------------------------------------------------------------------');
}

function initialise()
{
	globalInitialise();

	try {
		listInitialise();
	} catch (e) {}

	try {
		homeInitialize();
	} catch (e) {}

	try {
		adminInitialise();
	} catch (e) {}
}

$(document).ready(function()
{
	globalInitialise();
});

$(document).on("mouseover", function (e1) {
	$(document).one("mouseup", function (e2) {
		let entry = $('#info_title').attr('data-id');

		if (e1.which === 2 && e1.target === e2.target) {
			let e3 = $.event.fix(e2);
			e3.type = "middleclick";
			$(e2.target).trigger(e3);

			if (e3.target.className === 'link-button') {
				goToUrl(e3.target.id, 2);
			}
		} else if (e1.which === 1 && e1.target === e2.target) {
			let e3 = $.event.fix(e2);
			e3.type = "leftclick";
			$(e2.target).trigger(e3);

			if (e3.target.className === 'link-button') {
				goToUrl(e3.target.id, 1);
			}
		}
	});
});

$(document).ajaxComplete(function () {
	initialise();
});