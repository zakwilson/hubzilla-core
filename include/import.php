<?php

use Zotlabs\Lib\Apps;
use Zotlabs\Lib\IConfig;
use Zotlabs\Lib\Libzot;

use Zotlabs\Web\HTTPSig;

require_once('include/menu.php');
require_once('include/perm_upgrade.php');


/**
 * @brief Import a channel.
 *
 * @param array $channel
 * @param int $account_id
 * @param int $seize
 * @param string $newname (optional)
 * @return boolean|array
 */
function import_channel($channel, $account_id, $seize, $newname = '') {

	if(! array_key_exists('channel_system',$channel)) {
		$channel['channel_system']  = (($channel['channel_pageflags'] & 0x1000) ? 1 : 0);
		$channel['channel_removed'] = (($channel['channel_pageflags'] & 0x8000) ? 1 : 0);
	}

	if(intval($channel['channel_removed'])) {
		notice( t('Unable to import a removed channel.') . EOL);
		return false;
	}

	// Ignore the hash provided and re-calculate

	$channel['channel_hash'] = Libzot::make_xchan_hash($channel['channel_guid'],$channel['channel_pubkey']);

	if($newname) {
		$channel['channel_address'] = $newname;
	}


	// Check for duplicate channels

	$r = q("select * from channel where (channel_guid = '%s' or channel_hash = '%s' or channel_address = '%s' ) limit 1",
		dbesc($channel['channel_guid']),
		dbesc($channel['channel_hash']),
		dbesc($channel['channel_address'])
	);
	if($r && $r[0]['channel_guid'] == $channel['channel_guid'] && $r[0]['channel_pubkey'] === $channel['channel_pubkey'] && $r[0]['channel_hash'] === $channel['channel_hash'])
		return $r[0];

	if(($r) || (check_webbie(array($channel['channel_address'])) !== $channel['channel_address'])) {
		if($r[0]['channel_guid'] === $channel['channel_guid'] || $r[0]['channel_hash'] === $channel['channel_hash']) {
			logger('mod_import: duplicate channel. ', print_r($channel,true));
			notice( t('Cannot create a duplicate channel identifier on this system. Import failed.') . EOL);
			return false;
		}
		else {
			// try at most ten times to generate a unique address.
			$x = 0;
			$found_unique = false;
			do {
				$tmp = $channel['channel_address'] . mt_rand(1000,9999);
				$r = q("select * from channel where channel_address = '%s' limit 1",
					dbesc($tmp)
				);
				if(! $r) {
					$channel['channel_address'] = $tmp;
					$found_unique = true;
					break;
				}
				$x ++;
			} while ($x < 10);
			if(! $found_unique) {
				logger('mod_import: duplicate channel. randomisation failed.', print_r($channel,true));
				notice( t('Unable to create a unique channel address. Import failed.') . EOL);
				return false;
			}
		}
	}

	unset($channel['channel_id']);
	$channel['channel_account_id'] = $account_id;
	$channel['channel_primary'] = (($seize) ? 1 : 0);

	if($channel['channel_pageflags'] & PAGE_ALLOWCODE) {
		if(! is_site_admin())
			$channel['channel_pageflags'] = $channel['channel_pageflags'] ^ PAGE_ALLOWCODE;
	}

	// remove all the permissions related settings, we will import/upgrade them after the channel
	// is created.

	$disallowed = [
		'channel_id',         'channel_r_stream',    'channel_r_profile', 'channel_r_abook',
		'channel_r_storage',  'channel_r_pages',     'channel_w_stream',  'channel_w_wall',
		'channel_w_comment',  'channel_w_mail',      'channel_w_like',    'channel_w_tagwall',
		'channel_w_chat',     'channel_w_storage',   'channel_w_pages',   'channel_a_republish',
		'channel_a_delegate', 'perm_limits',         'channel_password',  'channel_salt',
		'channel_moved',      'channel_removed',     'channel_deleted',   'channel_system',
		'channel_r_photos',   'channel_w_photos'
	];

	$clean = array();
	foreach($channel as $k => $v) {
		if(in_array($k,$disallowed))
			continue;
		$clean[$k] = $v;
	}

	if($clean) {
		channel_store_lowlevel($clean);
	}

	$r = q("select * from channel where channel_account_id = %d and channel_guid = '%s' limit 1",
		intval($account_id),
		dbesc($channel['channel_guid'])
	);
	if(! $r) {
		logger('mod_import: channel not found. ' . print_r($channel,true));
		notice( t('Cloned channel not found. Import failed.') . EOL);
		return false;
	}

	// extract the permissions from the original imported array and use our new channel_id to set them
	// These could be in the old channel permission stule or the new pconfig. We have a function to
	// translate and store them no matter which they throw at us.

	$channel['channel_id'] = $r[0]['channel_id'];
	translate_channel_perms_inbound($channel);

	// reset
	$channel = $r[0];

	set_default_login_identity($account_id,$channel['channel_id'],false);
	logger('import step 1');
	$_SESSION['import_step'] = 1;

	return $channel;
}

/**
 * @brief Import pconfig for channel.
 *
 * @param array $channel
 * @param array $configs
 */
function import_config($channel, $configs) {

	if($channel && $configs) {
		foreach($configs as $config) {
			unset($config['id']);
			$config['uid'] = $channel['channel_id'];
			if($config['cat'] === 'system' && $config['k'] === 'import_system_apps') {
				continue;
			}
			create_table_from_array('pconfig', $config);
		}

		load_pconfig($channel['channel_id']);
	}
}

function import_atoken($channel, $atokens) {
	if ($channel && $atokens) {
		foreach ($atokens as $atoken) {
			unset($atoken['atoken_id']);
			$atoken['atoken_aid'] = $channel['channel_account_id'];
			$atoken['atoken_uid'] = $channel['channel_id'];
			create_table_from_array('atoken', $atoken);
		}
	}
}

function sync_atoken($channel, $atokens) {

	if ($channel && $atokens) {
		foreach ($atokens as $atoken) {
			unset($atoken['atoken_id']);
			$atoken['atoken_aid'] = $channel['channel_account_id'];
			$atoken['atoken_uid'] = $channel['channel_id'];

			if ($atoken['deleted']) {
				q("delete from atoken where atoken_uid = %d and atoken_guid = '%s' ",
					intval($atoken['atoken_uid']),
					dbesc($atoken['atoken_guid'])
				);
				continue;
			}

			$r = q("select * from atoken where atoken_uid = %d and atoken_guid = '%s' ",
				intval($atoken['atoken_uid']),
				dbesc($atoken['atoken_guid'])
			);
			if (! $r) {
				create_table_from_array('atoken', $atoken);
			}
			else {
				$columns = db_columns('atoken');
				foreach ($atoken as $k => $v) {
					if (! in_array($k,$columns)) {
						continue;
					}

					if (in_array($k, ['atoken_guid','atoken_uid','atoken_aid'])) {
						continue;
					}

					$r = q("UPDATE atoken SET " . TQUOT . "%s" . TQUOT . " = '%s' WHERE atoken_guid = '%s' AND atoken_uid = %d",
						dbesc($k),
						dbesc($v),
						dbesc($atoken['atoken_guid']),
						intval($channel['channel_id'])
					);
				}
			}
		}
	}
}


/**
 * @brief Import profiles.
 *
 * @param array $channel
 * @param array $profiles
 */
function import_profiles($channel, $profiles) {

	if($channel && $profiles) {
		foreach($profiles as $profile) {
			unset($profile['id']);
			$profile['aid'] = get_account_id();
			$profile['uid'] = $channel['channel_id'];

			convert_oldfields($profile,'name','fullname');
			convert_oldfields($profile,'with','partner');
			convert_oldfields($profile,'work','employment');

			/**
			 * @TODO put all the applicable photos into the export.
			 */

			if((strpos($profile['thumb'],'/photo/profile/l/') !== false) || intval($profile['is_default'])) {
				$profile['photo'] = z_root() . '/photo/profile/l/' . $channel['channel_id'];
				$profile['thumb'] = z_root() . '/photo/profile/m/' . $channel['channel_id'];
			}
			else {
				$profile['photo'] = z_root() . '/photo/' . basename($profile['photo']);
				$profile['thumb'] = z_root() . '/photo/' . basename($profile['thumb']);
			}

			profile_store_lowlevel($profile);
		}
	}
}

