<div class="generic-content-wrapper">
	<div class="section-title-wrapper">
		<h2>{{$title}}</h2>
		<div class="clear"></div>
	</div>
	<div class="section-content-tools-wrapper">
		<form action="permcats" id="settings-permcats-form" method="post" autocomplete="off" >
			<input type="hidden" name="form_security_token" value="{{$form_security_token}}">
			<input type="hidden" name="return_path" value="{{$return_path}}">

			{{if $is_system_role}}
			<input type="hidden" name="is_system_role" value="1">
			<input type="hidden" name="name" value="{{$is_system_role}}">
			{{/if}}

			{{include file="field_input.tpl" field=$name}}
			{{include file="field_checkbox.tpl" field=$default_role}}

			{{$group_select}}

			<div class="section-subtitle-wrapper" id="perms-tool">
				<h3>
					{{$permlbl}}
				</h3>
			</div>
			<div class="section-content-warning-wrapper">
			{{$permnote}}
			</div>
			<table id="" class="table table-hover">
				{{foreach $perms as $prm}}
				{{include file="field_acheckbox.tpl" field=$prm}}
				{{/foreach}}
			</table>
			<div class="settings-submit-wrapper" >
				<button type="submit" name="submit" class="btn btn-primary">{{$submit}}</button>
			</div>
			{{**if $permcats}}
			<table id="permcat-index">
			{{foreach $permcats as $k => $v}}
			<tr class="permcat-row-{{$k}}">
				<td width="99%"><a href="permcats/{{$k}}">{{$v}}</a></td>
				<td width="1%"><i class="fa fa-trash-o drop-icons" onClick="dropItem('permcats/{{$k}}/drop', '.permcat-row-{{$k}}')"></i></td>
			</tr>
			{{/foreach}}
			</table>
			{{/if**}}

		</form>
	</div>
</div>
