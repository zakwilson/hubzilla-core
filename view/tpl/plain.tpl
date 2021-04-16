<h2>{{$title}}</h2>
{{if $now}}<div>{{$now}}</div>{{/if}}
<div style="font-weight: normal; font-family: monospace;">{{$infos}}</div>
<div id="countdown" class="h3"></div>
<script>
	$('.register_date').each( function () {
		var date = new Date($(this).data('utc'));
		$(this).html(date.toLocaleString());
	});


	var date = '{{$countdown}}';

	date = date !== '' ? date : $('#register_start').data('utc');

	if(date) {
		doCountDown(date, 'countdown');
		var x = setInterval(doCountDown, 1000, date, 'countdown');
	}
	function doCountDown(date, id) {
		var countDownDate = new Date(date).getTime();
		var now = new Date().getTime();
		var distance = countDownDate - now;
		var days = Math.floor(distance / (1000 * 60 * 60 * 24));
		var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		var seconds = Math.floor((distance % (1000 * 60)) / 1000);

		document.getElementById(id).innerHTML = days + "d " + hours + "h "+ minutes + "m " + seconds + "s ";

		if (distance < 0) {
			clearInterval(x);
			document.getElementById(id).innerHTML = 'Reloading...';
			window.location.reload();
		}
	}
</script>