/**
 * @brief Import hublocs.
 *
 * @param array $channel
 * @param array $hublocs
 * @param boolean $seize
 * @param boolean $moving (optional) default false
 */
function import_hublocs($channel, $hublocs, $seize, $moving = false) {

	if($channel && $hublocs) {
		foreach($hublocs as $hubloc) {

			// verify the hash. We can only do this if we already stored the xchan corresponding to this hubloc
			// as we need the public key from there

			if ($hubloc['hubloc_network'] === 'zot6') {
				$x = q("select xchan_pubkey from xchan where xchan_guid = '%s' and xchan_hash = '%s'",
					dbesc($hubloc['hubloc_guid']),
					dbesc($hubloc['hubloc_hash'])
				);

				if (! $x) {
					logger('hubloc could not be verified. ' . print_r($hubloc,true));
					continue;
				}
				$hash = Libzot::make_xchan_hash($hubloc['hubloc_guid'],$x[0]['xchan_pubkey']);
				if ($hash !== $hubloc['hubloc_hash']) {
					logger('forged hubloc: ' . print_r($hubloc,true));
					continue;
				}
			}

			if($moving && $hubloc['hubloc_hash'] === $channel['channel_hash'] && $hubloc['hubloc_url'] !== z_root()) {
				$hubloc['hubloc_deleted'] = 1;
			}

			$arr = [
				'id'           => $hubloc['hubloc_guid'],
				'id_sig'       => $hubloc['hubloc_guid_sig'],
				'location'     => $hubloc['hubloc_url'],
				'location_sig' => $hubloc['hubloc_url_sig'],
				'site_id'      => $hubloc['hubloc_site_id']
			];

			if (($hubloc['hubloc_hash'] === $channel['channel_hash']) && intval($hubloc['hubloc_primary']) && ($seize)) {
				$hubloc['hubloc_primary'] = 0;
			}

			if (($x = Libzot::gethub($arr,false)) === false) {
				unset($hubloc['hubloc_id']);
				hubloc_store_lowlevel($hubloc);
			}
			else {
				q("UPDATE hubloc set hubloc_primary = %d, hubloc_deleted = %d where hubloc_id = %d",
					intval($hubloc['hubloc_primary']),
					intval($hubloc['hubloc_deleted']),
					intval($x['hubloc_id'])
				);
			}
		}
	}
}

/**
 * @brief Import things.
 *
 * @param array $channel
 * @param array $objs
 */
function import_objs($channel, $objs) {

	if($channel && $objs) {
		foreach($objs as $obj) {

			// if it's the old term format - too hard to support
			if(! $obj['obj_created'])
				continue;

			$baseurl = $obj['obj_baseurl'];
			unset($obj['obj_id']);
			unset($obj['obj_baseurl']);

			$obj['obj_channel'] = $channel['channel_id'];

			if($baseurl && (strpos($obj['obj_url'], $baseurl . '/thing/') !== false)) {
				$obj['obj_url'] = str_replace($baseurl, z_root(), $obj['obj_url']);
			}

			if($obj['obj_imgurl']) {
				$x = import_xchan_photo($obj['obj_imgurl'], $channel['channel_hash'], true);
				$obj['obj_imgurl'] = $x[0];
			}

			create_table_from_array('obj', $obj);
		}
	}
}

/**
 * @brief Import things.
 *
 * @param array $channel
 * @param array $objs
 */
function sync_objs($channel, $objs) {

	if($channel && $objs) {
		foreach($objs as $obj) {

			if(array_key_exists('obj_deleted',$obj) && $obj['obj_deleted'] && $obj['obj_obj']) {
				q("delete from obj where obj_obj = '%s' and obj_channel = %d",
					dbesc($obj['obj_obj']),
					intval($channel['channel_id'])
				);
				continue;
			}

			// if it's the old term format - too hard to support
			if(! $obj['obj_created'])
				continue;

			$baseurl = $obj['obj_baseurl'];
			unset($obj['obj_id']);
			unset($obj['obj_baseurl']);

			$obj['obj_channel'] = $channel['channel_id'];

			if($baseurl && (strpos($obj['obj_url'], $baseurl . '/thing/') !== false)) {
				$obj['obj_url'] = str_replace($baseurl, z_root(), $obj['obj_url']);
			}

			$exists = false;

			$x = q("select * from obj where obj_obj = '%s' and obj_channel = %d limit 1",
				dbesc($obj['obj_obj']),
				intval($channel['channel_id'])
			);
			if($x) {
				if($x[0]['obj_edited'] >= $obj['obj_edited'])
					continue;

				$exists = true;
			}

			if($obj['obj_imgurl']) {
				$x = import_xchan_photo($obj['obj_imgurl'], $channel['channel_hash'], true);
				$obj['obj_imgurl'] = $x[0];
			}

			$hash = $obj['obj_obj'];

			if($exists) {
				unset($obj['obj_obj']);
				foreach($obj as $k => $v) {
					$r = q("UPDATE obj SET " . TQUOT . "%s" . TQUOT . " = '%s' WHERE obj_obj = '%s' AND obj_channel = %d",
						dbesc($k),
						dbesc($v),
						dbesc($hash),
						intval($channel['channel_id'])
					);
				}
			}
			else {
				create_table_from_array('obj', $obj);
			}
		}
	}
}

/**
 * @brief Import apps.
 *
 * @param array $channel
 * @param array $apps
 */
function import_apps($channel, $apps) {

	if($channel && $apps) {
		foreach($apps as $app) {

			if(array_key_exists('app_system',$app) && intval($app['app_system']))
				continue;

			$term = ((array_key_exists('term',$app) && is_array($app['term'])) ? $app['term'] : null);

			unset($app['id']);
			unset($app['app_channel']);
			unset($app['term']);

			$app['app_channel'] = $channel['channel_id'];

			if($app['app_photo']) {
				$x = import_xchan_photo($app['app_photo'], $channel['channel_hash'], true);
				$app['app_photo'] = $x[0];
			}

			$hash = $app['app_id'];

			create_table_from_array('app', $app);

			if($term) {
				$x = q("select * from app where app_id = '%s' and app_channel = %d limit 1",
					dbesc($hash),
					intval($channel['channel_id'])
				);
				if($x) {
					foreach($term as $t) {
						if(array_key_exists('type',$t))
							$t['ttype'] = $t['type'];

						store_item_tag($channel['channel_id'],$x[0]['id'],TERM_OBJ_APP,$t['ttype'],escape_tags($t['term']),escape_tags($t['url']));
					}
				}
			}
		}
	}
}

/**
 * @brief Sync apps.
 *
 * @param array $channel
 * @param array $apps
 */
