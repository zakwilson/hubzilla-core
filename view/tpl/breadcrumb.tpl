<nav aria-label="breadcrumb">
	<ol class="breadcrumb bg-transparent">
		{{foreach $breadcrumbs as $breadcrumb}}
		{{if $breadcrumb@last}}
		<li class="breadcrumb-item active h3 pt-3 pb-3" aria-current="page">{{$breadcrumb.name}}</li>
		{{else}}
		<li class="breadcrumb-item h3 cloud-index attach-drop pt-3 pb-3" data-folder="{{$breadcrumb.hash}}" title="{{$breadcrumb.hash}}"><a href="{{$breadcrumb.path}}">{{$breadcrumb.name}}</a></li>
		{{/if}}
		{{/foreach}}
	</ol>
</nav>
