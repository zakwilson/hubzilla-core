<?php

namespace Zotlabs\Lib;

class AccessList {

	static function add($uid, $name, $public = 0) {

		$ret  = false;
		$hash = '';
		if ($uid && $name) {
			$r = self::by_name($uid, $name); // check for dups
			if ($r !== false) {

				// This could be a problem.
				// Let's assume we've just created a list which we once deleted
				// all the old members are gone, but the list remains so we don't break any security
				// access lists. What we're doing here is reviving the dead list, but old content which
				// was restricted to this list may now be seen by the new list members.

				$z = q("SELECT * FROM pgrp WHERE id = %d LIMIT 1",
					intval($r)
				);
				if (($z) && $z[0]['deleted']) {
					q('UPDATE pgrp SET deleted = 0 WHERE id = %d', intval($z[0]['id']));
					notice(t('A deleted privacy group with this name was revived. Existing item permissions <strong>may</strong> apply to this privacy group and any future members. If this is not what you intended, please create another privacy group with a different name.') . EOL);
				}
				$hash = self::by_id($uid, $r);
				return $hash;
			}

			$hash = new_uuid();

			$r   = q("INSERT INTO pgrp ( hash, uid, visible, gname )
				VALUES( '%s', %d, %d, '%s' ) ",
				dbesc($hash),
				intval($uid),
				intval($public),
				dbesc($name)
			);
			$ret = $r;
		}

		Libsync::build_sync_packet($uid, null, true);

		return (($ret) ? $hash : $ret);
	}

	static function remove($uid, $name) {
		$ret = false;
		if ($uid && $name) {
			$r = q("SELECT id, hash FROM pgrp WHERE uid = %d AND gname = '%s' LIMIT 1",
				intval($uid),
				dbesc($name)
			);
			if ($r) {
				$group_id   = $r[0]['id'];
				$group_hash = $r[0]['hash'];
			}
			else {
				return false;
			}

			// remove group from default posting lists
			$r = q("SELECT channel_default_group, channel_allow_gid, channel_deny_gid FROM channel WHERE channel_id = %d LIMIT 1",
				intval($uid)
			);
			if ($r) {
				$user_info = array_shift($r);
				$change    = false;

				if ($user_info['channel_default_group'] == $group_hash) {
					$user_info['channel_default_group'] = '';
					$change                             = true;
				}
				if (strpos($user_info['channel_allow_gid'], '<' . $group_hash . '>') !== false) {
					$user_info['channel_allow_gid'] = str_replace('<' . $group_hash . '>', '', $user_info['channel_allow_gid']);
					$change                         = true;
				}
				if (strpos($user_info['channel_deny_gid'], '<' . $group_hash . '>') !== false) {
					$user_info['channel_deny_gid'] = str_replace('<' . $group_hash . '>', '', $user_info['channel_deny_gid']);
					$change                        = true;
				}

				if ($change) {
					q("UPDATE channel SET channel_default_group = '%s', channel_allow_gid = '%s', channel_deny_gid = '%s'
						WHERE channel_id = %d",
						intval($user_info['channel_default_group']),
						dbesc($user_info['channel_allow_gid']),
						dbesc($user_info['channel_deny_gid']),
						intval($uid)
					);
				}
			}

			// remove all members
			$r = q("DELETE FROM pgrp_member WHERE uid = %d AND gid = %d ",
				intval($uid),
				intval($group_id)
			);

			// remove group
			$r = q("UPDATE pgrp SET deleted = 1 WHERE uid = %d AND gname = '%s'",
				intval($uid),
				dbesc($name)
			);

			$ret = $r;

		}

		Libsync::build_sync_packet($uid, null, true);

		return $ret;
	}

	// returns the integer id of an access group owned by $uid and named $name
	// or false.

	static function by_name($uid, $name) {
		if (!($uid && $name)) {
			return false;
		}
		$r = q("SELECT id FROM pgrp WHERE uid = %d AND gname = '%s' LIMIT 1",
			intval($uid),
			dbesc($name)
		);
		if ($r) {
			return $r[0]['id'];
		}
		return false;
	}

	static function by_id($uid, $id) {
		if (!($uid && $id)) {
			return false;
		}

		$r = q("SELECT * FROM pgrp WHERE uid = %d AND id = %d and deleted = 0",
			intval($uid),
			intval($id)
		);
		if ($r) {
			return array_shift($r);
		}
		return false;
	}

	static function by_hash($uid, $hash) {
		if (!($uid && $hash)) {
			return false;
		}
		$r = q("SELECT * FROM pgrp WHERE uid = %d AND hash = '%s' LIMIT 1",
			intval($uid),
			dbesc($hash)
		);
		if ($r) {
			return array_shift($r);
		}
		return false;
	}

	static function member_remove($uid, $name, $member, $gid = 0) {
		if (!$gid) {
			$gid = self::by_name($uid, $name);
		}

		if (!($uid && $gid && $member)) {
			return false;
		}

		$r = q("DELETE FROM pgrp_member WHERE uid = %d AND gid = %d AND xchan = '%s' ",
			intval($uid),
			intval($gid),
			dbesc($member)
		);

		Libsync::build_sync_packet($uid, null, true);

		return $r;
	}

