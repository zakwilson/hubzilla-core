<?php

namespace Zotlabs\Widget;

use App;

class Profile {
	function widget($args) {
		if(!App::$profile['profile_uid']) {
			return;
		}

		$block = observer_prohibited();

		return profile_sidebar(App::$profile, $block, true, false);
	}
}