function sync_apps($channel, $apps) {

	if($channel && $apps) {
		foreach($apps as $app) {

			$exists = false;
			$term = ((array_key_exists('term',$app)) ? $app['term'] : null);

			if(array_key_exists('app_system',$app) && intval($app['app_system']))
				continue;

			$x = q("select * from app where app_id = '%s' and app_channel = %d limit 1",
				dbesc($app['app_id']),
				intval($channel['channel_id'])
			);
			if($x) {
				$exists = $x[0];
			}

			if(array_key_exists('app_deleted',$app) && $app['app_deleted'] && $app['app_id']) {
				q("delete from app where app_id = '%s' and app_channel = %d",
						dbesc($app['app_id']),
						intval($channel['channel_id'])
				);
				if($exists) {
					q("delete from term where otype = %d and oid = %d",
						intval(TERM_OBJ_APP),
						intval($exists['id'])
					);
				}
				continue;
			}

			unset($app['id']);
			unset($app['app_channel']);
			unset($app['term']);

			if($exists) {
				q("delete from term where otype = %d and oid = %d",
					intval(TERM_OBJ_APP),
					intval($exists['id'])
				);
			}

			if((! $app['app_created']) || ($app['app_created'] <= NULL_DATE))
				$app['app_created'] = datetime_convert();
			if((! $app['app_edited']) || ($app['app_edited'] <= NULL_DATE))
				$app['app_edited'] = datetime_convert();

			$app['app_channel'] = $channel['channel_id'];

			if($app['app_photo']) {
				$x = import_xchan_photo($app['app_photo'],$channel['channel_hash'],true);
				$app['app_photo'] = $x[0];
			}

			if($exists && $term) {
				foreach($term as $t) {
					if(array_key_exists('type',$t))
						$t['ttype'] = $t['type'];
					store_item_tag($channel['channel_id'],$exists['id'],TERM_OBJ_APP,$t['ttype'],escape_tags($t['term']),escape_tags($t['url']));
				}
			}

			if($exists) {
				if($exists['app_edited'] >= $app['app_edited'])
					continue;
			}
			$hash = $app['app_id'];

			if($exists) {
				unset($app['app_id']);
				foreach($app as $k => $v) {
					$r = q("UPDATE app SET " . TQUOT . "%s" . TQUOT . " = '%s' WHERE app_id = '%s' AND app_channel = %d",
						dbesc($k),
						dbesc($v),
						dbesc($hash),
						intval($channel['channel_id'])
					);
				}
			}
			else {
				create_table_from_array('app',$app);

				if($term) {
					$x = q("select * from app where app_id = '%s' and app_channel = %d",
						dbesc($hash),
						intval($channel['channel_id'])
					);
					if($x) {
						foreach($term as $t) {
							if(array_key_exists('type',$t))
								$t['ttype'] = $t['type'];
							store_item_tag($channel['channel_id'],$x[0]['id'],TERM_OBJ_APP,$t['ttype'],escape_tags($t['term']),escape_tags($t['url']));
						}
					}
				}
			}
		}
	}
}


/**
 * @brief Import system apps.
 * System apps from the original server may not exist on this system
 *   (e.g. apps associated with addons that are not installed here).
 *   Check the system apps that were provided in the import file to see if they
 *   exist here and if so, install them locally. Preserve categories that
 *   might have been added by this channel on the other server.
 *   Do not use any paths from the original as they will point to a different server.
 * @param array $channel
 * @param array $apps
 */
function import_sysapps($channel, $apps) {

	if ($channel && $apps) {

		$sysapps = Apps::get_system_apps(false, true);

		foreach ($apps as $app) {

			if (array_key_exists('app_system',$app) && (! intval($app['app_system']))) {
				continue;
			}

			if (array_key_exists('app_deleted',$app) && (intval($app['app_deleted']))) {
				continue;
			}

			$term = ((array_key_exists('term',$app) && is_array($app['term'])) ? $app['term'] : null);

			foreach ($sysapps as $sysapp) {
				if ($app['app_id'] === hash('whirlpool', $sysapp['name'])) {
					// install this app on this server
					$newapp = $sysapp;
					$newapp['uid'] = $channel['channel_id'];
					$newapp['guid'] = hash('whirlpool', $newapp['name']);

					$installed = q("select id from app where app_id = '%s' and app_channel = %d limit 1",
						dbesc($newapp['guid']),
						intval($channel['channel_id'])
					);
					if ($installed) {
						break;
					}

					$newapp['system'] = 1;
					if ($term) {
						$newapp['categories'] = array_elm_to_str($term, 'term');
					}
					Apps::app_install($channel['channel_id'], $newapp);
				}
			}
		}
	}
}


/**
 * @brief Sync system apps.
 *
 * @param array $channel
 * @param array $apps
 */
function sync_sysapps($channel, $apps) {

	$sysapps = Apps::get_system_apps(false, true);
	if ($channel && $apps) {

		$columns = db_columns('app');

		foreach ($apps as $app) {

			$term = ((array_key_exists('term',$app)) ? $app['term'] : null);

			if (array_key_exists('app_system',$app) && (! intval($app['app_system']))) {
				continue;
			}

			foreach ($sysapps as $sysapp) {

				if ($app['app_id'] === hash('whirlpool', $sysapp['name'])) {
					if (array_key_exists('app_deleted',$app) && $app['app_deleted'] == 1 && $app['app_id']) {
						Apps::app_destroy($channel['channel_id'], ['guid' => $app['app_id']]);
					}
					else {
						// install this app on this server
						$newapp = $sysapp;
						$newapp['uid'] = $channel['channel_id'];
						$newapp['guid'] = hash('whirlpool', $newapp['name']);
						$newapp['system'] = 1;
						if ($term) {
							$newapp['categories'] = array_elm_to_str($term, 'term');
						}
						Apps::app_install($channel['channel_id'], $newapp);
					}
				}
			}
		}
	}
}




/**
 * @brief Import chatrooms.
 *
 * @param array $channel
 * @param array $chatrooms
 */
function import_chatrooms($channel, $chatrooms) {

	if($channel && $chatrooms) {
		foreach($chatrooms as $chatroom) {

			if(! $chatroom['cr_name'])
				continue;

			unset($chatroom['cr_id']);
			unset($chatroom['cr_aid']);
			unset($chatroom['cr_uid']);

			$chatroom['cr_aid'] = $channel['channel_account_id'];
			$chatroom['cr_uid'] = $channel['channel_id'];

			create_table_from_array('chatroom', $chatroom);
		}
	}
}

/**
 * @brief Sync chatrooms.
 *
 * @param array $channel
 * @param array $chatrooms
 */
function sync_chatrooms($channel, $chatrooms) {

	if($channel && $chatrooms) {
		foreach($chatrooms as $chatroom) {

			if(! $chatroom['cr_name'])
				continue;

			if(array_key_exists('cr_deleted',$chatroom) && $chatroom['cr_deleted']) {
				q("delete from chatroom where cr_name = '%s' and cr_uid = %d",
						dbesc($chatroom['cr_name']),
						intval($channel['channel_id'])
				);
				continue;
			}

			unset($chatroom['cr_id']);
			unset($chatroom['cr_aid']);
			unset($chatroom['cr_uid']);

			if((! $chatroom['cr_created']) || ($chatroom['cr_created'] <= NULL_DATE))
				$chatroom['cr_created'] = datetime_convert();
			if((! $chatroom['cr_edited']) || ($chatroom['cr_edited'] <= NULL_DATE))
				$chatroom['cr_edited'] = datetime_convert();

			$chatroom['cr_aid'] = $channel['channel_account_id'];
			$chatroom['cr_uid'] = $channel['channel_id'];

			$exists = false;

			$x = q("select * from chatroom where cr_name = '%s' and cr_uid = %d limit 1",
				dbesc($chatroom['cr_name']),
				intval($channel['channel_id'])
			);
			if($x) {
				if($x[0]['cr_edited'] >= $chatroom['cr_edited'])
					continue;

				$exists = true;
			}
			$name = $chatroom['cr_name'];

			if($exists) {
				foreach($chatroom as $k => $v) {
					$r = q("UPDATE chatroom SET " . TQUOT . "%s" . TQUOT . " = '%s' WHERE cr_name = '%s' AND cr_uid = %d",
						dbesc($k),
						dbesc($v),
						dbesc($name),
						intval($channel['channel_id'])
					);
				}
			}
			else {
				create_table_from_array('chatroom', $chatroom);
			}
		}
	}
}


/**
 * @brief Import items to channel.
 *
 * @param array $channel where to import to
 * @param array $items
 * @param boolean $sync default false
 * @param array $relocate default null
 */
