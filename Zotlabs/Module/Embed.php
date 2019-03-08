<?php
namespace Zotlabs\Module;

require_once('include/security.php');
require_once('include/bbcode.php');


class Embed extends \Zotlabs\Web\Controller {

	function init() {
	
		$post_id = ((argc() > 1) ? intval(argv(1)) : 0);
	
		if(! $post_id)
			killme();
	
		echo '[share=' . $post_id . '][/share]';
		killme();

	}
	
}
