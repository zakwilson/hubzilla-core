<?php

namespace Zotlabs\Update;

class _1238 {

	function run() {
	
		q("START TRANSACTION");

		$r = q("DELETE FROM app WHERE app_name = '%s'",
			dbesc('Premium Channel')
		);

		// completely remove broken xchan entries
		$r = dbq("DELETE FROM xchan WHERE xchan_hash = ''");

		// fix legacy zot hubloc_id_url
		$r1 = dbq("UPDATE hubloc
			SET hubloc_id_url = CONCAT(hubloc_url, '/channel/', SUBSTRING(hubloc_addr FROM 1 FOR POSITION('@' IN hubloc_addr) -1))
			WHERE hubloc_network = 'zot'
			AND hubloc_id_url = ''"
		);

		// fix singleton networks hubloc_id_url
		if(ACTIVE_DBTYPE == DBTYPE_MYSQL) {
			// fix entries for activitypub which miss the xchan_url due to an earlier bug
			$r2 = dbq("UPDATE xchan
				SET xchan_url = xchan_hash
				WHERE xchan_network = 'activitypub'
				AND xchan_url = ''"
			);

			$r3 = dbq("UPDATE hubloc
				LEFT JOIN xchan ON hubloc_hash = xchan_hash
				SET hubloc_id_url = xchan_url
				WHERE hubloc_network IN ('activitypub', 'diaspora', 'friendica-over-diaspora', 'gnusoc')
				AND hubloc_id_url = ''"
			);
		}
		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			// fix entries for activitypub which miss the xchan_url due to an earlier bug
			$r2 = dbq("UPDATE xchan
				SET xchan_url = xchan_hash
				WHERE xchan_network = 'activitypub'
				AND xchan_url = ''"
			);

			$r3 = dbq("UPDATE hubloc
				SET hubloc_id_url = xchan_url
				FROM xchan
				WHERE hubloc_hash = xchan_hash
				AND hubloc_network IN ('activitypub', 'diaspora', 'friendica-over-diaspora', 'gnusoc')
				AND hubloc_id_url = ''"
			);
		}

		if($r1 && $r2 && $r3) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
