<?php
namespace Zotlabs\Module;

/*
 * Pinned post processing
 */

use App;

class Pin extends \Zotlabs\Web\Controller {


	function init() {

		if(argc() !== 1)
			http_status_exit(400, 'Bad request');

		if(! local_channel())
			http_status_exit(403, 'Forbidden');
	}


	function post() {

		$item_id = intval($_POST['id']);

		if ($item_id <= 0)
			http_status_exit(404, 'Not found');

		$channel = \App::get_channel();

		$r = q("SELECT * FROM item WHERE id = %d AND id = parent AND uid = %d AND owner_xchan = '%s' AND item_private = 0 LIMIT 1",
			$item_id,
			intval($channel['channel_id']),
			dbesc($channel['xchan_hash'])
		);
		if(!$r) {
			notice(t('Unable to locate original post.'));
			http_status_exit(404, 'Not found');
		}
		else {
			// Currently allow only one pinned item for each type
			$midb64 = 'b64.' . base64url_encode($r[0]['mid']);
			$pinned = (in_array($midb64, get_pconfig($channel['channel_id'], 'pinned', $r[0]['item_type'], [])) ? [] : [ $midb64 ]);
			set_pconfig($channel['channel_id'], 'pinned', $r[0]['item_type'], $pinned);
			
			build_sync_packet($channel['channel_id'], [ 'config' ]);
		}
	}
}
