<?php

namespace Zotlabs\Update;

class _1240 {

	function run() {
	
		q("START TRANSACTION");

		// remove broken xchan entries
		$r0 = dbq("DELETE FROM xchan WHERE xchan_hash = ''");

		// remove broken hubloc entries
		$r1 = dbq("DELETE FROM hubloc WHERE hubloc_hash = ''");

		// fix legacy zot hubloc_id_url 
		$r2 = dbq("UPDATE hubloc
			SET hubloc_id_url = CONCAT(hubloc_url, '/channel/', SUBSTRING(hubloc_addr FROM 1 FOR POSITION('@' IN hubloc_addr) -1))
			WHERE hubloc_network = 'zot'
			AND hubloc_id_url = ''"
		);

		if($r0 && $r1 && $r2) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
