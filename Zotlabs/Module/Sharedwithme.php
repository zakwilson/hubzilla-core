<?php
namespace Zotlabs\Module;

use Zotlabs\Web\Controller;

require_once('include/conversation.php');
require_once('include/text.php');


/**
 * @file Zotlabs/Module/Sharedwithme.php
 *
 */

class Sharedwithme extends Controller {

	function get() {
		if(! local_channel()) {
			notice( t('Permission denied.') . EOL);
			return;
		}
		
		$channel = \App::get_channel();
	
		$is_owner = (local_channel() && (local_channel() == $channel['channel_id']));

		$item_normal = item_normal();
	
		//drop single file - localuser
		if((argc() > 2) && (argv(2) === 'drop')) {

			$id = intval(argv(1));

			drop_item($id);

			goaway(z_root() . '/sharedwithme');

		}
	
		//drop all files - localuser
		if((argc() > 1) && (argv(1) === 'dropall')) {

			$r = q("SELECT id FROM item WHERE verb = '%s' AND obj_type IN ('Document', 'Video', 'Audio', 'Image') AND uid = %d AND owner_xchan != '%s' $item_normal",
				dbesc(ACTIVITY_POST),
				intval(local_channel()),
				dbesc($channel['channel_hash'])
			);

			$ids = ids_to_array($r);

			if($ids)
				drop_items($ids);

			goaway(z_root() . '/sharedwithme');

		}

		//list files
		$r = q("SELECT id, uid, obj, item_unseen FROM item WHERE verb = '%s' AND obj_type IN ('Document', 'Video', 'Audio', 'Image') AND uid = %d AND owner_xchan != '%s' $item_normal",
			dbesc(ACTIVITY_POST),
			intval(local_channel()),
			dbesc($channel['channel_hash'])
		);

		$items = [];
		$ids = [];

		if($r) {
	
			foreach($r as $rr) {
				$object = json_decode($rr['obj'],true);
				$meta = self::get_meta($object);

				$item = [];
				$item['id'] = $rr['id'];
				$item['objfiletype'] = $meta['type'];
				$item['objfiletypeclass'] = getIconFromType($meta['type']);
				$item['objurl'] = $meta['path'] . '?f=&zid=' . $channel['xchan_addr'];
				$item['objfilename'] = $object['name'];
				$item['objfilesize'] = userReadableSize($meta['size']);
				$item['objedited'] = $meta['edited'];
				$item['unseen'] = $rr['item_unseen'];
	
				$items[] = $item;
	
				if($item['unseen']) {
					$ids[] = $rr['id'];
				}
	
			}
	
		}

		$ids = implode(',', $ids);

		if($ids) {
			q("UPDATE item SET item_unseen = 0 WHERE id IN ( $ids ) AND uid = %d",
				intval(local_channel())
			);
		}
	
		$o = '';
	
		$o .= replace_macros(get_markup_template('sharedwithme.tpl'), array(
			'$header' => t('Files: shared with me'),
			'$name' => t('Name'),
			'$label_new' => t('NEW'),
			'$size' => t('Size'),
			'$lastmod' => t('Last Modified'),
			'$dropall' => t('Remove all files'),
			'$drop' => t('Remove this file'),
			'$items' => $items
		));
	
		return $o;
	
	}
	
	function get_meta($object) {

		$ret = [];

		if(! is_array($object['attachment']))
			return;

		foreach($object['attachment'] as $a) {
			if($a['name'] === 'zot.attach.meta') {
				$ret = $a['value'];
				break;
			}
		}

		return $ret;

	}
	
}
