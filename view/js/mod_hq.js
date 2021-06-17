$(document).ready(function() {

	$(document).on('click', '#jot-toggle', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(window).scrollTop(0);
		$('#jot-popup').toggle();
		$('#profile-jot-text').focus();

	});

	$(document).on('click', '#notes-toggle', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(window).scrollTop(0);
		$('#personal-notes').toggleClass('d-none');
		$('#note-text').focus();
	});

});