function import_items($channel, $items, $sync = false, $relocate = null) {

	if($channel && $items) {

		$allow_code = channel_codeallowed($channel['channel_id']);

		$deliver = false; // Don't deliver any messages or notifications when importing
		foreach($items as $i) {
			$item_result = false;
			$item = get_item_elements($i,$allow_code);
			if(! $item) {
				continue;
			}

			if($relocate && $item['mid'] === $item['parent_mid']) {
				item_url_replace($channel,$item,$relocate['url'],z_root(),$relocate['channel_address']);
			}

			$r = q("select id, edited from item where mid = '%s' and uid = %d and revision = %d limit 1",
				dbesc($item['mid']),
				intval($channel['channel_id']),
				intval($item['revision'])
			);
			if($r) {

				// flags may have changed and we are probably relocating the post,
				// so force an update even if we have the same timestamp

				if($item['edited'] >= $r[0]['edited']) {
					$item['id']  = $r[0]['id'];
					$item['uid'] = $channel['channel_id'];
					$item_result = item_store_update($item,$allow_code,$deliver);
				}
			}
			else {
				$item['aid'] = $channel['channel_account_id'];
				$item['uid'] = $channel['channel_id'];
				$item_result = item_store($item,$allow_code,$deliver);
			}

			// preserve conversations you've been involved in from being expired

			$stored = $item_result['item'];
			if((is_array($stored)) && ($stored['id'] != $stored['parent'])
				&& ($stored['author_xchan'] === $channel['channel_hash'])) {
				retain_item($stored['item']['parent']);
			}

			fix_attached_photo_permissions($channel['channel_id'],$item['author_xchan'],$item['body'],$item['allow_cid'],$item['allow_gid'],$item['deny_cid'],$item['deny_gid']);

			fix_attached_file_permissions($channel,$item['author_xchan'],$item['body'],$item['allow_cid'],$item['allow_gid'],$item['deny_cid'],$item['deny_gid']);

			if($sync && $item['item_wall']) {
				// deliver singletons if we have any
				if($item_result && $item_result['success']) {
					Zotlabs\Daemon\Master::Summon( [ 'Notifier','single_activity',$item_result['item_id'] ]);
				}
			}
		}
	}
}

/**
 * @brief Sync items to channel.
 *
 * @see import_items()
 *
 * @param array $channel where to import to
 * @param array $items
 * @param array $relocate default null
 */
function sync_items($channel, $items, $relocate = null) {

        // Check if this is sync of not Zot-related content and we're connected to the top post owner
        // to avoid confusing with cloned channels
        $size = count($items);
        for($i = 0; $i < $size; $i++) {
                if($items[$i]['owner']['network'] !== 'zot6') {
                        $r = q("SELECT * FROM abook WHERE abook_channel = %d
                                        AND abook_xchan = ( SELECT xchan_hash FROM xchan WHERE xchan_guid = '%s' LIMIT 1 )
                                        AND abook_not_here = 0 AND abook_ignored = 0 AND abook_blocked = 0",
                                intval($channel['channel_id']),
                                dbesc($items[$i]['owner']['guid'])
                        );
                        if(! $r)
                                unset($items[$i]);
                }
        }
        if(count($items) > 0)
                import_items($channel, $items, true, $relocate);
}

/**
 * @brief
 *
 * @param array $channel A channel array.
 * @param array $itemids
 */
function import_item_ids($channel, $itemids) {
	if($channel && $itemids) {
		foreach($itemids as $i) {
			$r = q("select id from item where mid = '%s' and uid = %d limit 1",
				dbesc($i['mid']),
				intval($channel['channel_id'])
			);
			if(! $r)
				continue;
			$z = q("select * from iconfig where iconfig.cat = 'system' and iconfig.k = '%s'
				and iconfig.v = '%s' and iid = %d limit 1",
				dbesc($i['service']),
				dbesc($i['sid']),
				intval($r[0]['id'])
			);
			if(! $z) {
				IConfig::Set($r[0]['id'],'system',$i['service'],$i['sid'],true);
			}
		}
	}
}

/**
 * @brief Import events.
 *
 * @param array $channel
 * @param array $events
 */
function import_events($channel, $events) {

	if($channel && $events) {
		foreach($events as $event) {
			unset($event['id']);
			$event['aid'] = $channel['channel_account_id'];
			$event['uid'] = $channel['channel_id'];
			convert_oldfields($event,'start','dtstart');
			convert_oldfields($event,'finish','dtend');
			convert_oldfields($event,'type','etype');
			convert_oldfields($event,'ignore','dismissed');

			create_table_from_array('event', $event);
		}
	}
}

/**
 * @brief Sync events.
 *
 * @param array $channel
 * @param array $events
 */
function sync_events($channel, $events) {

	if($channel && $events) {
		foreach($events as $event) {

			if((! $event['event_hash']) || (! $event['start']))
				continue;

			if($event['event_deleted']) {
				$r = q("delete from event where event_hash = '%s' and uid = %d",
					dbesc($event['event_hash']),
					intval($channel['channel_id'])
				);
				continue;
			}

			unset($event['id']);
			$event['aid'] = $channel['channel_account_id'];
			$event['uid'] = $channel['channel_id'];

			convert_oldfields($event,'start','dtstart');
			convert_oldfields($event,'finish','dtend');
			convert_oldfields($event,'type','etype');
			convert_oldfields($event,'ignore','dismissed');

			$exists = false;

			$x = q("select * from event where event_hash = '%s' and uid = %d limit 1",
				dbesc($event['event_hash']),
				intval($channel['channel_id'])
			);
			if($x) {
				if($x[0]['edited'] >= $event['edited'])
					continue;

				$exists = true;
			}

			if($exists) {
				foreach($event as $k => $v) {
					$r = q("UPDATE event SET " . TQUOT . "%s" . TQUOT . " = '%s' WHERE event_hash = '%s' AND uid = %d",
						dbesc($k),
						dbesc($v),
						dbesc($event['event_hash']),
						intval($channel['channel_id'])
					);
				}
			}
			else {
				create_table_from_array('event', $event);
			}
		}
	}
}

/**
 * @brief Import menus.
 *
 * @param array $channel
 * @param array $menus
 */
function import_menus($channel, $menus) {

	if($channel && $menus) {
		foreach($menus as $menu) {
			$m = array();
			$m['menu_channel_id'] = $channel['channel_id'];
			$m['menu_name'] = $menu['pagetitle'];
			$m['menu_desc'] = $menu['desc'];
			if($menu['created'])
				$m['menu_created'] = datetime_convert('UTC','UTC',$menu['created']);
			if($menu['edited'])
				$m['menu_edited'] = datetime_convert('UTC','UTC',$menu['edited']);

			$m['menu_flags'] = 0;
			if($menu['flags']) {
				if(in_array('bookmark',$menu['flags']))
					$m['menu_flags'] |= MENU_BOOKMARK;
				if(in_array('system',$menu['flags']))
					$m['menu_flags'] |= MENU_SYSTEM;
			}

			$menu_id = menu_create($m);

			if($menu_id) {
				if(is_array($menu['items'])) {
					foreach($menu['items'] as $it) {
						$mitem = array();

						$mitem['mitem_link'] = str_replace('[channelurl]',z_root() . '/channel/' . $channel['channel_address'],$it['link']);
						$mitem['mitem_link'] = str_replace('[pageurl]',z_root() . '/page/' . $channel['channel_address'],$it['link']);
						$mitem['mitem_link'] = str_replace('[cloudurl]',z_root() . '/cloud/' . $channel['channel_address'],$it['link']);
						$mitem['mitem_link'] = str_replace('[baseurl]',z_root(),$it['link']);

						$mitem['mitem_desc'] = escape_tags($it['desc']);
						$mitem['mitem_order'] = intval($it['order']);
						if(is_array($it['flags'])) {
							$mitem['mitem_flags'] = 0;
							if(in_array('zid',$it['flags']))
								$mitem['mitem_flags'] |= MENU_ITEM_ZID;
							if(in_array('new-window',$it['flags']))
								$mitem['mitem_flags'] |= MENU_ITEM_NEWWIN;
							if(in_array('chatroom',$it['flags']))
								$mitem['mitem_flags'] |= MENU_ITEM_CHATROOM;
						}
						menu_add_item($menu_id,$channel['channel_id'],$mitem);
					}
				}
			}
		}
	}
}

