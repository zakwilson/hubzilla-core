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
		$r2 = q("UPDATE hubloc
			LEFT JOIN xchan ON hubloc.hubloc_hash = xchan.xchan_hash
			SET hubloc.hubloc_id_url = xchan.xchan_url
			WHERE hubloc.hubloc_network IN ('activitypub', 'diaspora', 'friendica-over-diaspora', 'gnusoc')
			AND hubloc.hubloc_id_url = ''
			AND xchan.xchan_url != ''"
		);

		if($r1 && $r2) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
