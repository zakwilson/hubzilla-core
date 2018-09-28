<?php

namespace Zotlabs\Update;

class _1221 {

	function run() {

		q("START TRANSACTION");

		$r1 = q("ALTER table " . TQUOT . 'groups' . TQUOT . " rename to pgrp ");
		$r2 = q("ALTER table " . TQUOT . 'group_member' . TQUOT . " rename to pgrp_member ");


		if($r1 && $r2) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
