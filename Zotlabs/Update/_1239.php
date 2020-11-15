<?php

namespace Zotlabs\Update;

class _1239 {

	function run() {
	
		dbq("START TRANSACTION");

		// remove broken activitypub hubloc entries
		$r = dbq("DELETE FROM hubloc WHERE hubloc_network = 'activitypub' and hubloc_callback = ''");

		// remove broken hubloc entries from friendica
		$r1 = dbq("DELETE FROM hubloc WHERE hubloc_hash = ''");

		if($r && $r1) {
			dbq("COMMIT");
			return UPDATE_SUCCESS;
		}

		dbq("ROLLBACK");
		return UPDATE_FAILED;

	}

}
