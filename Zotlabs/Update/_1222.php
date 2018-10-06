<?php

namespace Zotlabs\Update;

use Zotlabs\Lib\Apps;

class _1222 {

	function run() {

		q("START TRANSACTION");

		$r1 = q("DELETE FROM app WHERE app_name = 'Grid' and app_system = 1");

		if($r1) {
			q("COMMIT");

			Apps::import_system_apps();

			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
