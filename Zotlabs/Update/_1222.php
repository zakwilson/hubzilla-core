<?php

namespace Zotlabs\Update;

class _1222 {

	function run() {

		q("START TRANSACTION");

		$r1 = q("DELETE FROM app WHERE app_name = 'Grid' and app_system = 1");

		if($r1) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
