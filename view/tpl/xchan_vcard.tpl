<div class="card mb-3 h-card">
	<div class="row">
		<div class="col-4">
			<a href="{{$link}}" >
				<img class="u-photo" src="{{$photo}}" alt="{{$name}}" width="80px" height="80px">
			</a>
		</div>
		<div class="col m-1">
			<div class="row">
				<strong class="fn p-name">{{$name}}</strong>
			</div>
			<div class="row">
				<small class="text-muted p-adr">{{$addr}}</small>
			</div>
			{{if $connect}}
			<div class="row mt-2">
				<a href="follow?f=&url={{$follow}}" class="btn btn-success btn-sm" rel="nofollow">
					<i class="fa fa-plus"></i> {{$connect}}
				</a>
			</div>
			{{/if}}
		</div>
	</div>
</div>

