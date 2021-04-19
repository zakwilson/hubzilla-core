<?php

namespace Zotlabs\Module\Admin;



class Accounts {

	/**
	 * @brief Handle POST actions on accounts admin page.
	 *
	 * This function is called when on the admin user/account page the form was
	 * submitted to handle multiple operations at once. If one of the icons next
	 * to an entry are pressed the function admin_page_accounts() will handle this.
	 *
	 */

	const MYP = 	'ZAR';			// ZAR2x
	const VERSION =	'2.0.0';

	function post() {

		$pending = ( x($_POST, 'pending') ? $_POST['pending'] : array() );
		$users   = ( x($_POST, 'user')    ? $_POST['user']    : array() );
		$blocked = ( x($_POST, 'blocked') ? $_POST['blocked'] : array() );

		check_form_security_token_redirectOnErr('/admin/accounts', 'admin_accounts');

		$isajax = is_ajax();
		$rc = 0;

		If (!is_site_admin()) {
			if ($isajax) {
				killme();
				exit;
			}
			goaway(z_root() . '/');
		}

		if ($isajax) {
			//$debug = print_r($_SESSION[self::MYP],true);
			$zarop = (x($_POST['zardo']) && preg_match('/^[ad]{1,1}$/', $_POST['zardo']) )
					 ? $_POST['zardo'] : '';
			// zarat arrives with leading underscore _n
			$zarat = (x($_POST['zarat']) && preg_match('/^_{1,1}[0-9]{1,6}$/', $_POST['zarat']) )
					 ? substr($_POST['zarat'],1) : '';
			$zarse = (x($_POST['zarse']) && preg_match('/^[0-9a-f]{8,8}$/', $_POST['zarse']) )
					 ? hex2bin($_POST['zarse']) : '';

			if ($zarop && $zarat >= 0 && $zarse && $zarse == $_SESSION[self::MYP]['h'][$zarat]) {

				//
				if ($zarop == 'd') {
					$rd = q("UPDATE register SET reg_vital = 0 WHERE reg_id = %d AND SUBSTR(reg_hash,1,4) = '%s' ",
						intval($_SESSION[self::MYP]['i'][$zarat]),
						dbesc($_SESSION[self::MYP]['h'][$zarat])
					);
					$rc = '×';
				}
				elseif ($zarop == 'a') {
					// approval, REGISTER_DENIED by user 0x0040, REGISTER_AGREED by user 0x0020 @Regate
					$rd = q("UPDATE register SET reg_flags = (reg_flags & ~ 16), "
						.	" reg_vital = (CASE (reg_flags & ~ 48) WHEN 0 THEN 0 ELSE 1 END) "
						.	" WHERE reg_vital = 1 AND reg_id = %d AND SUBSTR(reg_hash,1,4) = '%s' ",
						intval($_SESSION[self::MYP]['i'][$zarat]),
						dbesc($_SESSION[self::MYP]['h'][$zarat])
					);
					$rc = 0;
					$rs = q("SELECT * from register WHERE reg_id = %d ",
							intval($_SESSION[self::MYP]['i'][$zarat])
					);
					if ($rs && ($rs[0]['reg_flags'] & ~ 48) == 0) {
						// create account
						$rc = 'ok'.$rs[0]['reg_id'];
						$ac = create_account_from_register($rs[0]);
						if ( $ac['success'] ) {
							$rc .= '✔';

							$auto_create  = get_config('system','auto_channel_create',1);

							if($auto_create) {
								$reonar = json_decode($rs[0]['reg_stuff'], true);
								// prepare channel creation
								if($reonar['chan.name'])
									set_aconfig($ac['account']['account_id'], 'register', 'channel_name', $reonar['chan.name']);

								if($reonar['chan.did1'])
									set_aconfig($ac['account']['account_id'], 'register', 'channel_address', $reonar['chan.did1']);

								$permissions_role  = get_config('system','default_permissions_role');
								if($permissions_role)
									set_aconfig($ac['account']['account_id'], 'register', 'permissions_role', $permissions_role);

								// create channel
								$new_channel = auto_channel_create($ac['account']['account_id']);

								if($new_channel['success']) {
									$rc .= ' c,ok' . $new_channel['channel']['channel_id'] . '✔';
								}
								else {
									$rc .= ' c ×';
								}
							}


						}
					} else {
						$rc='oh ×';
					}
				}
				echo json_encode(array('re' => $zarop, 'at' => '_' . $zarat, 'rc' => $rc));
			}
			killme();
			exit;
		}

		// change to switch structure?
		// account block/unblock button was submitted
		if (x($_POST, 'page_accounts_block')) {
			for ($i = 0; $i < count($users); $i++) {
				// if account is blocked remove blocked bit-flag, otherwise add blocked bit-flag
				$op = ($blocked[$i]) ? '& ~' : '| ';
				q("UPDATE account SET account_flags = (account_flags $op%d) WHERE account_id = %d",
					intval(ACCOUNT_BLOCKED),
					intval($users[$i])
				);
			}
			notice( sprintf( tt("%s account blocked/unblocked", "%s account blocked/unblocked", count($users)), count($users)) );
		}
		// account delete button was submitted
		if (x($_POST, 'page_accounts_delete')) {
			foreach ($users as $uid){
				account_remove($uid, true, false);
			}
			notice( sprintf( tt("%s account deleted", "%s accounts deleted", count($users)), count($users)) );
		}
		// registration approved button was submitted
		if (x($_POST, 'page_accounts_approve')) {
			foreach ($pending as $hash) {
				account_allow($hash);
			}
		}
		// registration deny button was submitted
		if (x($_POST, 'page_accounts_deny')) {
			foreach ($pending as $hash) {
				account_deny($hash);
			}
		}

		goaway(z_root() . '/admin/accounts' );
	}