	static function member_add($uid, $name, $member, $gid = 0) {
		if (!$gid) {
			$gid = self::by_name($uid, $name);
		}
		if (!($gid && $uid && $member)) {
			return false;
		}

		$r = q("SELECT * FROM pgrp_member WHERE uid = %d AND gid = %d AND xchan = '%s' LIMIT 1",
			intval($uid),
			intval($gid),
			dbesc($member)
		);
		if ($r) {
			return true;
			// You might question this, but
			// we indicate success because the group member was in fact created
			// -- It was just created at another time
		}
		else {
			$r = q("INSERT INTO pgrp_member (uid, gid, xchan)
				VALUES( %d, %d, '%s' ) ",
				intval($uid),
				intval($gid),
				dbesc($member)
			);
		}
		Libsync::build_sync_packet($uid, null, true);
		return $r;
	}

	static function members($uid, $gid) {
		$ret = [];
		if (intval($gid)) {
			$r = q("SELECT * FROM pgrp_member
				LEFT JOIN abook ON abook_xchan = pgrp_member.xchan left join xchan on xchan_hash = abook_xchan
				WHERE gid = %d AND abook_channel = %d and pgrp_member.uid = %d and xchan_deleted = 0 and abook_self = 0 and abook_blocked = 0 and abook_pending = 0 ORDER BY xchan_name ASC ",
				intval($gid),
				intval($uid),
				intval($uid)
			);
			if ($r) {
				$ret = $r;
			}
		}
		return $ret;
	}

	static function members_xchan($uid, $gid) {
		$ret = [];
		if (intval($gid)) {
			$r = q("SELECT xchan FROM pgrp_member WHERE gid = %d AND uid = %d",
				intval($gid),
				intval($uid)
			);
			if ($r) {
				foreach ($r as $rv) {
					$ret[] = $rv['xchan'];
				}
			}
		}
		return $ret;
	}

	static function profile_members_xchan($uid,$gid) {
		$ret = [];

		if(intval($gid)) {
			$r = q("SELECT abook_xchan as xchan from abook left join profile on abook_profile = profile_guid where profile.id = %d and profile.uid = %d",
				intval($gid),
				intval($uid)
			);
			if($r) {
				foreach($r as $rr) {
					$ret[] = $rr['xchan'];
				}
			}
		}
		return $ret;
	}

	static function select($uid, $options) {

		$selected = $options['selected'] ?? '';
		$form_id = $options['form_id'] ?? 'accesslist_select';
		$label = $options['label'] ?? t('Select a privacy group');
		$before = $options['before'] ?? [];
		$after = $options['after'] ?? [];

		$grps = [];
		$o = '';

		$grps[] = [
			'name' => '',
			'id' => '0',
			'selected' => false
		];

		if ($before) {
			$grps[] = $before;
		}

		$r = q("SELECT * FROM pgrp WHERE deleted = 0 AND uid = %d ORDER BY gname ASC",
			intval($uid)
		);

		if($r) {
			foreach($r as $rr) {
				$grps[] = [
					'name' => $rr['gname'],
					'id' => $rr['hash'],
					'selected' => ($selected == $rr['hash'])
				];
			}
		}

		if ($after) {
			$grps[] = $after;
		}

		logger('select: ' . print_r($grps,true), LOGGER_DATA);

		$o = replace_macros(get_markup_template('group_selection.tpl'), array(
			'$label' => $label,
			'$form_id' => $form_id,
			'$groups' => $grps
		));

		return $o;
	}

	static function expand($g) {
		if (!(is_array($g) && count($g))) {
			return [];
		}

		$ret = [];
		$x   = [];

		// private profile linked virtual groups

		foreach ($g as $gv) {
			if (substr($gv, 0, 3) === 'vp.') {
				$profile_hash = substr($gv, 3);
				if ($profile_hash) {
					$r = q("select abook_xchan from abook where abook_profile = '%s'",
						dbesc($profile_hash)
					);
					if ($r) {
						foreach ($r as $rv) {
							$ret[] = $rv['abook_xchan'];
						}
					}
				}
			}
			else {
				$x[] = $gv;
			}
		}

		if ($x) {
			stringify_array_elms($x, true);
			$groups = implode(',', $x);
			if ($groups) {
				$r = q("SELECT xchan FROM pgrp_member WHERE gid IN ( select id from pgrp where hash in ( $groups ))");
				if ($r) {
					foreach ($r as $rv) {
						$ret[] = $rv['xchan'];
					}
				}
			}
		}
		return $ret;
	}

	static function member_of($c) {
		$r = q("SELECT pgrp.gname, pgrp.id FROM pgrp LEFT JOIN pgrp_member ON pgrp_member.gid = pgrp.id
			WHERE pgrp_member.xchan = '%s' AND pgrp.deleted = 0 ORDER BY pgrp.gname  ASC ",
			dbesc($c)
		);

		return $r;
	}

	static function containing($uid, $c) {

		$r = q("SELECT gid FROM pgrp_member WHERE uid = %d AND pgrp_member.xchan = '%s' ",
			intval($uid),
			dbesc($c)
		);

		$ret = [];
		if ($r) {
			foreach ($r as $rv)
				$ret[] = $rv['gid'];
		}

		return $ret;
	}

}
