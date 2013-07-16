<h1>{{$title}}</h1>


<div id="settings-remove-account-link">
<a href="removeme" title="{{$permanent}}" >{{$removeme}}</a>
</div>


<form action="settings/account" id="settings-account-form" method="post" autocomplete="off" >
<input type='hidden' name='form_security_token' value='{{$form_security_token}}'>

{{include file="field_input.tpl" field=$email}}


<h3 class="settings-heading">{{$h_pass}}</h3>

{{include file="field_password.tpl" field=$password1}}
{{include file="field_password.tpl" field=$password2}}


<div class="settings-submit-wrapper" >
<input type="submit" name="submit" class="settings-submit" value="{{$submit}}" />
</div>

{{$account_settings}}



