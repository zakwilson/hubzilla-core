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

	function get() {
		if(!is_site_admin())
			return '<h1>Not Allowed</h1>';

		$o = '<h2>' . t('Update to Hubzilla 5.0 step 2') . '</h2><br>';

		$o .= '<h3>' . t('To complete the update please run') . '</h3>';

		$o .= '<code>' . t('php util/z6convert.php') . '</code>';

		$o .= '<h3>' . t('from the terminal.') . '</h3>';

		return $o;

/* this code is outdated use util/z6convert.php instead

		$path = 'store/z6trans.sql';

		$r = q("SELECT channel.channel_name, channel.channel_portable_id, xchan.xchan_network FROM channel 
			LEFT JOIN xchan ON channel_portable_id = xchan_hash 
			WHERE xchan.xchan_network = 'zot' 
			AND channel.channel_removed = 0"
		);

		$q = '';

		foreach($r as $rr) {

			$zot_xchan = $rr['channel_portable_id'];

			$r = q("SELECT xchan_guid FROM xchan WHERE xchan_hash = '%s' AND xchan_network = 'zot'",
				dbesc($zot_xchan)
			);

			if(!$r) {
				$q .= '-- ' . $zot_xchan . 'failed: zot xchan not found' . "\r\n";
				continue;
			}

			$guid = $r[0]['xchan_guid'];

			$r = q("SELECT xchan_hash, xchan_guid_sig FROM xchan WHERE xchan_guid = '%s' AND xchan_network = 'zot6'",
				dbesc($guid)
			);

			if(!$r) {
				$q .= '-- ' . $zot_xchan . 'failed: zot6 xchan not found' . "\r\n";
				continue;
			}

			$zot6_xchan = $r[0]['xchan_hash'];

			$core = self::get_core_cols();

			$q .= '-- Transforming ' . $rr['channel_name'] . "\r\n";

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
			
			$zot = dbesc($zot_xchan);
			$zot6 = dbesc($zot6_xchan);

			// Item table
			foreach(['owner_xchan', 'author_xchan'] as $x) {
				$q .= sprintf("UPDATE item SET $x = '%s' WHERE $x = '%s';\r\n",
					$zot6,
					$zot
				);
			}
			$q .= "UPDATE item SET source_xchan = replace(source_xchan, '$zot', '$zot6'), route = replace(route, '$zot', '$zot6'), allow_cid = replace(allow_cid, '$zot', '$zot6'), deny_cid = replace(deny_cid, '$zot', '$zot6');\r\n";

			// photo table
			$q .= "UPDATE photo SET xchan = replace(xchan, '$zot', '$zot6'), allow_cid = replace(allow_cid, '$zot', '$zot6'), deny_cid = replace(deny_cid, '$zot', '$zot6');\r\n";

			// dreport table
			$q .= "UPDATE dreport SET dreport_recip = '$zot6' WHERE dreport_recip = '$zot';\r\n";
			$q .= "UPDATE dreport SET dreport_xchan = '$zot6' WHERE dreport_xchan = '$zot';\r\n";
		}

		if($q)
			file_put_contents($path, $q);

		$o = '<h2>' . t('Update to Hubzilla 5.0 step 2') . '</h2><br>';

		$o .= '<h3>' . t('To complete the update please run') . '</h3>';
		if(ACTIVE_DBTYPE == DBTYPE_MYSQL)
			$o .= '<code>source ' . $_SERVER["DOCUMENT_ROOT"] . '/' . $path . '</code><h3>from the mysql console.</h3>';
		else
			$o .= '<code>\i ' . $_SERVER["DOCUMENT_ROOT"] . '/' . $path . '</code><h3>from the postgresql console.</h3>';

		$o .= '<br><h3>' . t('INFO: this command can take a very long time depending on your DB size.') . '</h3>';

		return $o;
*/

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
//			'dreport' => ['dreport_recip', 'dreport_xchan'],
			'event' => ['event_xchan', 'allow_cid', 'deny_cid'],
			'iconfig' => ['v'],
//			'item' => ['owner_xchan', 'author_xchan', 'source_xchan', 'route', 'allow_cid', 'deny_cid'],
			'mail' => ['from_xchan', 'to_xchan'],
			'menu_item' => ['allow_cid', 'deny_cid'],
			'obj' => ['allow_cid', 'deny_cid'],
			'pconfig' => ['v'],
			'pgrp_member' => ['xchan'],
//			'photo' => ['xchan', 'allow_cid', 'deny_cid'],
			'source' => ['src_channel_xchan', 'src_xchan'],
			'updates' => ['ud_hash'],
			'xchat' => ['xchat_xchan'],
			'xconfig' => ['xchan', 'v'],
			'xign' => ['xchan'],
			'xlink' => ['xlink_xchan', 'xlink_link'],
//			'xprof' => ['xprof_hash'],
			'xtag' => ['xtag_hash'],
		];

		return $core;

	}

}
