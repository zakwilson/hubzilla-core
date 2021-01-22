~~~
L	regate	ZAR1131E	dId2 mistaken
L			ZAR1132E	Identity unknown
			ZAR1133A	Sorry for any inconvience. Thank you for your response.
L			ZAR1134S	email verfication denied {did2}
L			ZAR1135E 	not awaited url parameter received

L	regate	ZAR1230S 	Unexpected registration verification request for
L			ZAR1231E	dId2 mistaken
L			ZAR1232E	Identity unknown
L			ZAR1234W	Request not inside time frame
L			ZAR1235E	Token verification failed
			ZAR1236I	Verify successfull
L			ZAR1236E	Verify failed
L			ZAR1237D	unexpected
						(reason may be caused by new account flags implemented still not known)
			ZAR1238I	Email resent
			ZAR1238E	Resent failed
L			ZAR1239I	Account successfull created
L			ZAR1239E	Account creation error

	register	ZAR0130E	Registration on this hub is disabled.
				ZAR0131I	Registration on this hub is by approval only.
							Register at another affiliated hub in case when prefered
				ZAR0132I	Registration on this hub is by invitation only.
							Register at another affiliated hub
				ZAR0133I	If the registation was already submitted with your data once ago,
							enter your identity (like email) here and submit
				ZAR0134I	I have an invite code
				ZAR0135I	This site requires verification. After completing this form,
							please check the notice or your email for further instructions.
				ZAR0136I	Your email address (or leave blank to register without email)

L 	register	ZAR0230S	Unexpected registration request
				ZAR0231E	Email address mistake
				ZAR0231E	Passwords do not match.
				ZAR0231E	Please indicate acceptance of the Terms of Service. Registration failed.
				ZAR0232E	Invitations are not available
L				ZAR0233E	Registration on this hub is by invitation only
L				ZAR0234S	Invitation code failed
L				ZAR0235S	Invitation email failed
				ZAR0236E	Invitation not in time or too late
				ZAR0237I	Invitation code succesfully applied
L				ZAR0238E	Email address already in use
L				ZAR0239D	Error creating dId A
				ZAR0239I	Your didital id is {did2} and your pin for is {pin}
							Keep these infos and your entered password safe
							Valid from ... nd expire ...
L 				ZAR0239S 	Exceeding same ip register request of

	ui:admin:site
			ZAR0810C	Register text
						Will be displayed prominently on the registration page.
			ZAR0820C	register_policy
						Does this site allow new member registration?
			ZAR0830C	Registration office on duty
						The weekdays and hours the register office is open for registrations
			ZAR0831I	Testmode duties
						(interactive)
			ZAR0840C	Account registrations max per day
						How many registration requests the site accepts during one day. Unlimited if zero or no value.
			ZAR0850C	Account registrations from same ip
						How many pending registration requests the site accepts from a same ip address.
			ZAR0860C	Account registration delay
						How long a registration request has to wait before validation can perform
			ZAR0862C	Account registration expiration
						How long a registration to confirm remains valid. Not expire if zero or no value
			ZAR0870C	Auto channel create
						Auto create a channel when register a new account. When On, the register form will show
						 additional fields for the channel-name and the nickname.
			ZAR0880C	Invitation only
						Only allow new member registrations with an invitation code.
						 Above register policy must be set to Yes.
			ZAR0881C	Invitation also
						Also allow new member registrations with an invitation code.
						 Above register policy must be set to Yes.
			ZAR0890C	Verify Email Addresses
						Check to verify email addresses used in account registration (recommended).

	invite	ZAI0100E	All users invitation limit exceeded
			ZAI0101E	Permission denied.
			ZAI0102E	Invite App (Not Installed)
			ZAI0103E	Invites not proposed by configuration. Contact the site admin
			ZAI0104E	Invites by users not enabled
			ZAI0105W	You have no more invitations available
			ZAI0106I	Invitations I am using
			ZAI0107I	Invitations we are using
			ZAI0109E	Not on xchan
			ZAI0110I	§ Note, the email(s) sent will be recorded in the system logs
						(see ZAI0208I @ L)
			ZAI0111I	Enter email addresses, one per line
			ZAI0112I	Your message
						Here you may enter personal notes to the recipient(s)
			ZAI0113I	Invite template
			ZAI0114I	Note, the invitation code is valid up to ...

	invite	ZAI0201E	Permission denied.
			ZAI0202E	Invite App (Not Installed)
			ZAI0203E	Not a valid email address
			ZAI0204E	Not a real email address
			ZAI0205E	Not allowed email address
			ZAI0206E	mail address already in use
			ZAI0207I	Note, the invitation code is valid up to
			ZAI0208E	Message delivery failed.
			ZAI0208I	Message delivery success.
L 			ZAI0208I	to {email} Message delivery success. ({account#}, {channel#}, from:{email}})
			ZAI0209I	Accepted email address
			ZAI0210E	Too many recipients for one invitation (max n)
			ZAI0211E	No recipients for this invitation
			ZAI0212I	n mail(s) sent, n mail error(s)
			ZAI0213E	Register is closed
~~~
