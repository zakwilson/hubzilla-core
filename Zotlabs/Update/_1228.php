<?php

namespace Zotlabs\Update;


class _1228 {

	function run() {

		q("START TRANSACTION");

		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			$r1 = q("ALTER TABLE item ADD uuid text NOT NULL DEFAULT '' ");
			$r2 = q("create index \"uuid_idx\" on item (\"uuid\")");
			$r3 = q("ALTER TABLE item add summary TEXT NOT NULL DEFAULT ''");

			$r = ($r1 && $r2 && $r3);
		}
		else {
			$r1 = q("ALTER TABLE `item` ADD `uuid` char(191) NOT NULL DEFAULT '' , 
				ADD INDEX `uuid` (`uuid`)");
			$r2 = q("ALTER TABLE `item` ADD `summary` mediumtext NOT NULL");
			$r = ($r1 && $r2);
		}

		if($r) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;
	}

}

