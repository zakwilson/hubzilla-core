<?php

namespace Zotlabs\Update;

class _1234 {

	function run() {
	
		q("START TRANSACTION");

		$r = q("DELETE FROM app WHERE app_name = '%s' OR app_name = '%s'",
			dbesc('Events'),
			dbesc('CalDAV')
		);

		if($r) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
