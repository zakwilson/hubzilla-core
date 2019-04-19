<script>
var calendar;
var new_event = {};
var new_event_id = Math.random().toString(36).substring(7);
var views = {'dayGridMonth' : '{{$month}}', 'timeGridWeek' : '{{$week}}', 'timeGridDay' : '{{$day}}', 'listMonth' : '{{$list_month}}', 'listWeek' : '{{$list_week}}', 'listDay' : '{{$list_day}}'};

$(document).ready(function() {
	var calendarEl = document.getElementById('calendar');
	calendar = new FullCalendar.Calendar(calendarEl, {
		plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'list' ],
		eventSources: [ {{$sources}} ],
		
		timeZone: '{{$timezone}}',

		locale: '{{$lang}}',

		eventTextColor: 'white',
		header: false,
		
		height: 'auto',
		
		firstDay: {{$first_day}},

		monthNames: aStr['monthNames'],
		monthNamesShort: aStr['monthNamesShort'],
		dayNames: aStr['dayNames'],
		dayNamesShort: aStr['dayNamesShort'],
		allDayText: aStr['allday'],

		defaultTimedEventDuration: '01:00:00',
		snapDuration: '00:15:00',
		
		dateClick: function(info) {
			if(new_event.id) {
				var event_poi = calendar.getEventById(new_event.id);
				event_poi.remove();
				new_event = {};
			}

			var dtend = new Date(info.date.toUTCString());
			if(info.view.type == 'dayGridMonth') {
				dtend.setDate(dtend.getDate() + 1);
			}
			else{
				dtend.setHours(dtend.getHours() + 1);
			}

			$('#event_uri').val('');
			$('#id_title').val('New event');
			$('#calendar_select').val($("#calendar_select option:first").val()).attr('disabled', false);
			$('#id_dtstart').val(info.date.toUTCString());
			$('#id_dtend').val(dtend ? dtend.toUTCString() : '');
			$('#id_description').val('');
			$('#id_location').val('');
			$('#event_submit').val('create_event').html('Create');
			$('#event_delete').hide();

			new_event = { id: new_event_id, title  : 'New event', start: $('#id_dtstart').val(), end: $('#id_dtend').val(), editable: true, color: '#bbb' };
			calendar.addEvent(new_event);
		},
		
		eventClick: function(info) {

			var event = info.event._def;
			var dtstart = new Date(info.event._instance.range.start);
			var dtend = new Date(info.event._instance.range.end);
			
			if(event.publicId == new_event_id) {
				$(window).scrollTop(0);
				$('.section-content-tools-wrapper, #event_form_wrapper').show();
				$('#recurrence_warning').hide();
				$('#id_title').focus().val('');
				return false;
			}

			if(new_event.id && event.extendedProps.rw) {
				var event_poi = calendar.getEventById(new_event.id);
				event_poi.remove();
				new_event = {};
			}
			
			if(!event.extendedProps.recurrent) {
				$(window).scrollTop(0);
				$('.section-content-tools-wrapper, #event_form_wrapper').show();
				$('#recurrence_warning').hide();
				$('#event_uri').val(event.extendedProps.uri);
				$('#id_title').val(event.title);
				$('#calendar_select').val(event.extendedProps.calendar_id[0] + ':' + event.extendedProps.calendar_id[1]).attr('disabled', true);
				$('#id_dtstart').val(dtstart.toUTCString());
				$('#id_dtend').val(dtend.toUTCString());
				$('#id_description').val(event.extendedProps.description);
				$('#id_location').val(event.extendedProps.location);
				$('#event_submit').val('update_event').html('Update');
				if(event.extendedProps.rw) {
					$('#event_delete').show();
					$('#event_submit').show();
					$('#id_title').focus();
					$('#id_title').attr('disabled', false);
					$('#id_dtstart').attr('disabled', false);
					$('#id_dtend').attr('disabled', false);
					$('#id_description').attr('disabled', false);
					$('#id_location').attr('disabled', false);
				}
				else {
					$('#event_submit').hide();
					$('#event_delete').hide();
					$('#id_title').attr('disabled', true);
					$('#id_dtstart').attr('disabled', true);
					$('#id_dtend').attr('disabled', true);
					$('#id_description').attr('disabled', true);
					$('#id_location').attr('disabled', true);
				}
			}
			else if(event.extendedProps.recurrent && event.extendedProps.rw) {
				$('.section-content-tools-wrapper, #recurrence_warning').show();
				$('#event_form_wrapper').hide();
				$('#event_uri').val(event.extendedProps.uri);
				$('#calendar_select').val(event.extendedProps.calendar_id[0] + ':' + event.extendedProps.calendar_id[1]).attr('disabled', true);
			}
		},
		
		eventResize: function(info) {
			
			var event = info.event._def;
			var dtstart = new Date(info.event._instance.range.start);
			var dtend = new Date(info.event._instance.range.end);
			
			$('#id_title').val(event.title);
			$('#id_dtstart').val(dtstart.toUTCString());
			$('#id_dtend').val(dtend.toUTCString());

			$.post( 'cdav/calendar', {
				'update': 'resize',
				'id[]': event.extendedProps.calendar_id,
				'uri': event.extendedProps.uri,
				'dtstart': dtstart ? dtstart.toUTCString() : '',
				'dtend': dtend ? dtend.toUTCString() : ''
			})
			.fail(function() {
				info.revert();
			});
		},
		
		eventDrop: function(info) {

			var event = info.event._def;
			var dtstart = new Date(info.event._instance.range.start);
			var dtend = new Date(info.event._instance.range.end);
			
			$('#id_title').val(event.title);
			$('#id_dtstart').val(dtstart.toUTCString());
			$('#id_dtend').val(dtend.toUTCString());
		
			$.post( 'cdav/calendar', {
				'update': 'drop',
				'id[]': event.extendedProps.calendar_id,
				'uri': event.extendedProps.uri,
				'dtstart': dtstart ? dtstart.toUTCString() : '',
				'dtend': dtend ? dtend.toUTCString() : ''
			})
			.fail(function() {
				info.revert();
			});
		},
		
		loading: function(isLoading, view) {
			$('#events-spinner').show();
			$('#today-btn > i').hide();
			if(!isLoading) {
				$('#events-spinner').hide();
				$('#today-btn > i').show();
			}
		}
		
	});
	
	calendar.render();
	
	$('#title').text(calendar.view.title);
	$('#view_selector').html(views[calendar.view.type]);
	
	$('#today-btn').on('click', function() {
		calendar.today();
		$('#title').text(calendar.view.title);
	});
	
	$('#prev-btn').on('click', function() {
 		 calendar.prev();
 		 $('#title').text(calendar.view.title);
	});
	
	$('#next-btn').on('click', function() {
 		 calendar.next();
 		 $('#title').text(calendar.view.title);
	});
	
	$('.color-edit').colorpicker({ input: '.color-edit-input' });

	$(document).on('click','#fullscreen-btn', updateSize);
	$(document).on('click','#inline-btn', updateSize);
	$(document).on('click','#event_submit', on_submit);
	$(document).on('click','#event_more', on_more);
	$(document).on('click','#event_cancel, #event_cancel_recurrent', reset_form);
	$(document).on('click','#event_delete, #event_delete_recurrent', on_delete);
});


