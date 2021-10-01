<h3>Channel clone status: 100%</h3>

<div>
	<div class="progress mb-2">
		<div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
	<div>
		<span class="text-muted">Channel cloning completed!</span>
	</div>
</div>

<hr>

<h3>Item sync status: <span id="cprogress-label">{{$cprogress_str}}</span></h3>

<div id="cprogress">
	<div class="progress mb-2">
		<div id="cprogress-bar" class="progress-bar progress-bar-striped bg-warning{{if $cprogress < 100}} progress-bar-animated{{/if}}" role="progressbar" style="width: {{$cprogress}}%" aria-valuenow="{{$cprogress}}" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
	<div id="cprogress-resume" class="{{if $cprogress == 100}}d-none{{/if}}">
		<a href="/import_progress/resume_itemsync">[ RESUME ]</a> <span class="text-muted">Only resume if sync stalled!</span>
	</div>
	<div id="cprogress-complete" class="{{if $cprogress < 100}}d-none{{/if}}">
		<span class="text-muted">Item sync completed!</span>
	</div>
</div>

<hr>

<h3>File sync status: <span id="fprogress-label">{{$fprogress_str}}</span></h3>

<div id="fprogress">
	<div  class="progress mb-2">
		<div id="fprogress-bar" class="progress-bar progress-bar-striped bg-info{{if $fprogress < 100}} progress-bar-animated{{/if}}" role="progressbar" style="width: {{$fprogress}}%" aria-valuenow="{{$fprogress}}" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
	<div id="fprogress-resume" class="{{if $fprogress == 100}}d-none{{/if}}">
		<a href="/import_progress/resume_filesync">[ RESUME ]</a> <span class="text-muted">Only resume if sync stalled!</span>
	</div>
	<div id="fprogress-complete" class="{{if $fprogress < 100}}d-none{{/if}}">
		<span class="text-muted">File sync completed!</span>
	</div>
</div>
