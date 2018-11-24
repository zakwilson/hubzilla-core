<?php

namespace Zotlabs\Update;


class _1227 {

	function run() {

		q("START TRANSACTION");

		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			$r = q("ALTER TABLE dreport ADD dreport_name text NOT NULL DEFAULT '' ");
		}
		else {
			$r = q("ALTER TABLE `dreport` ADD `dreport_name` char(191) NOT NULL DEFAULT ''"); 
		}

		if($r) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;
	}

}


