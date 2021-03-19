<?php

namespace Zotlabs\Module;

use Zotlabs\Web\Controller;

require_once('include/security.php');

class Register extends Controller {

	const MYP = 	'ZAR';		// ZAR0x
	const VERSION =	'2.0.0';

	function init() {

		// ZAR0

		$result = null;
		$cmd = ((argc() > 1) ? argv(1) : '');

		// Provide a stored request for somebody desiring a connection
		// when they first need to register someplace. Once they've
		// created a channel, we'll try to revive the connection request
		// and process it.

		if($_REQUEST['connect'])
			$_SESSION['connect'] = $_REQUEST['connect'];

		switch($cmd) {
			case 'invite_check.json':
				$result = check_account_invite($_REQUEST['invite_code']);
				break;
			case 'email_check.json':
				$result = check_account_email($_REQUEST['email']);
				break;
			case 'password_check.json':
				$result = check_account_password($_REQUEST['password1']);
				break;
			default:
				break;
		}
		if($result) {
			json_return_and_die($result);
		}
	}


	function post() {

		check_form_security_token_redirectOnErr('/register', 'register');

		/**
		 * [hilmar:]
		 * It may happen, the posted form arrives in a strange fashion. With the control of the duty hours
		 * for registration, the input form was disabled at html. While receiving posted data, checks are
		 * required if all is on the right road (most posts are not accepted during off duty).
		 *
		 */


        $act        = q("SELECT COUNT(*) AS act FROM account")[0]['act'];
		$duty 		= zar_register_dutystate();
		$is247		= false;
		$ip 		= $_SERVER['REMOTE_ADDR'];
		$sameip  	= intval(get_config('system','register_sameip'));

		$arr 		 = $_POST;
		$invite_code = ( (x($arr,'invite_code'))   ? notags(trim($arr['invite_code']))   : '');
		$email 		 = ( (x($arr,'email'))         ? notags(punify(trim($arr['email']))) : '');
		$password 	 = ( (x($arr,'password'))      ? trim($arr['password'])              : '');
		$reonar		 = array();


		// case when an invited prepares the own account by supply own pw, accept tos, prepage channel (if auto)
		if ($email && $invite_code) {

			if ( preg_match('/^.{2,64}\@[a-z0-9.-]{4,32}\.[a-z]{2,12}$/', $email ) ) {
				if ( preg_match('/^[a-z0-9]{12,12}$/', $invite_code ) ) {
					$is247 = true;
				}
			}

		}
		// assume someone tries to validate (dId2 C/D/E), because only field email entered
		if ( $email && ( ! $invite_code ) && ( ! $password ) && ( ! $_POST['password2'] ) ) {

			// dId2 logic

			if ( preg_match('/^\@{1,1}.{2,64}\@[a-z0-9.-]{4,32}\.[a-z]{2,12}$/', $email ) ) {
				// dId2 C channel - ffu
			}

			if ( preg_match('/^.{2,64}\@[a-z0-9.-]{4,32}\.[a-z]{2,12}$/', $email ) ) {
				// dId2 E email
				goaway(z_root() . '/regate/' . bin2hex($email) . 'e' );
			}

			if ( preg_match('/^d{1,1}[0-9]{5,10}$/', $email ) ) {
				// dId2 A artifical & anonymous
				goaway(z_root() . '/regate/' . bin2hex($email) . 'a' );
			}

		}


		if ($act > 0 && !$is247 && !$duty['isduty']) {
			// normally (except very 1st timr after install), that should never arrive here (ie js hack or sth like)
			// log suitable for f2b also
			$logmsg = 'ZAR0230S Unexpected registration request off duty';
			zar_log($logmsg);
			goaway(z_root() . '/~');
		}

		if ($sameip && !$is247) {
			$f = q("SELECT COUNT(reg_atip) AS atip FROM register WHERE reg_vital = 1 AND reg_atip = '%s' ",
				dbesc($ip)
			);
			if ($f && $f[0]['atip'] > $sameip) {
				$logmsg = 'ZAR0239S Exceeding same ip register request of ' . $sameip;
				zar_log($logmsg);
				goaway(z_root() . '/~');
			}
		}

		// s2 max daily
		// msg?
		if ( !$is247 && self::check_reg_limits()['is'] ) return;

		// accept tos
		if(! x($_POST,'tos')) {
			// msg!
			notice( 'ZAR0230E '
			. t('Please indicate acceptance of the Terms of Service. Registration failed.') . EOL);
			return;
		}

		// pw1 == pw2
		if((! $_POST['password']) || ($_POST['password'] !== $_POST['password2'])) {
			// msg!
			notice( 'ZAR0230E '
			. t('Passwords do not match.') . EOL);
			return;
		}


		$email_verify = intval(get_config('system','verify_email'));

		if ($email) {
			if ( ! preg_match('/^.{2,64}\@[a-z0-9.-]{4,32}\.[a-z]{2,12}$/', $_POST['email'] ) ) {
				// msg!
				notice('ZAR0239E '
				.  t('Email address mistake') . EOL);
				return;
			}
		}

		$policy  = intval(get_config('system','register_policy'));
		$invonly = intval(get_config('system','invitation_only'));
		$invalso = intval(get_config('system','invitation_also'));
		$auto_create  = (get_config('system','auto_channel_create') ? true : false);
		$auto_create = true;


		switch($policy) {

			case REGISTER_OPEN:
				$flags = ACCOUNT_OK;
				break;

			case REGISTER_APPROVE:
				$flags = ACCOUNT_PENDING;
				break;

			default:
			case REGISTER_CLOSED:
				if(! is_site_admin()) {
					notice( t('Permission denied.') . EOL );
					return;
				}
				$flags = ACCOUNT_BLOCKED;
				break;
		}

		if($email_verify && ($policy == REGISTER_OPEN || $policy == REGISTER_APPROVE) )
			$flags = ($flags | ACCOUNT_UNVERIFIED);

		// $arr has $_POST;
		$arr['account_flags'] = $flags;
		$now = datetime_convert();
		$well = false;

		// s3
		if ($invite_code) {

			if ($invonly || $invalso) {

				$reg = q("SELECT * from register WHERE reg_vital = 1 AND reg_didx = 'i' AND reg_hash = '%s'",
					 dbesc($invite_code));

				if ( $reg && count($reg) == 1 ) {
					$reg = $reg[0];
					if ($reg['reg_email'] == ($email)) {

						if ($reg['reg_startup'] <= $now && $reg['reg_expires'] >= $now) {

							// is invitor admin
							$isa = get_account_by_id($reg['reg_uid']);
							$isa = ( $isa && ($isa['account_roles'] && ACCOUNT_ROLE_ADMIN) );

							// approve contra invite by admin
							if ($isa && $policy == REGISTER_APPROVE)
								$flags &= $flags ^ ACCOUNT_PENDING;

							// if $flags == 0  ??

							// transit ?

							// update reg vital 0 off
							$icdone = q("UPDATE register SET reg_vital = 0 WHERE reg_id = %d ",
								intval($reg['reg_id'])
							);

							$msg = 'ZAR0237I ' . t('Invitation code succesfully applied');
							zar_log($msg) . ', ' . $email;
							// msg!
							info($msg . EOL);

							$well = true;


						} else {
							// msg!
							notice('ZAR0236E ' . t('Invitation not in time or too late') . EOL);
							goaway(z_root() . '/~');
						}

					} else {
						// no match email adr
						$msg = 'ZAR0235S ' . t('Invitation email failed');
						zar_log($msg);
						notice($msg . EOL);
						goaway(z_root() . '/~');
					}

				} else {
					// no match invitecode
					$msg = 'ZAR0234S ' . t('Invitation code failed') ;
					zar_log($msg);
					notice( $msg . EOL);
					goaway(z_root() . '/~');
				}

			} else {
				notice('ZAR0232E ' . t('Invitations are not available') . EOL);
				goaway(z_root() . '/~');
			}


		} else {

			$icdone = false;
			// no ivc entered
			if ( ! $invonly) {
				// possibly the email is just in use ?
				$reg = q("SELECT * from register WHERE reg_vital = 1 AND reg_email = '%s'",
					 dbesc('e' . $email));

				if ( ! $reg)
					$act = q("SELECT * from account WHERE account_email = '%s'", dbesc($email));

				// in case an invitation was made but the invitecode was not entered, better ignore.
				// goaway(z_root() . '/regate/' . bin2hex($reg['email']));

				if ( ! $reg && ! $act) {
					// email useable

					$well = true;


				} else {
					$msg = 'ZAR0237E ' . t('Email address already in use') . EOL;
					notice($msg);
					// problem, the msg tells to anonymous about existant email addrs
					// use another msg instead ? TODO ?
					// on the other hand can play the fail2ban game
					zar_log($msg . ' (' . $email . ')');
					goaway(z_root());
				}

			} else {
				$msg = 'ZAR0233E ' . t('Registration on this hub is by invitation only') . EOL;
				notice($msg);
				zar_log($msg);
				goaway(z_root());
			}

		}

		if ($well) {

			if($policy == REGISTER_OPEN || $policy == REGISTER_APPROVE ) {

				$cfgdelay = get_config( 'system', 'register_delay' );
				$reg_delayed = calculate_adue( $cfgdelay );
				$regdelay = (($reg_delayed) ? datetime_convert(date_default_timezone_get(), 'UTC', $reg_delayed['due']) : $now);

				$cfgexpire = get_config('system','register_expire' );
				$reg_expires = calculate_adue( $cfgexpire );
				$regexpire = (($reg_expires) ? datetime_convert(date_default_timezone_get(), 'UTC', $reg_expires['due']) : datetime_convert('UTC', 'UTC', 'now + 99 years'));

				// handle an email request that will be verified or an ivitation associated with an email address
				if ( $email > '' && ($email_verify || $icdone) ) {
					// enforce in case of icdone
					$flags |= ACCOUNT_UNVERIFIED;
					$empin = $pass2 = random_string(24);
					$did2  = $email;
					$didx  = 'e';

					push_lang(($reg['lang']) ? $reg['lang'] : 'en');
					$reonar['from'] = get_config('system', 'from_email');
					$reonar['to'] = $email;
					$reonar['subject'] = sprintf( t('Registration confirmation for %s'), get_config('system','sitename'));
					$reonar['txtpersonal']= t('Valid from') . ' ' . $regdelay . ' UTC' . t('and expire') . ' ' . $regexpire . ' UTC';
					$reonar['txttemplate']= replace_macros(get_intltext_template('register_verify_member.tpl'),
						[
						'$sitename' => get_config('system','sitename'),
						'$siteurl'  => z_root(),
						'$email'    => $email,
						'$due'		=> $reonar['txtpersonal'],
						'$mail'		=> bin2hex($email) . 'e',
						'$ko'		=> bin2hex(substr($empin,0,4)),
						'$hash'     => $empin
				 		]
					);
					pop_lang();
					zar_reg_mail($reonar);

				} else {
					// that is an anonymous request without email or with email not to verify
					$acpin = $pass2 = rand(100000,999999);
					$did2 = rand(10,99);
					$didx = 'a';
					// enforce delayed verify
					$flags = ($flags | ACCOUNT_UNVERIFIED);
					if ($email) {
						$reonar['email.untrust'] = $email;
						$reonar['email.comment'] = 'received, but no need for';
					}
				}

				if ( $auto_create ) {
					$reonar['chan.name'] = notags(trim($arr['name']));
					$reonar['chan.did1'] = notags(trim($arr['nickname']));
				}

				$reg = q("INSERT INTO register ("
				. "reg_flags,reg_didx,reg_did2,reg_hash,reg_created,reg_startup,reg_expires,"
				. "reg_email,reg_pass,reg_lang,reg_atip,reg_stuff)"
				. " VALUES (%d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') ",
						intval($flags),
						dbesc($didx),
						dbesc($did2),
						dbesc($pass2),
						dbesc($now),
						dbesc($regdelay),
						dbesc($regexpire),
						dbesc($email),
						dbesc(bin2hex($password)),
						dbesc(substr(get_best_language(),0,2)),
						dbesc($ip),
						dbesc(json_encode( $reonar ))
					);

				if ($didx == 'a') {

					$lid = q("SELECT reg_id FROM register WHERE reg_vital = 1 AND reg_did2 = '%s' AND reg_pass = '%s' ",
						 	dbesc($did2), dbesc(bin2hex($password)) );

					if ($lid && count($lid) == 1 ) {

						$didnew = ( $lid[0]['reg_id'] . $did2 )
						 		. ( substr( base_convert( md5( $lid[0]['reg_id'] . $did2 ), 16, 10 ),-2 ) );

						$reg = q("UPDATE register SET reg_did2 = CONCAT('d','%s') WHERE reg_id = %d ",
							dbesc($didnew), intval($lid[0]['reg_id'])
						);

						zar_log( 'ZAR0239A ' . t('New register request') . ' d' . $didnew . ', '
							.  $regdelay . ' - ' . $regexpire);

						if($reg_delayed) {
							// notice( 'ZAR0239I,' . t( 'Your digital id is' ) . EOL . 'd' . $didnew . EOL
							$_SESSION['zar']['msg'] = ( t('Your validation token is') . ' ' . $pass2 . EOL
							. t('Please remember your token and reload this page between') . EOL
							. '<code class="inline-code"><span data-utc="' . datetime_convert('UTC', 'UTC', $regdelay, 'c') . '" class="register_date">' . datetime_convert('UTC', 'UTC', $regdelay, 'c') . '</span></code> ' . t('and') . ' <code class="inline-code"><span data-utc="' . datetime_convert('UTC', 'UTC', $regexpire, 'c') . '" class="register_date">' . datetime_convert('UTC', 'UTC', $regexpire, 'c') . '</span></code>' . EOL
							. t('to complete registration.')
							);
						}
						else {
							$_SESSION['zar']['pin'] = $pass2;
						}
						goaway(z_root() . '/regate/' . bin2hex('d' . $didnew) . 'a' );
					}
					else {
						$msg = 'ZAR0239D,' . t('Error creating dId A');
						notice( $msg );
						zar_log( $msg . ' ' . $did2);
					}
				}
			}
		}
	}



