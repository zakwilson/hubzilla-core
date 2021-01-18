<?php /** @file */

namespace Zotlabs\Daemon;

require_once('include/queue_fn.php');

class Deliver {

	static public function run($argc, $argv) {

		if ($argc < 2)
			return;

		logger('deliver: invoked: ' . print_r($argv, true), LOGGER_DATA);

		for ($x = 1; $x < $argc; $x++) {

			if (!$argv[$x])
				continue;

			$r = q("select * from outq where outq_hash = '%s'",
				dbesc($argv[$x])
			);

			if ($r) {
				queue_deliver($r[0], true);
			}

		}

	}

}
