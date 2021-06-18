<div class="mb-3{{if $wrapper_class}} {{$wrapper_class}}{{/if}}">
	{{foreach $entries as $e}}
	<button id="{{$e.id}}" class="{{$e.class}} rounded-circle{{if $entry_class}} {{$entry_class}}{{/if}}" type="{{$e.type}}" title="{{$e.label}}"{{if $e.extra}} {{$e.extra}}{{/if}}>
		{{if $e.icon}}<i class="fa fa-{{$e.icon}}"></i>{{/if}}
	</button>
	{{/foreach}}
</div>