	function get() {

		$registration_is = '';
		$other_sites = '';

		if(intval(get_config('system','register_policy')) === REGISTER_CLOSED) {
			if(intval(get_config('system','directory_mode')) === DIRECTORY_MODE_STANDALONE) {
				notice( 'ZAR0130E ' . t('Registration on this hub is disabled.')  . EOL);
				return;
			}

			$mod = new Pubsites();
			return $mod->get();
		}

		if(intval(get_config('system','register_policy')) == REGISTER_APPROVE) {
			$registration_is = t('Registration on this hub is by approval only.') . '<sup>ZAR0131I</sup>';
			$other_sites = '<a href="pubsites">' . t('Register at another affiliated hub in case when prefered') . '</a>';
		}

		if ( !get_config('system', 'register_duty_jso') ) {
			// duty yet not configured
			$duty = array( 'isduty' => false, 'atfrm' => '', 'nowfmt' => '');
		} else {
			$duty = zar_register_dutystate();
		}

		$invitations = false;
		if(intval(get_config('system','invitation_only'))) {
			$invitations = true;
			$registration_is = t('Registration on this hub is by invitation only.') . '<sup>ZAR0132I</sup>';
			$other_sites = '<a href="pubsites">' . t('Register at another affiliated hub') . '</a>';
		} elseif (intval(get_config('system','invitation_also'))) {
			$invitations = true;
		}

		$opal = self::check_reg_limits();
		if ( $opal['is'])
			 $duty['atform'] = 'disabled';

		$privacy_role = ((x($_REQUEST,'permissions_role')) ? $_REQUEST['permissions_role'] : "");

		$perm_roles = \Zotlabs\Access\PermissionRoles::roles();

		// Configurable terms of service link
		$tosurl = get_config('system','tos_url');
		if(! $tosurl)
			$tosurl = z_root() . '/help/TermsOfService';

		$toslink = '<a href="' . $tosurl . '" target="_blank">' . t('Terms of Service') . '</a>';

		// Configurable whether to restrict age or not - default is based on international legal requirements
		// This can be relaxed if you are on a restricted server that does not share with public servers

		if(get_config('system','no_age_restriction')) {
			$label_tos = sprintf( t('I accept the %s for this website'), $toslink);
		}
		else {
			$age = get_config('system','minimum_age');
			if(!$age) {
				$age = 13;
			}
			$label_tos = sprintf( t('I am over %s years of age and accept the %s for this website'), $age, $toslink);
		}

		$enable_tos = 1 - intval(get_config('system','no_termsofservice'));

		$emailval = ((x($_REQUEST,'email')) ? strip_tags(trim($_REQUEST['email'])) : "");
		$email = array('email',
				 	t('Your email address (or leave blank to register without email)') . ' <sup>ZAR0136I</sup>',
				 	$emailval,
				 	t('If the registation was already submitted with your data once ago, enter your identity (like email) here and submit') . '<sup>ZAR0133I</sup>'
					);

		$password     = array('password', t('Choose a password'), '');
		$password2    = array('password2', t('Please re-enter your password'), '');

		$invite_code  = array('invite_code', t('Please enter your invitation code'), ((x($_REQUEST,'invite_code')) ? strip_tags(trim($_REQUEST['invite_code'])) : ""));

		//
		$name = array('name', t('Your Name'),
			((x($_REQUEST,'name')) ? $_REQUEST['name'] : ''), t('Real names are preferred.'));
		$nickhub = '@' . str_replace(array('http://','https://','/'), '', get_config('system','baseurl'));
		$nickname = array('nickname', t('Choose a short nickname'),
			((x($_REQUEST,'nickname')) ? $_REQUEST['nickname'] : ''),
			sprintf( t('Your nickname will be used to create an easy to remember channel address e.g. nickname%s'),
			$nickhub));
		$role = array('permissions_role' , t('Channel role and privacy'),
			($privacy_role) ? $privacy_role : 'social',
			t('Select a channel permission role for your usage needs and privacy requirements.')
			. ' <a href="help/member/member_guide#Channel_Permission_Roles" target="_blank">'
			. t('Read more about channel permission roles')
			. '</a>',$perm_roles);
		//

		$tos = array('tos', $label_tos, '', '', array(t('no'),t('yes')));

		$auto_create  = (get_config('system','auto_channel_create') ? true : false);
		$default_role = get_config('system','default_permissions_role');
		$email_verify = get_config('system','verify_email');

		require_once('include/bbcode.php');

		$o = replace_macros(get_markup_template('register.tpl'), array(

			'$tao'			=> 	"typeof(window.tao) == 'undefined' ? window.tao = {} : '';\n"
							.	"tao.zar = { vsn: '2.0.0', form: {}, msg: {} };\n"
							.	"tao.zar.patano = /^d[0-9]{5,10}$/;\n"
							.	"tao.zar.patema = /^[a-z0-9.-]{2,64}@[a-z0-9.-]{4,32}\.[a-z]{2,12}$/;\n"
							.	"tao.zar.msg.ZAR0239E = '" . t('email mistake') . "';\n",

			'$form_security_token' => get_form_security_token("register"),
			'$title'        => t('Registration'),
			'$reg_is'       => $registration_is,
			'$registertext' => bbcode(get_config('system','register_text')),
			'$other_sites'  => $other_sites,
			'$msg'			=> $opal['rn'] . ',' . $opal['an'],
			'$invitations'  => $invitations,
			'$invite_code'  => $invite_code,
			'$haveivc'		=> t('I have an invite code') . '.<sup>ZAR0134I</sup>',
			'$now'			=> $duty['nowfmt'],
			'$atform'		=> $duty['atform'],
			'$auto_create'  => $auto_create,
			'$name'         => $name,
			'$role'         => $role,
			'$default_role' => $default_role,
			'$nickname'     => $nickname,
			'$enable_tos'	=> $enable_tos,
			'$tos'          => $tos,
			'$email'        => $email,
			'$validate'		=> $validate,
			'$validate_link'=> $validate_link,
			'$validate_here'=> $validate_here,
			'$pass1'        => $password,
			'$pass2'        => $password2,
			'$submit'       => t('Register'),
			'$verify_note'  => (($email_verify) ? t('This site requires verification. After completing this form, please check the notice or your email for further instructions.') . '<sup>ZAR0135I</sup>' : '')
		));

		return $o;
	}

