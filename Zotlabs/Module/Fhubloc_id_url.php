<?php
namespace Zotlabs\Module;

/* fix missing or hubloc_id_url entries */

class Fhubloc_id_url extends \Zotlabs\Web\Controller {

	function get() {

		if(! is_site_admin())
			return;
	
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
				AND xchan_url = ''
				AND xchan_hash != ''"
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
				AND xchan_url = ''
				AND xchan_hash != ''"
			);

			$r3 = dbq("UPDATE hubloc                                                                                   
				SET hubloc_id_url = xchan_url
				FROM xchan
				WHERE hubloc_hash = xchan_hash
				AND hubloc_network IN ('activitypub', 'diaspora', 'friendica-over-diaspora', 'gnusoc')
				AND hubloc_id_url = ''"
			);


		}
	}
}