/**
 * @brief Sync menus.
 *
 * @param array $channel
 * @param array $menus
 */
function sync_menus($channel, $menus) {

	if($channel && $menus) {
		foreach($menus as $menu) {
			$m = array();
			$m['menu_channel_id'] = $channel['channel_id'];
			$m['menu_name'] = $menu['pagetitle'];
			$m['menu_desc'] = $menu['desc'];
			if($menu['created'])
				$m['menu_created'] = datetime_convert('UTC','UTC',$menu['created']);
			if($menu['edited'])
				$m['menu_edited'] = datetime_convert('UTC','UTC',$menu['edited']);

			$m['menu_flags'] = 0;
			if($menu['flags']) {
				if(in_array('bookmark',$menu['flags']))
					$m['menu_flags'] |= MENU_BOOKMARK;
				if(in_array('system',$menu['flags']))
					$m['menu_flags'] |= MENU_SYSTEM;

			}

			$editing = false;

			$r = q("select * from menu where menu_name = '%s' and menu_channel_id = %d limit 1",
				dbesc($m['menu_name']),
				intval($channel['channel_id'])
			);
			if($r) {
				if($r[0]['menu_edited'] >= $m['menu_edited'])
					continue;
				if($menu['menu_deleted']) {
					menu_delete_id($r[0]['menu_id'],$channel['channel_id']);
					continue;
				}
				$menu_id = $r[0]['menu_id'];
				$m['menu_id'] = $r[0]['menu_id'];
				$x = menu_edit($m);
				if(! $x)
					continue;
				$editing = true;
			}
			if(! $editing) {
				$menu_id = menu_create($m);
			}
			if($menu_id) {
				if($editing) {
					// don't try syncing - just delete all the entries and start over
					q("delete from menu_item where mitem_menu_id = %d",
						intval($menu_id)
					);
				}

				if(is_array($menu['items'])) {
					foreach($menu['items'] as $it) {
						$mitem = array();

						$mitem['mitem_link'] = str_replace('[channelurl]',z_root() . '/channel/' . $channel['channel_address'],$it['link']);
						$mitem['mitem_link'] = str_replace('[pageurl]',z_root() . '/page/' . $channel['channel_address'],$it['link']);
						$mitem['mitem_link'] = str_replace('[cloudurl]',z_root() . '/cloud/' . $channel['channel_address'],$it['link']);
						$mitem['mitem_link'] = str_replace('[baseurl]',z_root(),$it['link']);

						$mitem['mitem_desc'] = escape_tags($it['desc']);
						$mitem['mitem_order'] = intval($it['order']);
						if(is_array($it['flags'])) {
							$mitem['mitem_flags'] = 0;
							if(in_array('zid',$it['flags']))
								$mitem['mitem_flags'] |= MENU_ITEM_ZID;
							if(in_array('new-window',$it['flags']))
								$mitem['mitem_flags'] |= MENU_ITEM_NEWWIN;
							if(in_array('chatroom',$it['flags']))
								$mitem['mitem_flags'] |= MENU_ITEM_CHATROOM;
						}
						menu_add_item($menu_id,$channel['channel_id'],$mitem);
					}
				}
			}
		}
	}
}

/**
 * @brief Import likes.
 *
 * @param array $channel
 * @param array $likes
 */
function import_likes($channel, $likes) {
	if($channel && $likes) {
		foreach($likes as $like) {
			if($like['deleted']) {
				q("delete from likes where liker = '%s' and likee = '%s' and verb = '%s' and target_type = '%s' and target_id = '%s'",
					dbesc($like['liker']),
					dbesc($like['likee']),
					dbesc($like['verb']),
					dbesc($like['target_type']),
					dbesc($like['target_id'])
				);
				continue;
			}

			unset($like['id']);
			unset($like['iid']);
			$like['channel_id'] = $channel['channel_id'];
			$r = q("select * from likes where liker = '%s' and likee = '%s' and verb = '%s' and target_type = '%s' and target_id = '%s' and i_mid = '%s'",
				dbesc($like['liker']),
				dbesc($like['likee']),
				dbesc($like['verb']),
				dbesc($like['target_type']),
				dbesc($like['target_id']),
				dbesc($like['i_mid'])
			);
			if($r)
				continue;

			create_table_from_array('likes', $like);
		}
	}
}

/**
 * @brief Synchronise files.
 *
 * @param array $channel
 * @param array $files
 */
