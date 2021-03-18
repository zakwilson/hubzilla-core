<div class="generic-content-wrapper">
	<div class="section-title-wrapper">
		<h2>{{$title}}</h2>
	</div>
	<div class="section-content-wrapper">
		<form action="register" method="post" id="register-form">
			<input type='hidden' name='form_security_token' value='{{$form_security_token}}'>
			<div class="section-content-warning-wrapper">
				{{if $reg_is}}
				<div id="register-desc" class="descriptive-paragraph">{{$reg_is}}</div>
				{{/if}}
				<div id="register-sites" class="descriptive-paragraph">{{$other_sites}}</div>
				<h2>{{$now}}</h2>
			</div>

			{{if $registertext}}
			<div class="section-content-info-wrapper">
				<div id="register-text" class="descriptive-paragraph">{{$registertext}}</div>
			</div>
			{{/if}}

			<div>
			{{if $invitations}}
				<a id="zar014" href="javascript:;" style="display: inline-block;">{{$haveivc}}</a>
				<div id="zar015" style="display: none;">
				{{include file="field_input.tpl" field=[$invite_code.0,$invite_code.1,"","",""]}}
				</div>
			{{/if}}

			{{include file="field_input.tpl" field=[$email.0,$email.1,"",$email.3,"",""]}}
			</div>

			{{include file="field_password.tpl" field=[$pass1.0,$pass1.1,"","","",$atform]}}

			{{include file="field_password.tpl" field=[$pass2.0,$pass2.1,"","","",$atform]}}

			{{if $auto_create}}
				{{if $default_role}}
				<input type="hidden" name="permissions_role" value="{{$default_role}}" />
				{{else}}
				<div class="section-content-info-wrapper">
					{{$help_role}}
				</div>
				{{include file="field_select_grouped.tpl" field=$role}}
				{{/if}}

				{{include file="field_input.tpl" field=[$name.0,$name.1,"","","",$atform]}}
				<div id="name-spinner" class="spinner-wrapper"><div class="spinner m"></div></div>

				{{include file="field_input.tpl" field=[$nickname.0,$nickname.1,"","","",$atform]}}
				<div id="nick-spinner" class="spinner-wrapper"><div class="spinner m"></div></div>
			{{/if}}

			{{if $enable_tos}}
			{{include file="field_checkbox.tpl" field=[$tos.0,$tos.1,"","","",$atform]}}
			{{else}}
			<input type="hidden" name="tos" value="1" />
			{{/if}}

			<button class="btn btn-primary" type="submit" name="submit" id="newchannel-submit-button" value="{{$submit}}" {{$atform}}>{{$submit}}</button>
			<div id="register-submit-end" class="register-field-end"></div>
		</form>
		<br />
		<div class="descriptive-text">{{$verify_note}} {{$msg}}</div>
	</div>
</div>
{{* 
	COMMENTS for this template:
	hilmar, 2020.02
*}}
<script>
	$('head').append(
	  '<style> '+
 	 '  .zuiqmid  { font-weight: normal; font-family: monospace; }'+
 	 '  .zuirise  { font-weight: bold; font-size: 100%; color: red; }'+
	  '</style>');

	{{$tao}}

	var week_days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
	$('.register_date').each( function () {
		var date = new Date($(this).data('utc'));
		$(this).html(date.toLocaleString(undefined, {weekday: 'short', hour: 'numeric', minute: 'numeric'}));
	});
</script>
