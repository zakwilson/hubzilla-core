<?php

namespace Zotlabs\Update;

class _1232 {

	function run() {
	
		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			return UPDATE_SUCCESS;
		}
		else {
			q("START TRANSACTION");

			$r = q("ALTER TABLE channel
				DROP channel_r_stream,
				DROP channel_r_profile,
				DROP channel_r_photos,
				DROP channel_r_abook,
				DROP channel_w_stream,
				DROP channel_w_wall,
				DROP channel_w_tagwall,
				DROP channel_w_comment,
				DROP channel_w_mail,
				DROP channel_w_photos,
				DROP channel_w_chat,
				DROP channel_a_delegate,
				DROP channel_r_storage,
				DROP channel_w_storage,
				DROP channel_r_pages,
				DROP channel_w_pages,
				DROP channel_a_republish,
				DROP channel_w_like"
			);
		}

		if($r) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}
