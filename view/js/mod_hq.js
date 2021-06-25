$(document).ready(function() {

	$(document).on('click', '.jot-toggle', function(e) {
		$(window).scrollTop(0);
		$('#jot-popup').toggle();
		$('#profile-jot-text').focus();
	});

	$(document).on('click', '.notes-toggle', function(e) {
		$(window).scrollTop(0);
		$('#personal-notes').toggleClass('d-none');
		$('#note-text').focus();
	});

	$(document).on('hz:handleNetworkNotificationsItems', function(e, obj) {
		push_notification($('<p>' + obj.message + '</p>').text(), obj.name);
	});

});
