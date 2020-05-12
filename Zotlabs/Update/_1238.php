<?php

namespace Zotlabs\Update;

class _1238 {

	function run() {
	
		q("START TRANSACTION");

		$r = q("DELETE FROM app WHERE app_name = '%s'",
			dbesc('Premium Channel')
		);

		// fix legacy zot hubloc_id_url
		$r1 = q("UPDATE hubloc
			SET hubloc_id_url = CONCAT(hubloc_url, '/channel/', SUBSTRING(hubloc_addr FROM 1 FOR POSITION('@' IN hubloc_addr) -1))
			WHERE hubloc_network = 'zot'
			AND hubloc_id_url = ''"
		);

		// fix singleton networks hubloc_id_url
		if(ACTIVE_DBTYPE == DBTYPE_MYSQL) {
			$r2 = q("UPDATE hubloc
				LEFT JOIN xchan ON hubloc_hash = xchan_hash
				SET hubloc_id_url = xchan_url
				WHERE hubloc_network IN ('activitypub', 'diaspora', 'friendica-over-diaspora', 'gnusoc')
				AND hubloc_id_url = ''
				AND xchan_url != ''"
			);
		}
		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			$r2 = q("UPDATE hubloc                                                                                   
				SET hubloc_id_url = xchan_url
				FROM xchan
				WHERE hubloc_hash = xchan_hash
				AND hubloc_network IN ('activitypub', 'diaspora', 'friendica-over-diaspora', 'gnusoc')
				AND hubloc_id_url = ''
				AND xchan_url != ''"
			);
		}

		if($r1 && $r2) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