function sync_files($channel, $files) {

	require_once('include/attach.php');

	if($channel && $files) {

		$limit = service_class_fetch($channel['channel_id'], 'attach_upload_limit');

		foreach($files as $f) {
			if(! $f)
				continue;

			$fetch_url = $f['fetch_url'];
			$oldbase = dirname($fetch_url);
			$original_channel = $f['original_channel'];

			if(! ($fetch_url && $original_channel))
				continue;

			if($f['attach']) {
				$attachment_stored = false;
				foreach($f['attach'] as $att) {

					convert_oldfields($att,'data','content');

					if($att['deleted']) {
						attach_delete($channel['channel_id'],$att['hash']);
						continue;
					}

					$term =	$att['term'];
					unset($att['term']);

					$attach_exists = false;
					$x = attach_by_hash($att['hash'],$channel['channel_hash']);
					logger('sync_files duplicate check: attach_exists=' . $attach_exists, LOGGER_DEBUG);
					logger('sync_files duplicate check: att=' . print_r($att,true), LOGGER_DEBUG);
					logger('sync_files duplicate check: attach_by_hash() returned ' . print_r($x,true), LOGGER_DEBUG);

					if($x['success']) {
						$orig_attach = $x['data'];
						$attach_exists = true;
						$attach_id = $orig_attach['id'];
					}

					$newfname = 'store/' . $channel['channel_address'] . '/' . get_attach_binname($att['content']);

 					unset($att['id']);
					$att['aid'] = $channel['channel_account_id'];
					$att['uid'] = $channel['channel_id'];

					// check for duplicate folder names with the same parent.
					// If we have a duplicate that doesn't match this hash value
					// change the name so that the contents won't be "covered over"
					// by the existing directory. Use the same logic we use for
					// duplicate files.

					if(strpos($att['filename'],'.') !== false) {
						$basename = substr($att['filename'],0,strrpos($att['filename'],'.'));
						$ext = substr($att['filename'],strrpos($att['filename'],'.'));
					}
					else {
						$basename = $att['filename'];
						$ext = '';
					}

					$r = q("select filename from attach where ( filename = '%s' OR filename like '%s' ) and folder = '%s' and hash != '%s' and uid = %d ",
						dbesc($basename . $ext),
						dbesc($basename . '(%)' . $ext),
						dbesc($att['folder']),
						dbesc($att['hash']),
						intval($channel['channel_id'])
					);

					if($r) {
						$x = 1;

						do {
							$found = false;
							foreach($r as $rr) {
								if($rr['filename'] === $basename . '(' . $x . ')' . $ext) {
									$found = true;
									break;
								}
							}
							if($found)
								$x++;
						}
						while($found);
						$att['filename'] = $basename . '(' . $x . ')' . $ext;
					}
					else
						$att['filename'] = $basename . $ext;

					// end duplicate detection

					/// @FIXME update attachment structures if they are modified rather than created

					$att['content'] = $newfname;

					// Note: we use $att['hash'] below after it has been escaped to
					// fetch the file contents.
					// If the hash ever contains any escapable chars this could cause
					// problems. Currently it does not.

					if(!isset($att['os_path']))
						$att['os_path'] = '';

					if($attach_exists) {
						logger('sync_files attach exists: ' . print_r($att,true), LOGGER_DEBUG);

						// process/sync a remote rename/move operation

						if($orig_attach['content'] !== $newfname) {
							rename($orig_attach['content'],$newfname);
						}

						if(! dbesc_array($att))
							continue;

						$str = '';
						foreach($att as $k => $v) {
							if($str)
								$str .= ",";

							$str .= " " . TQUOT . $k . TQUOT . " = '" . $v . "' ";
						}
						$r = dbq("update attach set " . $str . " where id = " . intval($attach_id) );
					}
					else {
						logger('sync_files attach does not exists: ' . print_r($att,true), LOGGER_DEBUG);

				        if($limit !== false) {
				            $r = q("select sum(filesize) as total from attach where aid = %d ",
                				intval($channel['channel_account_id'])
            				);
				            if(($r) &&  (($r[0]['total'] + $att['filesize']) > $limit)) {
								logger('service class limit exceeded');
                				continue;
							}
						}

						create_table_from_array('attach',$att);
					}

					// is this a directory?

					if($att['filetype'] === 'multipart/mixed' && $att['is_dir']) {
						os_mkdir($newfname, STORAGE_DEFAULT_PERMISSIONS,true);
						$attachment_stored = true;
						continue;
					}
					else {
						// it's a file
						// for the sync version of this algorithm (as opposed to 'offline import')
						// we will fetch the actual file from the source server so it can be
						// streamed directly to disk and avoid consuming PHP memory if it's a huge
						// audio/video file or something.

						$time = datetime_convert();

						$parr = array('hash' => $channel['channel_hash'],
							'time' => $time,
							'resource' => $att['hash'],
							'revision' => 0,
							'signature' => Libzot::sign($channel['channel_hash'] . '.' . $time, $channel['channel_prvkey'])
						);

						$store_path = $newfname;


						$fp = fopen($newfname,'w');
						if(! $fp) {
							logger('failed to open storage file.',LOGGER_NORMAL,LOG_ERR);
							continue;
						}
						$redirects = 0;

						$headers = [];
						$headers['Accept'] = 'application/x-zot+json' ;
						$headers['Sigtoken'] = random_string();
						$headers = HTTPSig::create_sig($headers, $channel['channel_prvkey'], channel_url($channel), true, 'sha512');

						$x = z_post_url($fetch_url,$parr,$redirects,[ 'filep' => $fp, 'headers' => $headers]);
						fclose($fp);

						if($x['success']) {
							$attachment_stored = true;

							$a = q("SELECT id FROM attach WHERE hash = '%s' AND uid = %d LIMIT 1",
								dbesc($att['hash']),
								intval($channel['channel_id'])
							);
							if($a) {
								q("DELETE FROM term WHERE uid = %d AND oid = %d AND otype = %d",
									intval($channel['channel_id']),
									intval($a[0]['id']),
									intval(TERM_OBJ_FILE)
								);
								if($term) {
									foreach($term as $t) {
										if(array_key_exists('type',$t))
											$t['ttype'] = $t['type'];
										store_item_tag($channel['channel_id'], $a[0]['id'], TERM_OBJ_FILE, $t['ttype'], escape_tags($t['term']), escape_tags($t['url']));
									}
								}
							}
						}
						continue;
					}
				}
			}
			if(! $attachment_stored) {
				/// @TODO should we queue this and retry or delete everything or what?
				logger('attachment store failed',LOGGER_NORMAL,LOG_ERR);
			}
			if($f['photo']) {

				foreach($f['photo'] as $p) {
 					unset($p['id']);
					$p['aid'] = $channel['channel_account_id'];
					$p['uid'] = $channel['channel_id'];

					convert_oldfields($p,'data','content');
					convert_oldfields($p,'scale','imgscale');
					convert_oldfields($p,'size','filesize');
					convert_oldfields($p,'type','mimetype');

					// if this is a profile photo, undo the profile photo bit
					// for any other photo which previously held it.

					if($p['photo_usage'] == PHOTO_PROFILE) {
						$e = q("update photo set photo_usage = %d where photo_usage = %d
							and resource_id != '%s' and uid = %d ",
							intval(PHOTO_NORMAL),
							intval(PHOTO_PROFILE),
							dbesc($p['resource_id']),
							intval($channel['channel_id'])
						);
						$update_xchan = $p['edited'];
					}

					// same for cover photos

					if($p['photo_usage'] == PHOTO_COVER) {
						$e = q("update photo set photo_usage = %d where photo_usage = %d
							and resource_id != '%s' and uid = %d ",
							intval(PHOTO_NORMAL),
							intval(PHOTO_COVER),
							dbesc($p['resource_id']),
							intval($channel['channel_id'])
						);
					}

					if(intval($p['os_storage'])) {
						$p['content'] = $store_path . ((intval($p['imgscale'])) ? '-' . $p['imgscale'] : '');
					}
					else {
						$p['content'] = (($p['content'])? base64_decode($p['content']) : '');
					}

					if(intval($p['imgscale'])) {

						$time = datetime_convert();

						$parr = array(
						    'hash' => $channel['channel_hash'],
							'time' => $time,
							'resource' => $p['resource_id'],
							'revision' => 0,
							'signature'  => Libzot::sign($channel['channel_hash'] . '.' . $time, $channel['channel_prvkey']),
							'resolution' => intval($p['imgscale'])
						);

						$stored_image = $newfname . '-' . $p['imgscale'];

						$fp = fopen($stored_image,'w');
						if(! $fp) {
							logger('failed to open storage file.',LOGGER_NORMAL,LOG_ERR);
							continue;
						}
						$redirects = 0;

						$headers = [];
						$headers['Accept'] = 'application/x-zot+json' ;
						$headers['Sigtoken'] = random_string();
						$headers = HTTPSig::create_sig($headers, $channel['channel_prvkey'], channel_url($channel), true, 'sha512');

						$x = z_post_url($fetch_url,$parr,$redirects,[ 'filep' => $fp, 'headers' => $headers]);
						fclose($fp);

						// Override remote hub thumbnails storage settings
						if(! boolval(get_config('system','photo_storage_type', 1))) {
							$p['os_storage'] = 0;
							$p['content'] = file_get_contents($stored_image);
							@unlink($stored_image);
						}
						else {
							$p['os_storage'] = 1;
							$p['content'] = $stored_image;
						}
					}

					if(!isset($p['display_path']))
						$p['display_path'] = '';

					$exists = q("select * from photo where resource_id = '%s' and imgscale = %d and uid = %d limit 1",
						dbesc($p['resource_id']),
						intval($p['imgscale']),
						intval($channel['channel_id'])
					);

					if($exists) {

						$str = '';
						foreach($p as $k => $v) {
							$matches = false;
							if(preg_match('/([^a-zA-Z0-9\-\_\.])/',$k,$matches)) {
								continue;
							}

							if($str)
								$str .= ",";

							$str .= " " . TQUOT . $k . TQUOT . " = '" . (($k === 'content') ? dbescbin($v) : dbesc($v)) . "' ";
						}
						$r = dbq("update photo set " . $str . " where id = " . intval($exists[0]['id']) );
					}
					else {
						create_table_from_array('photo',$p, [ 'content' ] );
					}
				}

			}

            // Set xchan photo date to prevent thumbnails fetch for clones on profile update packet recieve
			if(isset($update_xchan)) {

				$x = q("UPDATE xchan SET xchan_photo_date = '%s' WHERE xchan_hash = '%s'",
					dbescdate($update_xchan),
					dbesc($channel['channel_hash'])
				);
			}

			\Zotlabs\Daemon\Master::Summon([ 'Thumbnail' , $att['hash'] ]);

			if($f['item']) {
				sync_items($channel,$f['item'],
					['channel_address' => $original_channel,'url' => $oldbase]
				);
			}
		}
	}
}


/**
 * @brief Synchronize addressbooks.
 *
 * @param array $channel
 * @param array $data
 */
