$(document).ready(function()
{
	$('.home-url').click(function () {
		localStorage.clear();

		window.location.href = '/';
	});

	$('.banner_btn').mouseover(function()
	{
		$('#header_a img').css('background', '#3b2919');
	});

	$('.banner_btn').mouseout(function()
	{
		$('#header_a img').css('background', '#000');
	});

	$('.banner_btn').mousedown(function()
	{
		$('#header_a img').css('background', '#401919');
	});
	//
	// $('#home').click(function()
	// {
	// 	window.location.href = '?v=2';
	//
	// 	localStorage.clear();
	// });

	$('#upcoming').click(function()
	{

	});

	$('#home_btn').click(function () {
	});

	$('#games').click(function() {
		direct('?v=2&l=g');
	});

	$('#ovas').click(function() {
		direct('?v=2&l=o');
	});

	$('#d3').click(function() {
		direct('?v=2&l=3');
	});

	$('#developers').click(function() {
		direct('?v=2&l=d');
	});

	$('#characters').click(function() {
		direct('?v=2&l=c');
	});

	$('#upcoming_btn').click(function() {
		localStorage.clear();
	});

	$('#header-search').submit(function (e) {
		e.preventDefault();

		let search = $('input[name="header-text"]').val();

		let extraData = 'accept-charset="UTF-8"';

		direct('?v=2&l=a&s=' + search, extraData);
	});

	$('#switch_source').click(function(e) {
		e.preventDefault();

		window.location.href = $(this).attr('url');
	});

	$('#random_button').click(() => {
		localStorage.removeItem('random');
	});
});