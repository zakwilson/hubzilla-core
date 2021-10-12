{{if $app}}
<div id="personal-notes" class="generic-content-wrapper{{if $hidden}} d-none{{/if}}">
	<div class="section-title-wrapper">
		<h2>{{$banner}}</h2>
	</div>
	<div class="section-content-wrapper">
{{else}}
<div id="personal-notes" class="widget{{if $hidden}} d-none{{/if}}">
	<h3>{{$banner}}</h3>
{{/if}}
	<textarea name="note_text" id="note-text" class="{{if $app}}form-control border-0{{/if}}">{{$text}}</textarea>
	<script>
		var noteSaveTimer = null;
		var noteText = $('#note-text');

		$(document).on('focusout',"#note-text",function(e){
			if(noteSaveTimer)
				clearTimeout(noteSaveTimer);
			notePostFinal();
			noteSaveTimer = null;
		});

		$(document).on('focusin',"#note-text",function(e){
			noteSaveTimer = setTimeout(noteSaveChanges,10000);
		});

		function notePostFinal() {
			$.post('notes/sync', { 'note_text' : $('#note-text').val() });
		}

		function noteSaveChanges() {
			$.post('notes', { 'note_text' : $('#note-text').val() });
			noteSaveTimer = setTimeout(noteSaveChanges,10000);
		}
	</script>
{{if $app}}
</div>
{{/if}}
</div>