function changeView(action, viewName) {
	calendar.changeView(viewName);
	$('#title').text(calendar.view.title);
	$('#view_selector').html(views[calendar.view.type]);
	return;
}

function add_remove_json_source(source, color, editable, status) {
	var parts = source.split('/');
	var id = parts[4];
	
	var eventSource = calendar.getEventSourceById(id);
	var selector = '#calendar-btn-' + id;

	if(status === undefined)
		status = 'fa-calendar-check-o';

	if(status === 'drop') {
		eventSource.remove();
		reset_form();
		return;
	}

	if($(selector).hasClass('fa-calendar-o')) {
		calendar.addEventSource({ id: id, url: source, color: color, editable: editable });
		$(selector).removeClass('fa-calendar-o');
		$(selector).addClass(status);
		$.get('/cdav/calendar/switch/' + id + '/1');
	}
	else {
		eventSource.remove();
		$(selector).removeClass(status);
		$(selector).addClass('fa-calendar-o');
		$.get('/cdav/calendar/switch/' + id + '/0');
	}
}

function updateSize() {
	calendar.updateSize();
}

function on_submit() {
	$.post( 'cdav/calendar', {
		'submit': $('#event_submit').val(),
		'target': $('#calendar_select').val(),
		'uri': $('#event_uri').val(),
		'title': $('#id_title').val(),
		'dtstart': $('#id_dtstart').val(),
		'dtend': $('#id_dtend').val(),
		'description': $('#id_description').val(),
		'location': $('#id_location').val()
	})
	.done(function() {
		var parts = $('#calendar_select').val().split(':');
		var eventSource = calendar.getEventSourceById(parts[0]);
		eventSource.refetch();
		reset_form();

	});
}

function on_delete() {
	$.post( 'cdav/calendar', {
		'delete': 'delete',
		'target': $('#calendar_select').val(),
		'uri': $('#event_uri').val(),
	})
	.done(function() {
		var parts = $('#calendar_select').val().split(':');
		var eventSource = calendar.getEventSourceById(parts[0]);
		eventSource.refetch();
		reset_form();
	});
}

