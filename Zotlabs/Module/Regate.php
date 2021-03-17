<?php

namespace Zotlabs\Module;

require_once('include/security.php');

/**
 *
 * @version 2.0.0
 * @author  hilmar runge
 * @since 	2020-03-03
 * Check verification pin
 * input field email address
 * input field pin (told during register)
 * check duty
 * check startup and expire
 * compare email address
 * check pin
 * limited tries to enter the correct pin/pass 2 handle via f2b
 * on success create account and update register
 *
 */

	define ( 'REGISTER_AGREED', 0x0020 );
	define ( 'REGISTER_DENIED', 0x0040 );

class Regate extends \Zotlabs\Web\Controller {

	const MYP = 	'ZAR';		//ZAR1x
	const VERSION =	'2.0.0';


	function post() {

		check_form_security_token_redirectOnErr('/', 'regate');

		if ( argc() > 1 ) {
			$did2 = hex2bin( substr( argv(1), 0, -1) );
			$didx = substr( argv(1), -1 );
		}

		$msg = '';
		$nextpage = '';

		if ($did2) {

			$nowhhmm 	= date('Hi');
			$day 	= date('N');
			$now 	= date('Y-m-d H:i:s');
			$ip 	= $_SERVER['REMOTE_ADDR'];

			$isduty = zar_register_dutystate();
			if ($isduty['isduty'] !== false && $isduty['isduty'] != 1) {
					// normally, that should never happen here
					// log suitable for fail2ban also
					$logmsg = 'ZAR1230S Unexpected registration verification request for '
							. get_config('system','sitename') . ' arrived from § ' . $ip . ' §';
					zar_log($logmsg);
					goaway(z_root() . '/');
			}

			// do we have a valid dId2 ?
			if ( ($didx == 'a' && substr( $did2 , -2) == substr( base_convert( md5( substr( $did2, 1, -2) ),16 ,10), -2))
			||   ($didx == 'e') ) {
				// check startup and expiration via [=[register
				$r = q("SELECT * FROM register WHERE reg_vital = 1 AND reg_did2 = '%s' ", dbesc($did2) );
				if ( $r && count($r) == 1 ) {
					$r = $r[0];
					// check timeframe
					if ( $r['reg_startup'] <= $now && $r['reg_expires'] >= $now ) {

						if ( isset($_POST['resend']) && $didx == 'e' ) {
							$re = q("SELECT * FROM register WHERE reg_vital = 1 AND reg_didx = 'e' AND reg_did2 = '%s' ", dbesc($r['reg_did2']) );
							if ( $re && count($re) == 1 ) {
								$re = $re[0];
								$reonar = json_decode($re['reg_stuff'],true);
								$reonar['subject'] = 'Re,Fwd,' . $reonar['subject'];
								if ($reonar) {
									$zm = zar_reg_mail($reonar);
									$msg = ($zm) ?	'ZAR1238I ' . t('Email resent')
											  	 :	'ZAR1238E ' . t('Resent failed');
									zar_log($msg . ' ' . $r['reg_did2']);
									info($msg);
									goaway(z_root() . '/' . $nextpage);
								}
							}
						}

						// check hash
						if ( $didx == 'a' )
							$acpin = (preg_match('/^[0-9]{6,6}$/', $_POST['acpin']) ? $_POST['acpin'] : false);
						elseif ( $didx == 'e' )
							$acpin = (preg_match('/^[0-9a-f]{24,24}$/', $_POST['acpin']) ? $_POST['acpin'] : false);
						else $acpin = false;

						if ( $acpin && ($r['reg_hash'] == $acpin )) {

							$flags = $r['reg_flags'];
							if ( ($flags & ACCOUNT_UNVERIFIED ) == ACCOUNT_UNVERIFIED) {

								// verification success
								$msg 	= 'ZAR1237I' . ' ' . t('Verify successfull');
								$reonar = json_decode( $r['reg_stuff'], true);
								$reonar['valid'] = $now . ',' . $ip . ' ' . $did2 . ' ' . $msg;
								// clear flag
								$flags &= $flags ^ ACCOUNT_UNVERIFIED;
								// sth todo?
								$vital = $flags == 0 ? 0 : 1;
								// set flag
								$flags |= REGISTER_AGREED;
								zar_log($msg . ' ' . $did2 . ':flags' . $flags . ',rid' . $r['reg_id']);

								q("START TRANSACTION");

								$qu = q("UPDATE register SET reg_stuff = '%s', reg_vital = %d, reg_flags = %d "
										." WHERE reg_id = %d ",
									dbesc(json_encode($reonar)),
									intval($vital),
									intval($flags),
									intval($r['reg_id'])
								);

								if ( ($flags & ACCOUNT_PENDING ) == ACCOUNT_PENDING ) {
									$msg .= "\n".t('Last step will be by an instance admin to agree your account request');
									q("COMMIT");
								}
								elseif ( ($flags ^ REGISTER_AGREED) == 0) {

									$cra = create_account_from_register([ 'reg_id' => $r['reg_id'] ]);

									if ($cra['success']) {

										q("COMMIT");
										$msg = 'ZAR1238I ' . t('Account successfull created');
										// zar_log($msg . ':' . print_r($cra, true));
										zar_log($msg . ' ' . $cra['account']['account_email']
													 . ' ' . $cra['account']['account_language']);
										$nextpage = 'new_channel';

										$auto_create  = (get_config('system','auto_channel_create') ? true : false);

										if($auto_create) {
											// prepare channel creation
											if($reonar['chan.name'])
												set_aconfig($cra['account']['account_id'],
													'register','channel_name',$reonar['chan.name']);
											if($reonar['chan.did1'])
												set_aconfig($cra['account']['account_id'],
													'register','channel_address',$reonar['chan.did1']);
										}

										authenticate_success($cra['account'],null,true,false,true);

										if($auto_create) {
											// create channel
											$new_channel = auto_channel_create($cra['account']['account_id']);

											if($new_channel['success']) {
												$channel_id = $new_channel['channel']['channel_id'];
												change_channel($channel_id);
												$nextpage = 'profiles/' . $channel_id;
												$msg = 'ZAR1239I ' . t('Channel successfull created') . ' ' . $did2;
											}
											else {
												$msg = 'ZAR1239E ' . t('Channel still not created') . ' ' . $did2;
											}
											zar_log($msg . ' ' . $reonar['chan.did1'] . ' (' . $reonar['chan.name'] . ')');
										}
										unset($_SESSION['login_return_url']);
									}
									else {
										q("ROLLBACK");
										$msg = 'ZAR1238E ' . t('Account creation error');
										zar_log($msg . ':' . print_r($cra, true));
									}
								}
								else {
									// new flags implemented and not recognized or sth like
									zar_log('ZAR1237D unexpected,' . $flags);
								}
							}
							else {
								// nothing to confirm
								$msg = 'ZAR1236E' . ' ' . t('Verify failed');
							}
						}
						else {
							$msg = 'ZAR1235E' . ' ' . t('Token verification failed');
						}
					}
					else {
						$msg = 'ZAR1234W' . ' ' . t('Request not inside time frame');
						//info($r[0]['reg_startup'] . EOL . $r[0]['reg_expire'] );
					}
				}
				else {
					$msg = 'ZAR1232E' . ' ' . t('Identity unknown');
					zar_log($msg . ':' . $did2 . $didx);
				}
			}
			else {
				$msg = 'ZAR1231E' . t('dId2 mistaken');
			}

		}

		if ($msg > '') info($msg);
		goaway( z_root() . '/' . $nextpage );
	}