	function check_reg_limits() {
		// check against register, account
		$rear = array( 'is' => false, 'rn' => 0, 'an' => 0, 'msg' => '' );

		$max_dailies = intval(get_config('system','max_daily_registrations'));

		if ( $max_dailies ) {

			$r = q("SELECT COUNT(reg_id) AS nr FROM register WHERE reg_vital = 1 AND reg_created > %s - INTERVAL %s",
				db_utcnow(), db_quoteinterval('1 day')
			);

			$rear['is'] = ( $r && $r[0]['nr'] >= $max_dailies ) ? true : false;
			$rear['rn'] = $r[0]['nr'];

			if ( !$rear['is']) {
				$r = q("SELECT COUNT(account_id) AS nr FROM account WHERE account_created > %s - INTERVAL %s",
					db_utcnow(), db_quoteinterval('1 day')
				);

				$rear['is'] = ( $r && ($r[0]['nr'] + $rear['rn']) >= $max_dailies ) ? true : false;
				$rear['ra'] = $r[0]['nr'];
			}

			if ( $rear['is']) {
				$rear['msg'] = 'ZAR0333W ' . t('This site has exceeded the number of allowed daily account registrations');
				zar_log($msg);
				$rear['is'] = true;
			}
		}
		return $rear;
	}
}
