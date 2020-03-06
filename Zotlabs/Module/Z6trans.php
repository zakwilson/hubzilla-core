<?php
/**
 * @file Zotlabs/Module/Z6trans.php
 *
 * @brief replace all occurances of an zot xchan with the zot6 xchan in DB.
 *
 */

namespace Zotlabs\Module;

use Zotlabs\Web\Controller;

class Z6trans extends Controller {

	function post() {
		if(!is_site_admin())
			return;

		$zot_xchan = trim($_POST['zot_xchan']);

		$r = q("SELECT xchan_guid FROM xchan WHERE xchan_hash = '%s' AND xchan_network = 'zot'",
			dbesc($zot_xchan)
		);

		if(!$r) {
			notice(t('Zot xchan not found. Aborting.') . EOL);
			return;
		}

		$guid = $r[0]['xchan_guid'];
		$r = q("SELECT xchan_hash, xchan_guid_sig FROM xchan WHERE xchan_guid = '%s' AND xchan_network = 'zot6'",
			dbesc($guid)
		);

		if(!$r) {
			notice(t('No zot6 xchan found. Aborting.') . EOL);
			return;
		}

		$zot6_xchan = $r[0]['xchan_hash'];
		$zot6_xchan_guid_sig = $r[0]['xchan_guid_sig'];


		$r = q("SELECT * FROM channel WHERE channel_hash = '%s'",
			dbesc($zot_xchan)
		);

		// We got everything we need - start transforming.

		if($r) {
			logger("Transforming channel $zot_xchan");
			q("UPDATE channel SET channel_hash = '%s', channel_portable_id = '%s', channel_guid_sig = '%s' WHERE channel_hash = '%s'",
				dbesc($zot6_xchan),
				dbesc($zot_xchan),
				dbesc($zot6_xchan_guid_sig),
				dbesc($zot_xchan)
			);
		}

		$core = self::get_core_cols();

		foreach($core as $table => $cols) {
			foreach($cols as $col) {
				logger("Transforming $table - $col");
				q("UPDATE %s SET %s = replace(%s, '%s', '%s')",
					dbesc($table),
					dbesc($col),
					dbesc($col),
					dbesc($zot_xchan),
					dbesc($zot6_xchan)
				);
			}
			logger("$table done.");
		}
		logger("Transformation completed.");



	}

	function get() {
		if(!is_site_admin())
			return 'Not Allowed';

		$path = 'store/z6upgrade.sql';

		$r = q("SELECT channel.channel_name, channel.channel_hash, xchan.xchan_network FROM channel LEFT JOIN xchan ON channel_hash = xchan_hash WHERE xchan.xchan_network = 'zot' AND channel.channel_removed = 0");

		foreach($r as $rr) {

			$zot_xchan = $rr['channel_hash'];

			$r = q("SELECT xchan_guid FROM xchan WHERE xchan_hash = '%s' AND xchan_network = 'zot'",
				dbesc($zot_xchan)
			);

			if(!$r) {
				notice(t('Zot xchan not found. Aborting.') . EOL);
				return;
			}

			$guid = $r[0]['xchan_guid'];
			$r = q("SELECT xchan_hash, xchan_guid_sig FROM xchan WHERE xchan_guid = '%s' AND xchan_network = 'zot6'",
				dbesc($guid)
			);

			if(!$r) {
				notice(t('No zot6 xchan found. Aborting.') . EOL);
				return;
			}

			$zot6_xchan = $r[0]['xchan_hash'];
			$zot6_xchan_guid_sig = $r[0]['xchan_guid_sig'];

			//this should probably happen in a db_update during upgrading
			$q .= sprintf("UPDATE channel SET channel_hash = '%s', channel_portable_id = '%s', channel_guid_sig = '%s' WHERE channel_hash = '%s';\r\n",
				dbesc($zot6_xchan),
				dbesc($zot_xchan),
				dbesc($zot6_xchan_guid_sig),
				dbesc($zot_xchan)
			);

			$core = self::get_core_cols();

			foreach($core as $table => $cols) {

				foreach($cols as $col) {

					$q .= sprintf("UPDATE %s SET %s = replace(%s, '%s', '%s');\r\n",
						dbesc($table),
						dbesc($col),
						dbesc($col),
						dbesc($zot_xchan),
						dbesc($zot6_xchan)
					);

				}

			}

			file_put_contents($path, $q);

		}


		$o = '<h3>' . t('Transform a single channel') . '</h3>';
		$o .= '<form action="z6trans" method="post">';
		$o .= t('Enter zot xchan to transform: ') . '<br>';
		$o .= '<input type="text" style="width:100%;" name="zot_xchan" value=""><br>';
		$o .= '<input type="submit" name="submit" value="' . t('Submit') .'"></form>';
		$o .= '<br><br><br>';


		$o .= '<h3>' . t('To transform all channels') . '</h3>';
		if(ACTIVE_DBTYPE == DBTYPE_MYSQL)
			$o .= 'Run <code>source ' . $_SERVER["DOCUMENT_ROOT"] . '/' . $path . '</code> from the mysql console to complete the upgrade.';
		else
			$o .= 'Run <code>\i ' . $_SERVER["DOCUMENT_ROOT"] . '/' . $path . '</code> from the postgresql console to complete the upgrade.';

		return $o;

	}

	function get_core_cols() {

		$core = [
			'abconfig' => ['xchan'],
			'abook' => ['abook_xchan'],
			'app' => ['app_author'],
			'attach' => ['creator', 'allow_cid', 'deny_cid'],
			'channel' => ['channel_allow_cid', 'channel_deny_cid'],
			'chat' => ['chat_xchan'],
			'chatpresence' => ['cp_xchan'],
			'chatroom' => ['allow_cid', 'deny_cid'],
			'config' => ['v'],
			'dreport' => ['dreport_recip', 'dreport_xchan'],
			'event' => ['event_xchan', 'allow_cid', 'deny_cid'],
			'iconfig' => ['v'],
			'item' => ['owner_xchan', 'author_xchan', 'source_xchan', 'route', 'allow_cid', 'deny_cid'],
			'mail' => ['from_xchan', 'to_xchan'],
			'menu_item' => ['allow_cid', 'deny_cid'],
			'obj' => ['allow_cid', 'deny_cid'],
			'pconfig' => ['v'],
			'pgrp_member' => ['xchan'],
			'photo' => ['xchan', 'allow_cid', 'deny_cid'],
			'source' => ['src_channel_xchan', 'src_xchan'],
			'updates' => ['ud_hash'],
			'xchat' => ['xchat_xchan'],
			'xconfig' => ['xchan', 'v'],
			'xign' => ['xchan'],
			'xlink' => ['xlink_xchan', 'xlink_link'],
			'xprof' => ['xprof_hash'],
			'xtag' => ['xtag_hash'],
		];

		return $core;

	}

}