function reset_form() {
	$('.section-content-tools-wrapper, #event_form_wrapper, #recurrence_warning').hide();

	$('#event_submit').val('');
	$('#calendar_select').val('');
	$('#event_uri').val('');
	$('#id_title').val('');
	$('#id_dtstart').val('');
	$('#id_dtend').val('');

	if(new_event.id) {
		var event_poi = calendar.getEventById(new_event.id);
		event_poi.remove();
		new_event = {};
	}
	
	if($('#more_block').hasClass('open'))
		on_more();
}

function on_more() {
	if($('#more_block').hasClass('open')) {
		$('#event_more').html('<i class="fa fa-caret-down"></i> {{$more}}');
		$('#more_block').removeClass('open').hide();
	}
	else {
		$('#event_more').html('<i class="fa fa-caret-up"></i> {{$less}}');
		$('#more_block').addClass('open').show();
	}
}

</script>

<div class="generic-content-wrapper">
	<div class="section-title-wrapper">
		<div class="float-right">
			<div class="dropdown">
				<button id="view_selector" type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown"></button>
				<div class="dropdown-menu">
					<a class="dropdown-item" href="#" onclick="changeView('changeView', 'dayGridMonth'); return false;">{{$month}}</a></li>
					<a class="dropdown-item" href="#" onclick="changeView('changeView', 'timeGridWeek'); return false;">{{$week}}</a></li>
					<a class="dropdown-item" href="#" onclick="changeView('changeView', 'timeGridDay'); return false;">{{$day}}</a></li>
					<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="#" onclick="changeView('changeView', 'listMonth'); return false;">{{$list_month}}</a></li>
					<a class="dropdown-item" href="#" onclick="changeView('changeView', 'listWeek'); return false;">{{$list_week}}</a></li>
					<a class="dropdown-item" href="#" onclick="changeView('changeView', 'listDay'); return false;">{{$list_day}}</a></li>
				</div>
				<div class="btn-group">
					<button id="prev-btn" class="btn btn-outline-secondary btn-sm" title="{{$prev}}"><i class="fa fa-backward"></i></button>
					<button id="today-btn" class="btn btn-outline-secondary btn-sm" title="{{$today}}"><div id="events-spinner" class="spinner s"></div><i class="fa fa-bullseye" style="display: none; width: 1rem;"></i></button>
					<button id="next-btn" class="btn btn-outline-secondary btn-sm" title="{{$next}}"><i class="fa fa-forward"></i></button>
				</div>
				<button id="fullscreen-btn" type="button" class="btn btn-outline-secondary btn-sm" onclick="makeFullScreen();"><i class="fa fa-expand"></i></button>
				<button id="inline-btn" type="button" class="btn btn-outline-secondary btn-sm" onclick="makeFullScreen(false);"><i class="fa fa-compress"></i></button>
			</div>
		</div>
		<h2 id="title"></h2>
		<div class="clear"></div>
	</div>
	<div class="section-content-tools-wrapper" style="display: none">
		<div id="recurrence_warning" style="display: none">
			<div class="section-content-warning-wrapper">
				{{$recurrence_warning}}
			</div>
			<div>
				<button id="event_delete_recurrent" type="button" class="btn btn-danger btn-sm">{{$delete_all}}</button>
				<button id="event_cancel_recurrent" type="button" class="btn btn-outline-secondary btn-sm">{{$cancel}}</button>
			</div>
		</div>
		<div id="event_form_wrapper" style="display: none">
			<form id="event_form" method="post" action="">
				<input id="event_uri" type="hidden" name="uri" value="">
				{{include file="field_input.tpl" field=$title}}
				<label for="calendar_select">{{$calendar_select_label}}</label>
				<select id="calendar_select" name="target" class="form-control form-group">
					{{foreach $writable_calendars as $writable_calendar}}
					<option value="{{$writable_calendar.id.0}}:{{$writable_calendar.id.1}}">{{$writable_calendar.displayname}}{{if $writable_calendar.sharer}} ({{$writable_calendar.sharer}}){{/if}}</option>
					{{/foreach}}
				</select>
				<div id="more_block" style="display: none;">
					{{include file="field_input.tpl" field=$dtstart}}
					{{include file="field_input.tpl" field=$dtend}}
					{{include file="field_textarea.tpl" field=$description}}
					{{include file="field_textarea.tpl" field=$location}}
				</div>
				<div class="form-group">
					<div class="pull-right">
						<button id="event_more" type="button" class="btn btn-outline-secondary btn-sm"><i class="fa fa-caret-down"></i> {{$more}}</button>
						<button id="event_submit" type="button" value="" class="btn btn-primary btn-sm">{{$update}}</button>

					</div>
					<div>
						<button id="event_delete" type="button" class="btn btn-danger btn-sm">{{$delete}}</button>
						<button id="event_cancel" type="button" class="btn btn-outline-secondary btn-sm">{{$cancel}}</button>
					</div>
					<div class="clear"></div>
				</div>
			</form>
		</div>
	</div>
	<div class="section-content-wrapper-np">
		<div id="calendar"></div>
	</div>
</div>
