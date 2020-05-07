<?php

namespace Zotlabs\Update;

class _1237 {

	function run() {
	
		q("START TRANSACTION");

		$r = q("DELETE FROM app WHERE app_name = '%s'",
			dbesc('Premium Channel')
		);

		if($r) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