function sync_addressbook($channel, $data) {

	if(! \Zotlabs\Lib\Apps::system_app_installed($channel['channel_id'], 'CardDAV'))
		return;

	logger("debug: " . print_r($data,true), LOGGER_DEBUG);

	require_once('include/cdav.php');

	$principalUri = 'principals/' . $channel['channel_address'];

	if($data['action'] !== 'create') {
	    $id = get_cdav_id($principalUri, $data['uri'], 'addressbooks');
	    if(! $id)
	        return;
		$id = $id['id'];
	}

	$pdo = \DBA::$dba->db;

	$carddavBackend = new \Sabre\CardDAV\Backend\PDO($pdo);
	$addressbooks = $carddavBackend->getAddressBooksForUser($principalUri);

	switch($data['action']) {

		case 'create':
			$carddavBackend->createAddressBook($principalUri, $data['uri'], $data['properties']);
			break;

		case 'drop':
			$carddavBackend->deleteAddressBook($id);
			break;

		case 'edit':
			$patch = new \Sabre\DAV\PropPatch($data['mutations']);
			$carddavBackend->updateAddressBook($id, $patch);
			$patch->commit();
			break;

		case 'delete_card':
			$carddavBackend->deleteCard($id, $data['carduri']);
			break;

		case 'update_card':
			$vcard = \Sabre\VObject\Reader::read($data['card']);
			$object = $vcard->convert(\Sabre\VObject\Document::VCARD40);
			$cardData = $vcard->serialize();
			$carddavBackend->updateCard($id, $data['carduri'], $cardData);
			break;

		case 'import':
			$objects = new \Sabre\VObject\Splitter\VCard($data['card']);
			$profile = \Sabre\VObject\Node::PROFILE_CARDDAV;
			import_cdav_card($id, 'vcf', 'cards', 'addressbookid', $objects, $profile, $carddavBackend, $data['ids']);
			break;

		default:
			break;
	}
}


/**
 * @brief Synchronize calendars.
 *
 * @param array $channel
 * @param array $data
 */
function sync_calendar($channel, $data) {

	if(! \Zotlabs\Lib\Apps::system_app_installed($channel['channel_id'], 'Calendar'))
		return;

	logger("debug: " . print_r($data,true), LOGGER_DEBUG);

	require_once('include/cdav.php');

        $principalUri = 'principals/' . $channel['channel_address'];

	if($data['action'] !== 'create') {
		$x = get_cdav_id($principalUri, $data['uri'], 'calendarinstances');
		if(! $x)
			return;
		$id = [ $x['id'], $x['calendarid'] ];
	}

	$pdo = \DBA::$dba->db;

	$caldavBackend = new \Sabre\CalDAV\Backend\PDO($pdo);
	$calendars = $caldavBackend->getCalendarsForUser($principalUri);

	switch($data['action']) {

		case 'create':
			$id = $caldavBackend->createCalendar($principalUri, $data['uri'], $data['properties']);
			set_pconfig($channel['channel_id'], 'cdav_calendar', $id[0], 1);
			break;

		case 'drop':
			$caldavBackend->deleteCalendar($id);
			break;

		case 'edit':
			$patch = new \Sabre\DAV\PropPatch($data['mutations']);
			$caldavBackend->updateCalendar($id, $patch);
			$patch->commit();
			break;

		case 'delete_card':
			$caldavBackend->deleteCalendarObject($id, $data['carduri']);
			break;

		case 'update_card':
			$caldavBackend->updateCalendarObject($id, $data['carduri'], $data['card']);
			break;

		case 'switch':
			set_pconfig($channel['channel_id'], 'cdav_calendar', $id[0], $data['switch']);
			break;

		case 'import':
			$objects = new \Sabre\VObject\Splitter\ICalendar($data['card']);
			$profile = \Sabre\VObject\Node::PROFILE_CALDAV;
			import_cdav_card($id, 'ics', 'calendarobjects', 'calendarid', $objects, $profile, $caldavBackend, $data['ids']);
			break;

		default:
			break;
	}
}


/**
 * @brief Rename a key in an array.
 *
 * Replaces $old key with $new key in $arr.
 *
 * @param[in,out] array &$arr The array where to work on
 * @param string $old The old key in the array
 * @param string $new The new key in the array
 */
function convert_oldfields(&$arr, $old, $new) {
	if(array_key_exists($old, $arr)) {
		$arr[$new] = $arr[$old];
		unset($arr[$old]);
	}
}

function scan_webpage_elements($path, $type, $cloud = false) {
	$channel = \App::get_channel();
	$dirtoscan = $path;
	switch ($type) {
		case 'page':
			$dirtoscan .= '/pages/';
			$json_filename = 'page.json';
			break;
		case 'layout':
			$dirtoscan .= '/layouts/';
			$json_filename = 'layout.json';
			break;
		case 'block':
			$dirtoscan .= '/blocks/';
			$json_filename = 'block.json';
			break;
		default :
			return array();
	}
	if($cloud) {
		$dirtoscan = get_dirpath_by_cloudpath($channel, $dirtoscan);
	}
	$elements = [];
	if(is_dir($dirtoscan)) {
		$dirlist = scandir($dirtoscan);
		if($dirlist) {
			foreach($dirlist as $element) {
				if($element === '.' || $element === '..') {
					continue;
				}
				$folder = $dirtoscan . '/' . $element;
				if(is_dir($folder)) {
					if($cloud) {
						$jsonfilepath = $folder . '/' . get_filename_by_cloudname($json_filename, $channel, $folder);
					}
					else {
						$jsonfilepath = $folder . '/' . $json_filename;
					}
					if(is_file($jsonfilepath)) {
						$metadata = json_decode(file_get_contents($jsonfilepath), true);
						if($cloud) {
							$contentfilename = get_filename_by_cloudname($metadata['contentfile'], $channel, $folder);
							$metadata['path'] = $folder . '/' . $contentfilename;
						}
						else {
							$contentfilename = $metadata['contentfile'];
							$metadata['path'] = $folder . '/' . $contentfilename;
						}
						if($metadata['contentfile'] === '') {
							logger('Invalid ' . $type . ' content file');
							return false;
						}
						$content = file_get_contents($folder . '/' . $contentfilename);
						if(!$content) {
							if(is_readable($folder . '/' . $contentfilename)) {
								$content = '';
							}
							else {
								logger('Failed to get file content for ' . $metadata['contentfile']);
								return false;
							}
						}
						$elements[] = $metadata;
					}
				}
			}
		}
	}

	return $elements;
}