	function get() {

		if ( argc() > 1 ) {
			$did2 = hex2bin( substr( argv(1), 0, -1) );
			$didx = substr( argv(1), -1 );
			$deny = argc() > 2 ? argv(2) : '';
			$deny = preg_match('/^[0-9a-f]{8,8}$/', $deny) ? hex2bin($deny) : false;
		}

		if ($_SESSION['zar']['msg']) {
			$o = replace_macros(get_markup_template('plain.tpl'), [
				'$title'	=> t('Your Registration'),
				'$now'		=> '',
				'$infos'	=> $_SESSION['zar']['msg'] . EOL,
			]);
			unset($_SESSION['zar']['msg']);
			return $o;
		}

		$now 	= date('Y-m-d H:i:s');
		$ip 	= $_SERVER['REMOTE_ADDR'];

		$isduty = zar_register_dutystate();
			$nowfmt = $isduty['nowfmt'];
			$atform = $isduty['atform'];

		$title = t('Register Verification');

		// do we have a valid dId2 ?
		if ( ($didx == 'a' && substr( $did2 , -2) == substr( base_convert( md5( substr( $did2, 1, -2) ),16 ,10), -2))
		||   ($didx == 'e') ) {

			$r = q("SELECT * FROM register WHERE reg_vital = 1 AND reg_didx = '%s' AND reg_did2 = '%s'",
					dbesc($didx),
					dbesc($did2)
			);

			if ( $r && count($r) == 1 && $r[0]['reg_flags'] &= (ACCOUNT_UNVERIFIED | ACCOUNT_PENDING)) {
				$r = $r[0];

				// provide a button in case
				$resend = ($r['reg_didx'] == 'e') ? t('Resend') : false;

				// is still only instance admins intervention required?
				if ( $r['reg_flags'] == ACCOUNT_PENDING ) {
					$o = replace_macros(get_markup_template('plain.tpl'), [
						'$title'	=> t('Register Verification Status'),
						'$now'		=> $nowfmt,
						'$infos'	=> t('Soon all is well.') . EOL
									.  t('Only one instance admin has still to agree your account request.') . EOL
									.  t('Please be patient') . EOL . EOL . 'ZAR1138I',
					]);
				}
				else {

					if ($deny) {

						if (substr($r['reg_hash'],0,4) == $deny) {

							zar_log('ZAR1134S email verfication denied ' . $did2);

							$msg = 'ZAR1133A' . ' ' . t('Sorry for any inconvience. Thank you for your response.');
							$o = replace_macros(get_markup_template('plain.tpl'), [
								'$title'	=> t('Registration request denied'),
								'$now'		=> $nowf,
								'$infos'	=> $msg . EOL,
							]);

							$reonar = json_decode( $r['reg_stuff'], true);
							$reonar['deny'] = $now . ',' . $ip . ' ' . $did2 . ' ' . $msg;
							$flags  = ( $r['reg_flags'] &= ( $r['reg_flags'] ^ ACCOUNT_UNVERIFIED) )
									| ( $r['reg_flags'] |= REGISTER_DENIED);
							$rd = q("UPDATE register SET reg_stuff='%s', reg_vital=0, reg_flags=%d  WHERE reg_id = %d ",
								dbesc(json_encode($reonar)),
								intval($flags),
								intval($r['reg_id'])
							);
						}
						else {
							zar_log('ZAR1135E not awaited url parameter received');
							goaway(z_root);
						}
					}
					else {

						if ( $r['reg_startup'] <= $now && $r['reg_expires'] >= $now) {
							$o = replace_macros(get_markup_template('regate.tpl'), [
							'$form_security_token' => get_form_security_token("regate"),
							'$title' 	=> $title,
							'$desc' 	=> t('You were given a validation token. Please enter that token here to continue the register verification step and allow some delay for proccessing.'),
							'$did2' 	=> bin2hex($did2) . $didx,
							'$now'		=> $nowfmt,
							'$atform'	=> $atform,
							'$resend' 	=> $resend,
							'$submit' 	=> t('Submit'),
							'$acpin' 	=> [ 'acpin', t('Validation token'),'','' ],
							]);
						}
						else {
							//  expired ?
							if ( $now > $r['reg_expires'] ) {
								$rd = q("UPDATE register SET reg_vital = 0 WHERE reg_id = %d ",
									intval($r['reg_id'])
								);
							}

							$o = replace_macros(get_markup_template('plain.tpl'), [
								'$title'	=> $title,
								'$now'		=> $nowf,
								'$infos'	=> 'ZAR1132W' . ' ' . t('Request not inside time frame') . EOL,
							]);
						}
					}
				}
			}
			else {
				$msg = 'ZAR1132E' . ' ' . t('Identity unknown');
				zar_log($msg . ':' . $did2 . ',' . $didx);
				$o = replace_macros(get_markup_template('plain.tpl'), [
					'$title'	=> $title,
					'$now'		=> $nowf,
					'$infos'	=> $msg . EOL,
				]);
			}

		}
		else {
			$msg = 'ZAR1131E ' . t('dId2 mistaken');
			// $log = ' from § ' . $ip . ' §' . ' (' . dbesc($did2) . ')';
			zar_log($msg);
			$o = replace_macros(get_markup_template('plain.tpl'), [
				'$title'	=> $title,
				'$now'		=> $nowf,
				'$infos'	=> ($msg) . EOL,
				]);
		}

		return $o;
	}
}

