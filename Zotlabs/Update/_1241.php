<?php

namespace Zotlabs\Update;

class _1241 {

	function run() {
	
		q("START TRANSACTION");

		// remove duplicated profile photos
		$r = dbq("DELETE FROM photo WHERE imgscale IN (4, 5, 6) AND photo_usage = 0");

		if($r) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
