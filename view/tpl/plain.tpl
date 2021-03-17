<h2>{{$title}}</h2>
{{if $now}}<div>{{$now}}</div>{{/if}}
<div style="font-weight: normal; font-family: monospace;">{{$infos}}</div>
<script>
	$('.register_date').each( function () {
		var UTC = $(this).html();
		var date = new Date(UTC);
		$(this).html(date.toLocaleString());
	});
</script>
