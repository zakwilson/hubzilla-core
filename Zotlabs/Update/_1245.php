<?php

namespace Zotlabs\Update;

class _1245 {

	function run() {

		if(ACTIVE_DBTYPE == DBTYPE_MYSQL) {
				return UPDATE_SUCCESS;
		}

		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			q("START TRANSACTION");

			$r = dbq("create index hubloc_hash on hubloc (hubloc_hash)");

			if($r) {
				q("COMMIT");
				return UPDATE_SUCCESS;
			}

			q("ROLLBACK");
			return UPDATE_FAILED;
		}

	}

}
