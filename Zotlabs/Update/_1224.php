<?php

namespace Zotlabs\Update;

class _1224 {

	function run() {
		if(ACTIVE_DBTYPE == DBTYPE_MYSQL) {
			q("START TRANSACTION");

			$r1 = q("ALTER TABLE hubloc ALTER hubloc_id_url SET DEFAULT ''");
			$r2 = q("ALTER TABLE hubloc ALTER hubloc_site_id SET DEFAULT ''");

			if($r1 && $r2) {
				q("COMMIT");
				return UPDATE_SUCCESS;
			}

			q("ROLLBACK");
			return UPDATE_FAILED;
		}
		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			return UPDATE_SUCCESS;
		}

	}

}
