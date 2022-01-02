<?php
namespace Zotlabs\Module;

use App;
use Zotlabs\Lib\AccessList;

require_once('include/security.php');

class Lockview extends \Zotlabs\Web\Controller {

	function get() {

		$atokens = array();

		if(local_channel()) {
			$at = q("select * from atoken where atoken_uid = %d",
				intval(local_channel())
			);
			if($at) {
				foreach($at as $t) {
					$atokens[] = array_merge($t, atoken_xchan($t));
				}
			}
		}

		$type = ((argc() > 1) ? argv(1) : 0);
		if (is_numeric($type)) {
			$item_id = intval($type);
			$type = 'item';
		}
		else {
			$item_id = ((argc() > 2) ? intval(argv(2)) : 0);
		}

		if(! $item_id)
			killme();

		if (! in_array($type, array('item', 'photo', 'attach', 'event', 'menu_item', 'chatroom')))
			killme();

		// we have different naming in in menu_item table and chatroom table
		switch($type) {
			case 'menu_item':
				$id = 'mitem_id';
				break;
			case 'chatroom':
				$id = 'cr_id';
				break;
			default:
				$id = 'id';
				break;
		}

		$r = q("SELECT * FROM %s WHERE $id = %d LIMIT 1",
			dbesc($type),
			intval($item_id)
		);

		if(! $r)
			killme();

		$item = $r[0];
		$uid = null;
		$url = '';

		switch($type) {
			case 'menu_item':
				$uid = $item['mitem_channel_id'];
				break;
			case 'chatroom':
				$uid = $item['cr_uid'];
				$channel = channelx_by_n($uid);
				$url = z_root() . '/chat/' . $channel['channel_address'] . '/' . $item['cr_id'];
				break;
			case 'item':
				$uid = $item['uid'];
				$url = $item['plink'];
				break;
			case 'attach':
				$uid = $item['uid'];
				$channel = channelx_by_n($uid);
				$url = z_root() . '/cloud/' . $channel['channel_address'] . '/' . $item['display_path'];
				break;
			default:
				break;
		}

		if($uid != local_channel()) {
			echo '<div class="dropdown-item-text">' . t('Remote privacy information not available') . '</div>';
			killme();
		}

		if(intval($item['item_private']) && (! strlen($item['allow_cid'])) && (! strlen($item['allow_gid']))
			&& (! strlen($item['deny_cid'])) && (! strlen($item['deny_gid']))) {

			// if the post is private, but public_policy is blank ("visible to the internet"), and there aren't any
			// specific recipients, we're the recipient of a post with "bcc" or targeted recipients; so we'll just show it
			// as unknown specific recipients. The sender will have the visibility list and will fall through to the
			// next section.

			echo '<div class="dropdown-item">' . translate_scope((! $item['public_policy']) ? 'specific' : $item['public_policy']) . '</div>';
			killme();
		}

		$allowed_users = expand_acl($item['allow_cid']);
		$allowed_groups = expand_acl($item['allow_gid']);
		$deny_users = expand_acl($item['deny_cid']);
		$deny_groups = expand_acl($item['deny_gid']);

		$o = '<div class="dropdown-item-text text-uppercase text-muted text-nowrap h6">' . t('Access') . '</div>';
		$l = array();

		stringify_array_elms($allowed_groups,true);
		stringify_array_elms($allowed_users,true);
		stringify_array_elms($deny_groups,true);
		stringify_array_elms($deny_users,true);

		$allowed_xchans = [];

		$profile_groups = [];
		if($allowed_groups) {
			foreach($allowed_groups as $g) {
				if(substr($g,0,4) === '\'vp.') {
					$profile_groups[] = '\'' . substr($g,4);
				}
			}
		}

		if(count($profile_groups)) {
			$r = q("SELECT profile_name FROM profile WHERE profile_guid IN ( " . implode(', ', $profile_groups) . " )");
			if($r) {
				foreach($r as $rr) {
					$l[] = '<div class="dropdown-item" title="' . t('Profile','acl') . '">' . $rr['profile_name'] . '</div>';
				}
			}
		}

		if(count($allowed_groups)) {
			$r = q("SELECT gname FROM pgrp WHERE hash IN ( " . implode(', ', $allowed_groups) . " )");
			if($r) {
				foreach($r as $rr) {
					$gid = AccessList::by_name($uid, $rr['gname']);
					$pgrp_members = AccessList::members_xchan($uid, $gid);
					$allowed_xchans = array_merge($allowed_xchans, $pgrp_members);

					$l[] = '<div class="dropdown-item" title="' . t('Privacy group') . '">' . $rr['gname'] . '</div>';
				}
			}
		}

		if(count($allowed_users)) {
			$r = q("SELECT xchan_name, xchan_hash FROM xchan WHERE xchan_hash IN ( " . implode(', ',$allowed_users) . " )");
			if($r) {
				foreach($r as $rr) {
					$allowed_xchans[] = $rr['xchan_hash'];
					$l[] = '<div class="dropdown-item">' . $rr['xchan_name'] . '</div>';
				}
			}
		}

		$profile_groups = [];
		if($deny_groups) {
			foreach($deny_groups as $g) {
				if(substr($g,0,4) === '\'vp.') {
					$profile_groups[] = '\'' . substr($g,4);
				}
			}
		}

		if(count($profile_groups)) {
			$r = q("SELECT profile_name FROM profile WHERE profile_guid IN ( " . implode(', ', $profile_groups) . " )");
			if($r)
				foreach($r as $rr)
					$l[] = '<div class="dropdown-item" title="' . t('Profile','acl') . '"><strike>' . $rr['profile_name'] . '</strike></b></div>';
		}

		if(count($deny_groups)) {
			$r = q("SELECT gname FROM pgrp WHERE hash IN ( " . implode(', ', $deny_groups) . " )");
			if($r)
				foreach($r as $rr)
					$l[] = '<div class="dropdown-item" title="' . t('Privacy group') .'"><strike>' . $rr['gname'] . '</strike></b></div>';
		}
		if(count($deny_users)) {
			$r = q("SELECT xchan_name FROM xchan WHERE xchan_hash IN ( " . implode(', ', $deny_users) . " )");
			if($r)
				foreach($r as $rr)
					$l[] = '<div class="dropdown-item"><strike>' . $rr['xchan_name'] . '</strike></div>';
		}

		if ($atokens && $allowed_xchans && $url) {
			$l[] = '<div class="dropdown-divider"></div>';
			$l[] = '<div class="dropdown-item-text text-uppercase text-muted text-nowrap h6">' . t('Guest access') . '</div>';

			$allowed_xchans = array_unique($allowed_xchans);
			foreach($atokens as $atoken) {
				if(in_array($atoken['xchan_hash'], $allowed_xchans)) {
					$l[] = '<div class="dropdown-item d-flex justify-content-between"><span>' . $atoken['xchan_name'] . '</span><i class="fa fa-copy p-1 cursor-pointer" title="' . sprintf(t('Click to copy link to this ressource for guest %s to clipboard'), $atoken['xchan_name']) . '" data-token="' . $url . '?zat=' . $atoken['atoken_token'] . '" onclick="navigator.clipboard.writeText(this.dataset.token); $.jGrowl(\'Copied\', { sticky: false, theme: \'info\', life: 500 });"></i></div>';
				}
			}
		}

		echo $o . implode($l);
		killme();

	}

}
