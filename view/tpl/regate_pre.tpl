<h2>{{$title}}</h2>

{{$delay_desc.0}}
<div id="countdown" class="h3"></div>

{{$desc.0}}<br>
<br>
{{$desc.1}}<br>
{{$id}}<br>
<br>
{{$desc.2}}<br>
{{$pin}}<br>
<br>
<div class="">
	{{$delay_desc.1}}<br>
	<span id="register_start" data-utc="{{$regdelay}}" class="register_date">
		{{$regdelay}}
	</span>
	&nbsp;&dash;&nbsp; 
	<span data-utc="{{$regexpire}}" class="register_date">
		{{$regexpire}}
	</span>
</div>
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
