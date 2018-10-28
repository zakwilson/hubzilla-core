<?php

namespace Zotlabs\Update;

class _1225 {

	function run() {

		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			$r1 = q("ALTER TABLE pconfig ADD updated timestamp NOT NULL DEFAULT '0001-01-01 00:00:00' ");
 			$r2 = q("create index \"pconfig_updated_idx\" on pconfig (\"updated\")");

			$r = ($r1 && $r2);
		}
		else {
			$r = q("ALTER TABLE `pconfig` ADD `updated` datetime NOT NULL DEFAULT '0001-01-01 00:00:00' , 
				ADD INDEX `pconfig_updated` (`updated`)");
		}

		if($r)
			return UPDATE_SUCCESS;
		return UPDATE_FAILED;

	}

}
