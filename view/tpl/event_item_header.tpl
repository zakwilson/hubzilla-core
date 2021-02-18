<div class="event-item-title">
	<h3><i class="fa fa-calendar"></i>&nbsp;{{$title}}</h3>
</div>
{{if $oneday && $allday}}
<span class="dtstart">{{$dtstart_dt}}</span>
{{else if $allday}}
<span class="dtstart">{{$dtstart_dt}}</span> &mdash; <span class="dtend">{{$dtend_dt}}</span>
{{else}}
<div class="event-item-start">
	<span class="event-item-label">{{$dtstart_label}}</span>&nbsp;<span class="dtstart" title="{{$dtstart_title}}">{{$dtstart_dt}}</span>
</div>
{{if $finish}}
<div class="event-item-start">
	<span class="event-item-label">{{$dtend_label}}</span>&nbsp;<span class="dtend" title="{{$dtend_title}}">{{$dtend_dt}}</span>
</div>
{{/if}}
{{/if}}
{{if $event_tz.value}}
<div class="event-item-start">
	<span class="event-item-label">{{$event_tz.label}}:</span>&nbsp;<span class="timezone" title="{{$event_tz.value}}">{{$event_tz.value}}</span>
</div>
{{/if}}
