<div class="d-grid gap-2 mb-3">
	{{foreach $entries as $e}}
	<button id="{{$e.id}}" class="{{$e.class}} rounded-circle" type="{{$e.type}}" title="{{$e.label}}"{{if $extra}} {{$extra}}{{/if}}>
		{{if $e.icon}}<i class="fa fa-{{$e.icon}}"></i>{{/if}}
	</button>
	{{/foreach}}
</div>