function import_webpage_element($element, $channel, $type) {

	$arr = array();		// construct information for the webpage element item table record

	switch($type) {
		//
		//	PAGES
		//
		case 'page':
			$arr['item_type'] = ITEM_TYPE_WEBPAGE;
			$namespace = 'WEBPAGE';
			$name = $element['pagelink'];
			if($name) {
				$name = strtolower(URLify::transliterate($name));
			}
			$arr['title'] = $element['title'];
			$arr['term'] = $element['term'];
			$arr['layout_mid'] = ''; // by default there is no layout associated with the page
			// If a layout was specified, find it in the database and get its info. If
			// it does not exist, leave layout_mid empty
			if($element['layout'] !== '') {
				$liid = q("select iid from iconfig where k = 'PDL' and v = '%s' and cat = 'system'",
					dbesc($element['layout'])
				);
				if($liid) {
					$linfo = q("select mid from item where id = %d",
						intval($liid[0]['iid'])
					);
					$arr['layout_mid'] = $linfo[0]['mid'];
				}
			}
			break;
		//
		//	LAYOUTS
		//
		case 'layout':
			$arr['item_type'] = ITEM_TYPE_PDL;
			$namespace = 'PDL';
			$name = $element['name'];
			$arr['title'] = $element['description'];
			$arr['term'] = $element['term'];
			break;
		//
		//	BLOCKS
		//
		case 'block':
			$arr['item_type'] = ITEM_TYPE_BLOCK;
			$namespace = 'BUILDBLOCK';
			$name = $element['name'];
			$arr['title'] = $element['title'];

			break;
		default :
			return null;	// return null if invalid element type
	}

	$arr['uid'] = local_channel();
	$arr['aid'] = $channel['channel_account_id'];

	// Check if an item already exists based on the name
	$iid = q("select iid from iconfig where k = '" . $namespace . "' and v = '%s' and cat = 'system'",
		dbesc($name)
	);
	if($iid) { // If the item does exist, get the item metadata
		$iteminfo = q("select mid,created,edited from item where id = %d",
			intval($iid[0]['iid'])
		);
		$arr['mid'] = $arr['parent_mid'] = $iteminfo[0]['mid'];
		$arr['created'] = $iteminfo[0]['created'];
	}
	else { // otherwise, generate the creation times and unique id
		$arr['created'] = datetime_convert();
		$arr['uuid'] = item_message_id();
		$arr['mid'] = $arr['parent_mid'] = z_root() . '/item/' . $arr['uuid'];
	}
	// Update the edited time whether or not the element already exists
	$arr['edited'] = datetime_convert();
	// Import the actual element content
	$arr['body'] = file_get_contents($element['path']);
	// The element owner is the channel importing the elements
	$arr['owner_xchan'] = get_observer_hash();
	// The author is either the owner or whomever was specified
	$arr['author_xchan'] = (($element['author_xchan']) ? $element['author_xchan'] : get_observer_hash());
	// Import mimetype if it is a valid mimetype for the element
	$mimetypes = [
		'text/bbcode',
		'text/html',
		'text/markdown',
		'text/plain',
		'application/x-pdl',
		'application/x-php'
	];
	// Blocks and pages can have any of the valid mimetypes, but layouts must be text/bbcode
	if((in_array($element['mimetype'], $mimetypes))	&& ($type === 'page' || $type === 'block') ) {
		$arr['mimetype'] = $element['mimetype'];
	}
	else {
		$arr['mimetype'] = 'text/bbcode';
	}

	// Verify ability to use html or php!!!

	$execflag = channel_codeallowed(local_channel());

	$i = q("select id, edited, item_deleted from item where mid = '%s' and uid = %d limit 1",
		dbesc($arr['mid']),
		intval(local_channel())
	);

	IConfig::Set($arr,'system',$namespace,(($name) ? $name : substr($arr['mid'],0,16)),true);

	if($i) {
		$arr['id'] = $i[0]['id'];
		// don't update if it has the same timestamp as the original
		if($arr['edited'] > $i[0]['edited'])
			$x = item_store_update($arr,$execflag);
	}
	else {
		if(($i) && (intval($i[0]['item_deleted']))) {
			// was partially deleted already, finish it off
			q("delete from item where mid = '%s' and uid = %d",
				dbesc($arr['mid']),
				intval(local_channel())
			);
		}
		else
			$x = item_store($arr,$execflag);
	}

	if($x && $x['success']) {
		//$item_id = $x['item_id'];
		//update_remote_id($channel, $item_id, $arr['item_type'], $name, $namespace, $remote_id, $arr['mid']);
		$element['import_success'] = 1;
	}
	else {
		$element['import_success'] = 0;
	}

	return $element;
}

function get_webpage_elements($channel, $type = 'all') {
	$elements = array();
	if(!$channel['channel_id']) {
		return null;
	}
	switch($type) {
		case 'all':
			// If all, execute all the pages, layouts, blocks case statements
		case 'pages':
			$elements['pages'] = null;
			$owner = $channel['channel_id'];

			$sql_extra = item_permissions_sql($owner);

			$r = q("select * from iconfig left join item on iconfig.iid = item.id
				where item.uid = %d and iconfig.cat = 'system' and iconfig.k = 'WEBPAGE' and item_type = %d
				$sql_extra order by item.created desc",
				intval($owner),
				intval(ITEM_TYPE_WEBPAGE)
			);

			$pages = null;

			if($r) {
				$elements['pages'] = array();
				$pages = array();
				foreach($r as $rr) {

					//$lockstate = (($rr['allow_cid'] || $rr['allow_gid'] || $rr['deny_cid'] || $rr['deny_gid']) ? 'lock' : 'unlock');

					$element_arr = array(
						'type'		=> 'webpage',
						'title'		=> $rr['title'],
						'body'		=> $rr['body'],
						'created'	=> $rr['created'],
						'edited'	=> $rr['edited'],
						'mimetype'	=> $rr['mimetype'],
						'pagetitle'	=> $rr['v'],
						'mid'		=> $rr['mid'],
						'layout_mid'    => $rr['layout_mid']
					);
					$pages[$rr['iid']][] = array(
						'url'		=> $rr['iid'],
						'pagetitle'	=> $rr['v'],
						'title'		=> $rr['title'],
						'created'	=> datetime_convert('UTC',date_default_timezone_get(),$rr['created']),
						'edited'	=> datetime_convert('UTC',date_default_timezone_get(),$rr['edited']),
						'bb_element'	=> '[element]' . base64url_encode(json_encode($element_arr)) . '[/element]',
						//'lockstate'     => $lockstate
					);
					$elements['pages'][] = $element_arr;
				}
			}
			if($type !== 'all') {
				break;
			}

		case 'layouts':
			$elements['layouts'] = null;
			$owner = $channel['channel_id'];

			$sql_extra = item_permissions_sql($owner);

			$r = q("select * from iconfig left join item on iconfig.iid = item.id
				where item.uid = %d and iconfig.cat = 'system' and iconfig.k = 'PDL' and item_type = %d
				$sql_extra order by item.created desc",
				intval($owner),
				intval(ITEM_TYPE_PDL)
			);

			if($r) {
				$elements['layouts'] = array();

				foreach($r as $rr) {
					$elements['layouts'][] = array(
						'type'        => 'layout',
						'description' => $rr['title'],		// description of the layout
						'body'        => $rr['body'],
						'created'     => $rr['created'],
						'edited'      => $rr['edited'],
						'mimetype'    => $rr['mimetype'],
						'name'        => $rr['v'],			// name of reference for the layout
						'mid'         => $rr['mid'],
					);
				}
			}

			if($type !== 'all') {
				break;
			}

		case 'blocks':
			$elements['blocks'] = null;
			$owner = $channel['channel_id'];

			$sql_extra = item_permissions_sql($owner);

			$r = q("select iconfig.iid, iconfig.k, iconfig.v, mid, title, body, mimetype, created, edited from iconfig
				left join item on iconfig.iid = item.id
				where uid = %d and iconfig.cat = 'system' and iconfig.k = 'BUILDBLOCK'
				and item_type = %d order by item.created desc",
				intval($owner),
				intval(ITEM_TYPE_BLOCK)
			);

			if($r) {
				$elements['blocks'] = array();

				foreach($r as $rr) {
					$elements['blocks'][] = array(
						'type'      => 'block',
						'title'     => $rr['title'],
						'body'      => $rr['body'],
						'created'   => $rr['created'],
						'edited'    => $rr['edited'],
						'mimetype'  => $rr['mimetype'],
						'name'      => $rr['v'],
						'mid'       => $rr['mid']
					);
				}
			}

			if($type !== 'all') {
				break;
			}

		default:
			break;
	}

	return $elements;
}

/**
 * @brief Create a compressed zip file.
 *
 * @param array $files List of files to put in zip file
 * @param string $destination
 * @param boolean $overwrite
 * @return boolean Success status
 */
function create_zip_file($files = array(), $destination = '', $overwrite = false) {
	// if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) {
		return false;
	}
	//vars
	$valid_files = array();
	// if files were passed in...
	if(is_array($files)) {
		// cycle through each file
		foreach($files as $file) {
			// make sure the file exists
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}

	// if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination, $overwrite ? ZipArchive::OVERWRITE : ZipArchive::CREATE) !== true) {
			return false;
		}
		// add the files
		foreach($valid_files as $file) {
			$zip->addFile($file, $file);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		//close the zip -- done!
		$zip->close();

		// check to make sure the file exists
		return file_exists($destination);
	}
	else {
		return false;
	}
}
