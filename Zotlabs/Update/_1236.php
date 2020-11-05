<?php

namespace Zotlabs\Update;

use Zotlabs\Lib\Libzot;

class _1236 {

	function run() {
	
		$r = q("SELECT channel.channel_address, channel.channel_hash, xchan.xchan_guid, channel.channel_pubkey, channel.channel_portable_id FROM channel 
			LEFT JOIN xchan ON channel_hash = xchan_hash 
			WHERE xchan.xchan_network = 'zot' 
			AND channel.channel_removed = 0"
		);

		$i = 0;

		foreach($r as $rr) {

			$zot_xchan = $rr['channel_hash'];
			$guid = $rr['xchan_guid'];

			$xchan = q("SELECT xchan_hash, xchan_guid_sig FROM xchan WHERE xchan_guid = '%s' AND xchan_network = 'zot6'",
				dbesc($guid)
			);

			if(!$xchan) {
				// This should not actually happen.
				// A zot6 xchan for every channel should have been
				// created in update _1226.

				// In case this failed, we will try to fix it here.
				logger('No zot6 xchan found for: ' . $rr['channel_hash']);

				$zhash = $rr['channel_portable_id'];

				if(!$zhash) {
					$zhash = Libzot::make_xchan_hash($rr['xchan_guid'], $rr['channel_pubkey']);

					q("UPDATE channel SET channel_portable_id = '%s' WHERE channel_hash = '%s'",
						dbesc($zhash),
						dbesc($zot_xchan)
					);
				}

				if(!$zhash) {
					logger('Could not create zot6 xchan_hash for: ' . $rr['channel_hash']);
					continue;
				}

				$x = q("SELECT * FROM xchan WHERE xchan_hash = '%s' LIMIT 1",
					dbesc($rr['channel_hash'])
				);

				if($x) {
					$rec = $x[0];
					$rec['xchan_hash'] = $zhash;
					$rec['xchan_guid_sig'] = 'sha256.' . $rec['xchan_guid_sig'];
					$rec['xchan_network'] = 'zot6';
					xchan_store_lowlevel($rec);
				}

				// Now try again
				$xchan = q("SELECT xchan_hash, xchan_guid_sig FROM xchan WHERE xchan_guid = '%s' AND xchan_network = 'zot6'",
					dbesc($guid)
				);

				if(! $xchan) {
					logger('Could not create zot6 xchan record for: ' . $zot_xchan);
					continue;
				}

			}

			$zot6_xchan = $xchan[0]['xchan_hash'];
			$zot6_xchan_guid_sig = $xchan[0]['xchan_guid_sig'];

			$hubloc = q("SELECT hubloc_hash FROM hubloc WHERE hubloc_guid = '%s' AND hubloc_url = '%s' AND hubloc_network = 'zot6'",
				dbesc($guid),
				dbesc(z_root())
			);

			if(! $hubloc) {
				// This should not actually happen.
				// A local zot6 hubloc for every channel should have been
				// created in update _1226.

				// In case this failed, we will try to fix it here.
				logger('No local zot6 hubloc found for: ' . $rr['channel_hash']);

				$h = q("SELECT * FROM hubloc WHERE hubloc_hash = '%s' AND hubloc_url = '%s' LIMIT 1",
					dbesc($zot_xchan),
					dbesc(z_root())
				);

				if($h) {
					$rec = $h[0];
					$rec['hubloc_hash'] = $zot6_xchan;
					$rec['hubloc_guid_sig'] = 'sha256.' . $rec['hubloc_guid_sig'];
					$rec['hubloc_network'] = 'zot6';
					$rec['hubloc_url_sig'] = 'sha256.' . $rec['hubloc_url_sig'];
					$rec['hubloc_callback'] = z_root() . '/zot';
					$rec['hubloc_id_url'] = channel_url($rr);
					$rec['hubloc_site_id'] = Libzot::make_xchan_hash(z_root(),get_config('system','pubkey'));

					$hubloc = hubloc_store_lowlevel($rec);
				}

				if(! $hubloc) {
					logger('Could not create local zot6 hubloc record for: ' . $zot_xchan);
					continue;
				}
			}

			logger('Transforming channel: ' . $zot_xchan);
			q("UPDATE channel SET channel_hash = '%s', channel_portable_id = '%s', channel_guid_sig = '%s' WHERE channel_hash = '%s'",
				dbesc($zot6_xchan),
				dbesc($zot_xchan),
				dbesc($zot6_xchan_guid_sig),
				dbesc($zot_xchan)
			);

			$i++;

		}

		if(count($r) == $i) {
			z6trans_connections();
			return UPDATE_SUCCESS;
		}

		return UPDATE_FAILED;

	}

}
