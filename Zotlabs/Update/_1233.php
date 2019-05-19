<?php

namespace Zotlabs\Update;

class _1233 {

	function run() {
	
		q("START TRANSACTION");

		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			$r1 = q("DROP INDEX item_uid_mid");

			$r2 = q("create index item_uid_mid on item (uid, mid)");
			$r3 = q("create index xchan_photo_m on xchan (xchan_photo_m)");
			
			$r = ($r1 && $r2 && $r3);
		}
		else {
			$r1 = q("ALTER TABLE item DROP INDEX uid_mid");

			$r2 = q("ALTER TABLE item ADD INDEX uid_mid (uid, mid)");
			$r3 = q("ALTER TABLE xchan ADD INDEX xchan_photo_m (xchan_photo_m)");
			
			$r = ($r1 && $r2 && $r3);
		}

		if($r) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
