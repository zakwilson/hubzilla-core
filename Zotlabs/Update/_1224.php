<?php

namespace Zotlabs\Update;

class _1224 {

	function run() {
		q("START TRANSACTION");

		if(ACTIVE_DBTYPE == DBTYPE_MYSQL) {
			$r1 = q("ALTER TABLE hubloc ALTER hubloc_id_url SET DEFAULT ''");
			$r2 = q("ALTER TABLE hubloc ALTER hubloc_site_id SET DEFAULT ''");
		}

		if($r1 && $r2) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
