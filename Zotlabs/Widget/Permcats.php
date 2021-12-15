<?php

namespace Zotlabs\Widget;

use Zotlabs\Lib\Permcat;
use Zotlabs\Access\PermissionLimits;

class Permcats {

	function widget($arr) {
		$pcat = new Permcat(local_channel());
		$pcatlist = $pcat->listing();

		$list = '<b>Roles:</b><br>';
		$active = '';
		$active_role = '';

		if($pcatlist) {
			$i = 0;
			foreach($pcatlist as $pc) {
				if(argc() > 1) {
					if($pc['name'] == hex2bin(argv(1))) {
						$active = $i;
						$active_role = $pc['name'];
					}
				}

				$list .= '<a href="permcats/' . bin2hex($pc['name']) . '">' . $pc['localname'] . '</a><br>';
				$i++;
			}
		}

		if(argc() > 1) {

/* get role members based on permissions
			$test = $pcatlist[$active]['perms'];

			$role_sql = '';
			$count = 0;
			foreach ($test as $t) {
				$checkinherited = PermissionLimits::Get(local_channel(),$t['name']);

				if($checkinherited & PERMS_SPECIFIC) {
					$role_sql .= "( abconfig.k = '" . dbesc($t['name']) . "' AND abconfig.v = '" . intval($t['value']) . "' ) OR ";
					$count++;
				}
			}

			$role_sql = rtrim($role_sql, ' OR ');

			$r = q("SELECT abconfig.xchan, xchan.xchan_name, abook.abook_id FROM abconfig LEFT JOIN xchan on abconfig.xchan = xchan.xchan_hash LEFT JOIN abook ON abconfig.xchan = abook.abook_xchan WHERE xchan.xchan_deleted = 0 and abconfig.chan = %d AND abconfig.cat = 'my_perms' AND ( $role_sql ) GROUP BY abconfig.xchan HAVING count(abconfig.xchan) = %d ORDER BY xchan.xchan_name",
				intval(local_channel()),
				intval($count)
			);
*/

			// get role members based on abook_role

			$r = q("SELECT abook.abook_id, abook.abook_role, xchan.xchan_name, xchan.xchan_addr, xchan.xchan_url, xchan.xchan_photo_s FROM abook
				LEFT JOIN xchan on abook.abook_xchan = xchan.xchan_hash
				WHERE abook.abook_channel = %d AND abook.abook_role = '%s' AND abook_self = 0 AND xchan_deleted = 0
				ORDER BY xchan.xchan_name",
				intval(local_channel()),
				dbesc($active_role)
			);

			$members = '<b>Role members:</b><br>';
			$members .= '<div class="border rounded" style="height: 20rem; overflow: auto;">';

			foreach ($r as $rr) {
				$addr = (($rr['xchan_addr']) ? $rr['xchan_addr'] : $rr['xchan_url']);
				$members .= '<a href="connections#' . $rr['abook_id'] . '" class="lh-sm border-bottom p-2 d-block text-truncate"><img src="' . $rr['xchan_photo_s'] . '" class="float-start rounded me-2" style="height: 2.2rem; width: 2.2rem;" loading="lazy">' . $rr['xchan_name'] . '<br><span class="text-muted small">' . $addr . '</span></a>';
			}
			$members .= '</div>';
		}
		return $list . '<br>' . $members. '<br>' . $others;

	}
}
