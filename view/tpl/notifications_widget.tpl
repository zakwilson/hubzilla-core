{{if $notifications}}
<script>
	var notifications_parent;

	$(document).ready(function() {
		notifications_parent = $('#notifications_wrapper')[0].parentElement.id;
		$('.notifications-btn').click(function() {
			if($('#notifications_wrapper').hasClass('fs')) {
				$('#notifications_wrapper').prependTo('#' + notifications_parent);
				//undo scrollbar remove
				$('section').css('height', '');
			}
			else {
				$('#notifications_wrapper').prependTo('section');
				//remove superfluous scrollbar
				//setting overflow to hidden here has issues with some browsers
				$('section').css('height', '100vh');
			}

			$('#notifications_wrapper').toggleClass('fs');
			if($('#navbar-collapse-2').hasClass('show')){
				$('#navbar-collapse-2').removeClass('show');
			}
		});

		window.onpopstate = function(e) {
			if(e.state !== null && e.state.b64mid !== bParam_mid)
				getData(e.state.b64mid, '');
		};
	});

	{{if $module == 'display' || $module == 'hq' || $startpage == 'hq'}}
	$(document).on('click', '.notification', function(e) {
		var b64mid = $(this).data('b64mid');
		var notify_id = $(this).data('notify_id');
		var path = $(this)[0].pathname.substr(1,7);
		var stateObj = { b64mid: b64mid };

		if(! b64mid && ! notify_id)
			return;

		{{if $module != 'hq' && $startpage == 'hq'}}
			e.preventDefault();
			if(notify_id != null) {
				$.post(
					"hq",
					{
						"notify_id" : notify_id
					}
				);
			}
			window.location.href = 'hq/' + b64mid;
			return;
		{{else}}
			{{if $module == 'display'}}
			history.pushState(stateObj, '', 'display/' + b64mid);
			{{/if}}

			{{if $module == 'hq'}}
			history.pushState(stateObj, '', 'hq/' + b64mid);
			{{/if}}

			{{if $module == 'hq'}}
			if(b64mid) {
			{{else}}
			if(path === 'display' && b64mid) {
			{{/if}}
				e.preventDefault();

				if(! page_load) {
					getData(b64mid, notify_id);
				}

				if($('#notifications_wrapper').hasClass('fs'))
					$('#notifications_wrapper').prependTo('#' + notifications_parent).removeClass('fs');
			}
		{{/if}}
	});
	{{/if}}

	{{foreach $notifications as $notification}}
	{{if $notification.filter}}
	$(document).on('click', '#tt-{{$notification.type}}-only', function(e) {
		if($(this).hasClass('active sticky-top')) {
			$('#nav-{{$notification.type}}-menu .notification[data-thread_top=false]').removeClass('tt-filter-active');
			$(this).removeClass('active sticky-top');
		}
		else {
			$('#nav-{{$notification.type}}-menu .notification[data-thread_top=false]').addClass('tt-filter-active');
			$(this).addClass('active sticky-top');
		}

	});
	$(document).on('click', '#cn-{{$notification.type}}-input-clear', function(e) {
		$('#cn-{{$notification.type}}-input').val('');
		$('#cn-{{$notification.type}}-only').removeClass('active sticky-top');
		$("#nav-{{$notification.type}}-menu .notification").removeClass('cn-filter-active');
		$('#cn-{{$notification.type}}-input-clear').addClass('d-none');
	});
	$(document).on('input', '#cn-{{$notification.type}}-input', function(e) {
		var val = $('#cn-{{$notification.type}}-input').val().toString().toLowerCase();
		if(val) {
			val = val.indexOf('%') == 0 ? val.substring(1) : val;
			$('#cn-{{$notification.type}}-only').addClass('active sticky-top');
			$('#cn-{{$notification.type}}-input-clear').removeClass('d-none');
		}
		else {
			$('#cn-{{$notification.type}}-only').removeClass('active sticky-top');
			$('#cn-{{$notification.type}}-input-clear').addClass('d-none');
		}

		$("#nav-{{$notification.type}}-menu .notification").each(function(i, el){
			var cn = $(el).data('contact_name').toString().toLowerCase();
			var ca = $(el).data('contact_addr').toString().toLowerCase();

			if(cn.indexOf(val) === -1 && ca.indexOf(val) === -1)
				$(this).addClass('cn-filter-active');
			else
				$(this).removeClass('cn-filter-active');
		});
	});
	{{/if}}
	{{/foreach}}

	function getData(b64mid, notify_id) {
		$(document).scrollTop(0);
		$('.thread-wrapper').remove();
		bParam_mid = b64mid;
		mode = 'replace';
		page_load = true;
		{{if $module == 'hq'}}
		liveUpdate(notify_id);
		{{/if}}
		{{if $module == 'display'}}
		liveUpdate();
		{{/if}}
	}
</script>

<div id="notifications_wrapper">
	<div id="no_notifications" class="d-xl-none">
		{{$no_notifications}}<span class="jumping-dots"><span class="dot-1">.</span><span class="dot-2">.</span><span class="dot-3">.</span></span>
	</div>
	<div id="nav-notifications-template" rel="template">
		<a class="list-group-item text-decoration-none text-darkclearfix notification {6}" href="{0}" title="{13}" data-b64mid="{7}" data-notify_id="{8}" data-thread_top="{9}" data-contact_name="{2}" data-contact_addr="{3}" data-when="{5}">
			<img class="menu-img-3" data-src="{1}">
			<div class="contactname"><span class="text-dark font-weight-bold">{2}</span> <span class="text-muted">{3}</span></div>
			<span class="text-muted">{4}</span><br>
			<span class="text-muted notifications-autotime" title="{5}">{5}</span>
		</a>
	</div>
	<div id="nav-notifications-forums-template" rel="template">
		<a class="list-group-item text-decoration-none clearfix notification notification-forum" href="{0}" title="{4} - {3}" data-b64mid="{7}" data-notify_id="{8}" data-thread_top="{9}" data-contact_name="{2}" data-contact_addr="{3}" data-b64mids='{12}'>
			<span class="float-right badge badge-secondary">{10}</span>
			<img class="menu-img-1" data-src="{1}">
			<span class="">{2}</span>
			<i class="fa fa-{11} text-muted"></i> 
		</a>
	</div>
	<div id="notifications" class="border border-bottom-0 rounded navbar-nav collapse">
		{{foreach $notifications as $notification}}
		<div class="rounded list-group list-group-flush collapse {{$notification.type}}-button">
			<a id="notification-link-{{$notification.type}}" class="collapsed list-group-item text-decoration-none notification-link" href="#" title="{{$notification.title}}" data-target="#nav-{{$notification.type}}-sub" data-toggle="collapse" data-sse_type="{{$notification.type}}">
				<i class="fa fa-fw fa-{{$notification.icon}}"></i> {{$notification.label}}
				<span class="float-right badge badge-{{$notification.severity}} {{$notification.type}}-update"></span>
			</a>
			<div id="nav-{{$notification.type}}-sub" class="list-group list-group-flush border border-left-0 border-top-0 border-right-0 collapse notification-content" data-parent="#notifications" data-sse_type="{{$notification.type}}">
				{{if $notification.viewall}}
				<a class="list-group-item text-decoration-none text-dark" id="nav-{{$notification.type}}-see-all" href="{{$notification.viewall.url}}">
					<i class="fa fa-fw fa-external-link"></i> {{$notification.viewall.label}}
				</a>
				{{/if}}
				{{if $notification.markall}}
				<div class="list-group-item cursor-pointer" id="nav-{{$notification.type}}-mark-all" onclick="markRead('{{$notification.type}}'); return false;">
					<i class="fa fa-fw fa-check"></i> {{$notification.markall.label}}
				</div>
				{{/if}}
				{{if $notification.filter}}
				{{if $notification.filter.posts_label}}
				<div class="list-group-item cursor-pointer" id="tt-{{$notification.type}}-only">
					<i class="fa fa-fw fa-filter"></i> {{$notification.filter.posts_label}}
				</div>
				{{/if}}
				{{if $notification.filter.name_label}}
				<div class="list-group-item clearfix notifications-textinput" id="cn-{{$notification.type}}-only">
					<div class="text-muted notifications-textinput-filter"><i class="fa fa-fw fa-filter"></i></div>
					<input id="cn-{{$notification.type}}-input" type="text" class="notification-filter form-control form-control-sm" placeholder="{{$notification.filter.name_label}}">
					<div id="cn-{{$notification.type}}-input-clear" class="text-muted notifications-textinput-clear d-none"><i class="fa fa-times"></i></div>
				</div>
				{{/if}}
				{{/if}}
				<div id="nav-{{$notification.type}}-menu" class="list-group list-group-flush"></div>
				<div id="nav-{{$notification.type}}-loading" class="list-group-item" style="display: none;">
					{{$loading}}<span class="jumping-dots"><span class="dot-1">.</span><span class="dot-2">.</span><span class="dot-3">.</span></span>
				</div>

			</div>
		</div>
		{{/foreach}}
	</div>
</div>
{{/if}}
