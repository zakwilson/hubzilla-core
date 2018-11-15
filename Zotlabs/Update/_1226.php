<?php

namespace Zotlabs\Update;

use Zotlabs\Lib\Libzot;

class _1226 {

	function run() {

		q("START TRANSACTION");

		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			$r1 = q("ALTER TABLE channel ADD channel_portable_id text NOT NULL DEFAULT '' ");
 			$r2 = q("create index \"channel_portable_id_idx\" on channel (\"channel_portable_id\")");

			$r = ($r1 && $r2);
		}
		else {
			$r = q("ALTER TABLE `channel` ADD `channel_portable_id` char(191) NOT NULL DEFAULT '' , 
				ADD INDEX `channel_portable_id` (`channel_portable_id`)");
		}

		if($r) {
			q("COMMIT");
			self::upgrade();
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;
	}


	static function upgrade() {

		$r = q("select * from channel where channel_portable_id = '' ");

		if($r) {
			foreach($r as $rv) {

				$zhash = Libzot::make_xchan_hash($rv['channel_guid'],$rv['channel_pubkey']);
				q("update channel set channel_portable_id = '%s' where channel_id = %d",
					dbesc($zhash),
					intval($rv['channel_id'])
				);
				$x = q("select * from xchan where xchan_hash = '%s' limit 1",
					dbesc($rv['channel_hash'])
				);
				if($x) {
					$rec = $x[0];
					$rec['xchan_hash'] = $zhash;
					$rec['xchan_guid_sig'] = 'sha256.' . $rec['xchan_guid_sig'];
					$rec['xchan_network'] = 'zot6';
	
					xchan_store_lowlevel($rec);
				}
				$x = q("select * from hubloc where hubloc_hash = '%s' and hubloc_url = '%s' limit 1",
					dbesc($rv['channel_hash']),
					dbesc(z_root())
				);
				if($x) {
					$rec = $x[0];
					$rec['hubloc_hash'] = $zhash;
					$rec['hubloc_guid_sig'] = 'sha256.' . $rec['hubloc_guid_sig'];
					$rec['hubloc_network'] = 'zot6';
					$rec['hubloc_url_sig'] = 'sha256.' . $rec['hubloc_url_sig'];
					$rec['hubloc_callback'] = z_root() . '/zot';
					$rec['hubloc_id_url'] = channel_url($rv);
					$rec['hubloc_site_id'] = Libzot::make_xchan_hash(z_root(),get_config('system','pubkey'));
					hubloc_store_lowlevel($rec);
				}
			}
		}
	}
}


