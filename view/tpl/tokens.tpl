<div class="generic-content-wrapper">
	<div class="section-title-wrapper">
		<h2>{{$title}}</h2>
		<div class="clear"></div>
	</div>
	<div class="section-content-tools-wrapper">
		<div class="section-content-info-wrapper">
			{{$desc}}
		</div>

		<form action="tokens" id="settings-account-form" method="post" autocomplete="off" >
			<input type='hidden' name='form_security_token' value='{{$form_security_token}}'>
			{{if $atoken}}<input type="hidden" name="atoken_id" value="{{$atoken.atoken_id}}" />{{/if}}
			{{include file="field_input.tpl" field=$name}}
			{{include file="field_input.tpl" field=$token}}
			{{include file="field_input.tpl" field=$expires}}
			{{include file="field_select.tpl" field=$permcat}}

			<div class="settings-submit-wrapper mb-3">
				<button type="submit" name="submit" class="btn btn-primary">{{$submit}}</button>
			</div>
	</div>

	{{if $tokens}}
	<div class="section-content-wrapper-np">
		<table id="atoken-index">
			{{foreach $tokens as $t}}
			<tr id="atoken-index-{{$t.atoken_id}}" class="atoken-index-row">
				<td width="99%"><a href="tokens/{{$t.atoken_id}}">{{$t.atoken_name}}</a></td>
				<td width="1%" class="atoken-index-tool"><i class="fa fa-trash-o drop-icons" onClick="dropItem('tokens/{{$t.atoken_id}}/drop', '#atoken-index-{{$t.atoken_id}}')"></i></td>
			</tr>
			{{/foreach}}
		</table>

	</div>
	{{/if}}
</div>
