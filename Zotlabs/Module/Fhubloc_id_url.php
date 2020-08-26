<?php
namespace Zotlabs\Module;

/* fix missing or hubloc_id_url entries */

class Fhubloc_id_url extends \Zotlabs\Web\Controller {

	function get() {

		if(! is_site_admin())
			return;

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

		// fix singleton networks hubloc_id_url
		if(ACTIVE_DBTYPE == DBTYPE_MYSQL) {
			// fix entries for activitypub which miss the xchan_url due to an earlier bug
			$r3 = dbq("UPDATE xchan
				SET xchan_url = xchan_hash
				WHERE xchan_network = 'activitypub'
				AND xchan_url = ''"
			);

			$r4 = dbq("UPDATE hubloc
				LEFT JOIN xchan ON hubloc.hubloc_hash = xchan.xchan_hash
				SET hubloc.hubloc_id_url = xchan.xchan_url
				WHERE hubloc.hubloc_network IN ('activitypub', 'diaspora', 'friendica-over-diaspora', 'gnusoc')
				AND hubloc.hubloc_id_url = ''
				AND xchan.xchan_url IS NOT NULL"
			);

		}
		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			// fix entries for activitypub which miss the xchan_url due to an earlier bug
			$r3 = dbq("UPDATE xchan
				SET xchan_url = xchan_hash
				WHERE xchan_network = 'activitypub'
				AND xchan_url = ''"
			);

			$r4 = dbq("UPDATE hubloc
				SET hubloc_id_url = xchan_url
				FROM xchan
				WHERE hubloc_hash = xchan_hash
				AND hubloc_network IN ('activitypub', 'diaspora', 'friendica-over-diaspora', 'gnusoc')
				AND hubloc_id_url = ''
				AND xchan_url IS NOT NULL"
			);


		}

		if($r0 && $r1 && $r2 && $r3 && $r4) {
			// remove hubloc entries where hubloc_id_url could not be fixed
			$r5 = dbq("DELETE FROM hubloc WHERE hubloc_id_url = ''");
		}

		if($r0 && $r1 && $r2 && $r3 && $r4 && $r5) {
			q("COMMIT");
			return 'Completed';
		}

		q("ROLLBACK");
		return 'Failed';
	}
}
