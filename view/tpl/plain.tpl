<h2>{{$title}}</h2>
{{if $now}}<div>{{$now}}</div>{{/if}}
<div style="font-weight: normal; font-family: monospace;">{{$infos}}</div>
<script>
	$('.register_date').each( function () {
		var date = new Date($(this).data('utc'));
		$(this).html(date.toLocaleString());
	});
</script>
