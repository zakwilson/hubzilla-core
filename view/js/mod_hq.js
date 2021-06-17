$(document).ready(function() {

	$(document).on('click', '#jot-toggle', function(e) {
		$(window).scrollTop(0);
		$('#jot-popup').toggle();
	});

	$(document).on('click', '#notes-toggle', function(e) {
		$(window).scrollTop(0);
		$('#personal-notes').toggleClass('d-none');
	});

});
