<?php

namespace Zotlabs\Update;

class _1243 {

	function run() {
		
		$x = get_config('system','filesystem_storage_thumbnails');
		del_config('system','filesystem_storage_thumbnails');
		if ($x !== false)
			set_config('system','photo_storage_type', intval($x));
			
		return UPDATE_SUCCESS;
	}

}
