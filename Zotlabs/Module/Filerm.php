<?php
namespace Zotlabs\Module;


class Filerm extends \Zotlabs\Web\Controller {

	function get() {
	
		if(! local_channel()) {
			killme();
		}
	
		$term = trim($_GET['term']);
		$cat  = trim($_GET['cat']);
	
		$category = (($cat) ? true : false);
		if($category)
			$term = $cat;
	
		$item_id = ((\App::$argc > 1) ? intval(\App::$argv[1]) : 0);
	
		logger('filerm: tag ' . $term . ' item ' . $item_id);
	
		if($item_id && strlen($term)) {
			$r = q("delete from term where uid = %d and ttype = %d and oid = %d and term = '%s'",
				intval(local_channel()),
				intval(($category) ? TERM_CATEGORY : TERM_FILE),
				intval($item_id),
				dbesc($term)
			);

			$x = q("update item set item_retained = 0, changed = '%s' where id = %d and uid = %d",
				dbesc(datetime_convert()),
				intval($item_id),
				intval(local_channel())
			);

		}
		
		killme();
	}
	
}
