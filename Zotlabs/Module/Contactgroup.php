<?php
namespace Zotlabs\Module;

use Zotlabs\Lib\AccessList;
use Zotlabs\Web\Controller;

class Contactgroup extends Controller {

	function get() {

		if(! local_channel()) {
			killme();
		}

		if((argc() > 2) && (intval(argv(1))) && (argv(2))) {
			$r = q("SELECT abook_xchan from abook where abook_xchan = '%s' and abook_channel = %d and abook_self = 0 limit 1",
				dbesc(base64url_decode(argv(2))),
				intval(local_channel())
			);
			if($r)
				$change = $r[0]['abook_xchan'];
		}

		if((argc() > 1) && (intval(argv(1)))) {

			$r = q("SELECT * FROM pgrp WHERE id = %d AND uid = %d AND deleted = 0 LIMIT 1",
				intval(argv(1)),
				intval(local_channel())
			);
			if(! $r) {
				killme();
			}

			$group = $r[0];
			$members = AccessList::members(local_channel(), $group['id']);
			$preselected = array();
			if(count($members))	{
				foreach($members as $member)
					$preselected[] = $member['xchan_hash'];
			}

			if($change) {
				if(in_array($change,$preselected)) {
					AccessList::member_remove(local_channel(),$group['gname'],$change);
				}
				else {
					AccessList::member_add(local_channel(),$group['gname'],$change);
				}
			}
		}

		killme();
	}
}