	/**
	 * @brief Generate accounts admin page and handle single item operations.
	 *
	 * This function generates the accounts/account admin page and handles the actions
	 * if an icon next to an entry was clicked. If several items were selected and
	 * the form was submitted it is handled by the function admin_page_accounts_post().
	 *
	 * @return string
	 */

	function get(){
		if (argc() > 2) {
			$uid = argv(3);
			$account = q("SELECT * FROM account WHERE account_id = %d",
				intval($uid)
			);

			if (! $account) {
				notice( t('Account not found') . EOL);
				goaway(z_root() . '/admin/accounts' );
			}

			check_form_security_token_redirectOnErr('/admin/accounts', 'admin_accounts', 't');

			$debug = '';

			switch (argv(2)){
				case 'delete':
					// delete user
					account_remove($uid,true,false);

					notice( sprintf(t("Account '%s' deleted"), $account[0]['account_email']) . EOL);
					break;
				case 'block':
					q("UPDATE account SET account_flags = ( account_flags | %d ) WHERE account_id = %d",
						intval(ACCOUNT_BLOCKED),
						intval($uid)
					);

					notice( sprintf( t("Account '%s' blocked") , $account[0]['account_email']) . EOL);
					break;
				case 'unblock':
					q("UPDATE account SET account_flags = ( account_flags & ~%d ) WHERE account_id = %d",
							intval(ACCOUNT_BLOCKED),
							intval($uid)
					);

					notice( sprintf( t("Account '%s' unblocked"), $account[0]['account_email']) . EOL);
					break;
			}

			goaway(z_root() . '/admin/accounts' );
		}

		$tao = 'tao.zar.zarax = ' . "'" . '<img src="' . z_root() . '/images/zapax16.gif">' . "';\n";


		// by default we will only return verified results. if reg_all is set we will return everything''
		$get_all = isset($_REQUEST['get_all']);
		$pending = get_pending_accounts($get_all);

		unset($_SESSION[self::MYP]);

		if ($pending) {
			// collect and group all ip
			$atips = dbq("SELECT reg_atip AS atip, COUNT(reg_atip) AS atips FROM register
				WHERE reg_vital = 1 GROUP BY reg_atip"
			);

			(($atips) ? $atipn = array_column($atips, 'atips', 'atip') : $atipn = ['' => 0]);

			$tao .= 'tao.zar.zarar = {';
			foreach ($pending as $n => $v) {

				$stuff = json_decode($v['reg_stuff'], true);

				if(isset($stuff['msg'])) {
					$pending[$n]['msg'] = $stuff['msg'];
				}

				if (array_key_exists($v['reg_atip'], $atipn)) {
					$pending[$n]['reg_atip'] = $v['reg_atip'];
					$pending[$n]['reg_atip_n'] = $atipn[$v['reg_atip']];
				}

				$pending[$n]['status'] = '';
				if($pending[$n]['reg_flags'] & ACCOUNT_UNVERIFIED > 0)
					$pending[$n]['status'] = [t('Unverified'), 'bg-warning'];

				if($pending[$n]['status'] && $pending[$n]['reg_expires'] < datetime_convert())
					$pending[$n]['status'] = [t('Expired'), 'bg-danger text-white'];

				// timezone adjust date_time for display
				$pending[$n]['reg_created'] = datetime_convert('UTC', date_default_timezone_get(), $pending[$n]['reg_created']);
				$pending[$n]['reg_startup'] = datetime_convert('UTC', date_default_timezone_get(), $pending[$n]['reg_startup']);
				$pending[$n]['reg_expires'] = datetime_convert('UTC', date_default_timezone_get(), $pending[$n]['reg_expires']);

				// better secure
				$tao .= $n . ": '" . substr(bin2hex($v['reg_hash']),0,8) . "',";
				$_SESSION[self::MYP]['h'][] = substr($v['reg_hash'],0,4);
				$_SESSION[self::MYP]['i'][] = $v['reg_id'];
			}
			$tao = rtrim($tao,',') . '};' . "\n";
		}
		// <- hilmar]

		/* get accounts */

		$total = q("SELECT count(*) as total FROM account");
		if (count($total)) {
			\App::set_pager_total($total[0]['total']);
			\App::set_pager_itemspage(100);
		}

		$serviceclass = (($_REQUEST['class']) ? " and account_service_class = '" . dbesc($_REQUEST['class']) . "' " : '');

		$key = (($_REQUEST['key']) ? dbesc($_REQUEST['key']) : 'account_id');
		$dir = 'asc';
		if(array_key_exists('dir',$_REQUEST))
			$dir = ((intval($_REQUEST['dir'])) ? 'asc' : 'desc');

		$base = z_root() . '/admin/accounts?f=';
		$odir = (($dir === 'asc') ? '0' : '1');

		$users = q("SELECT account_id , account_email, account_lastlog, account_created, account_expires, account_service_class, ( account_flags & %d ) > 0 as blocked,
			(SELECT %s FROM channel as ch WHERE ch.channel_account_id = ac.account_id and ch.channel_removed = 0 ) as channels FROM account as ac
			where true $serviceclass and account_flags != %d order by $key $dir limit %d offset %d ",
			intval(ACCOUNT_BLOCKED),
			db_concat('ch.channel_address', ' '),
			intval(ACCOUNT_BLOCKED | ACCOUNT_PENDING),
			intval(\App::$pager['itemspage']),
			intval(\App::$pager['start'])
		);

	//	function _setup_users($e){
	//		$accounts = Array(
	//			t('Normal Account'),
	//			t('Soapbox Account'),
	//			t('Community/Celebrity Account'),
	//			t('Automatic Friend Account')
	//		);

	//		$e['page_flags'] = $accounts[$e['page-flags']];
	//		$e['register_date'] = relative_date($e['register_date']);
	//		$e['login_date'] = relative_date($e['login_date']);
	//		$e['lastitem_date'] = relative_date($e['lastitem_date']);
	//		return $e;
	//	}
	//	$users = array_map("_setup_users", $users);

		$t = get_markup_template('admin_accounts.tpl');
		$o = replace_macros($t, array(
			// strings //
			'$debug' => $debug,
			'$title' => t('Administration'),
			'$page' => t('Accounts'),
			'$submit' => t('Submit'),
			'$get_all' => (($get_all) ? t('Show verified registrations') : t('Show all registrations')),
			'$get_all_link' => (($get_all) ? z_root() .'/admin/accounts' : z_root() .'/admin/accounts?get_all'),
			'$sel_tall' => t('Select toggle'),
			'$sel_deny' => t('Deny selected'),
			'$sel_aprv' => t('Approve selected'),
			'$h_pending' => (($get_all) ? t('All registrations') : t('Verified registrations waiting for approval')),
			'$th_pending' => array(t('Request date'), 'dId2', t('Email'), 'IP', t('Requests')),
			'$no_pending' => (($get_all) ? t('No registrations available') : t('No verified registrations available')),
			'$approve' => t('Approve'),
			'$deny' => t('Deny'),
			'$delete' => t('Delete'),
			'$block' => t('Block'),
			'$unblock' => t('Unblock'),
			'$verified' => t('Verified'),
			'$not_verified' => t('Not yet verified'),
			'$odir' => $odir,
			'$base' => $base,
			'$h_users' => t('Accounts'),
			'$th_users' => array(
				[ t('ID'), 'account_id' ],
				[ t('Email'), 'account_email' ],
				[ t('All channels'), 'channels' ],
				[ t('Register date'), 'account_created' ],
				[ t('Last login'), 'account_lastlog' ],
				[ t('Expires'), 'account_expires' ],
				[ t('Service class'), 'account_service_class'] ),

			'$confirm_delete_multi' => p2j(t('Selected accounts will be deleted!\n\nEverything these accounts had posted on this site will be permanently deleted!\n\nAre you sure?')),
			'$confirm_delete' => p2j(t('The account {0} will be deleted!\n\nEverything this account has posted on this site will be permanently deleted!\n\nAre you sure?')),

			'$form_security_token' => get_form_security_token("admin_accounts"),

			// values //
			'$baseurl' 	=> z_root(),
			'$tao'		=> $tao,
			'$pending' 	=> $pending,
			'$users' 	=> $users,
			'$msg'      => t('Message')
		));
		$o .= paginate($a);

		return $o;
	}

}

