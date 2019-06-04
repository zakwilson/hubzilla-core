$(document).ready(function() {
	$('#id_description').editor_autocomplete(baseurl + "/acl");
	$('textarea').bbco_autocomplete('bbcode');
});
