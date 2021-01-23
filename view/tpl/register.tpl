<div class="generic-content-wrapper">
	<div class="section-title-wrapper">
		<h2>{{$title}}</h2>
	</div>
	<div class="section-content-wrapper">
		<form action="register" method="post" id="register-form">
			<input type='hidden' name='form_security_token' value='{{$form_security_token}}'>
			{{if $reg_is}}
			<div class="section-content-warning-wrapper">
				<div id="register-desc" class="descriptive-paragraph">{{$reg_is}}</div>
				<div id="register-sites" class="descriptive-paragraph">{{$other_sites}}</div>
				<h2>{{$now}}</h2>
			</div>
			{{/if}}

			{{if $registertext}}
			<div class="section-content-info-wrapper">
				<div id="register-text" class="descriptive-paragraph">{{$registertext}}</div>
			</div>
			{{/if}}

			{{if $invitations}}
			<div style="text-align: center;">
				<a id="zar014" href="javascript:;" style="display: inline-block;">{{$haveivc}}</a>
			</div>
			<div id="zar015" style="display: none;">
			{{include file="field_input.tpl" field=[$invite_code.0,$invite_code.1,"","","",$atform]}}
			</div>
			{{/if}}

			{{include file="field_input.tpl" field=[$email.0,$email.1,"",$email.3,"",""]}}

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

				{{include file="field_input.tpl" field=$name}}
				<div id="name-spinner" class="spinner-wrapper"><div class="spinner m"></div></div>

				{{include file="field_input.tpl" field=$nickname}}
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
			<div class="descriptive-text">{{$verify_note}}</div>

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
	  '</style>');
	// does not work $('#id_email').off('blur');
	$('#id_email').change( function() {
		if ($('#id_email').val().length > 0) {
			$('#newchannel-submit-button').removeAttr('disabled');
		}
	});
	$('#zar014').click( function () { $('#zar015').toggle(); });
</script>
