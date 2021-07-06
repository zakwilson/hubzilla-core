$(document).ready(function() {

	$(document).on('click', '.jot-toggle', function(e) {
		$(window).scrollTop(0);
		$(document).trigger('hz:hqControlsClickAction');
		$('#jot-popup').toggle();
		$('#profile-jot-text').focus();
	});

	$(document).on('click', '.notes-toggle', function(e) {
		$(window).scrollTop(0);
		$(document).trigger('hz:hqControlsClickAction');
		$('#personal-notes').toggleClass('d-none');
		$('#note-text').focus();
	});

	$(document).on('hz:handleNetworkNotificationsItems', function(e, obj) {
		push_notification(
			obj.name,
			$('<p>' + obj.message + '</p>').text(),
			baseurl + '/hq/' + obj.b64mid
		);
	});

});
