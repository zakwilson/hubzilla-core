
Thank you for registering at {{$sitename}}.

Your login details are as follows:

Site Location:	{{$siteurl}}
Login Name:	{{$email}}

Login with the password you chose at registration.

We need to verify your email address in order to give you full access.

Your verification token is

{{$hash}}

{{if $timeframe}}
This token is valid from {{$timeframe.0}} UTC until {{$timeframe.1}} UTC


{{/if}}
If you registered this account, please enter the validation code when requested or visit the following link:

{{$siteurl}}/regate/{{$mail}}


To deny the request and remove the account, please visit:

{{$siteurl}}/regate/{{$mail}}{{if $ko}}/{{$ko}}{{/if}}


Thank you!


--
Terms Of Service:
{{$siteurl}}/help/TermsOfService

