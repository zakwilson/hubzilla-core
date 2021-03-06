<?php

/**
 *   * Name: Suggestions
 *   * Description: Display suggestions for new contacts
 */

namespace Zotlabs\Widget;

use Zotlabs\Lib\Apps;

require_once('include/socgraph.php');


class Suggestions {

	function widget($arr) {


		if(! local_channel())
			return EMPTY_STR;

		$r = suggestion_query(local_channel(),get_observer_hash(),0,20);

		if(! $r) {
			return;
		}

		$arr = array();

		// Get four random entries from the top 20 returned.
		// We'll grab the first one and the one immediately following.
		// This will throw some entropy intot he situation so you won't
		// be looking at the same two mug shots every time the widget runs

		$index = ((count($r) > 4) ? mt_rand(0,count($r) - 4) : 0);

		for($x = $index; $x <= ($index+3); $x ++) {
			$rr = $r[$x];
			if(! $rr['xchan_url'])
				break;

			$connlnk = z_root() . '/follow/?url=' . $rr['xchan_addr'];

			$arr[] = array(
				'url' => chanlink_url($rr['xchan_url']),
				'profile' => $rr['xchan_url'],
				'name' => $rr['xchan_name'],
				'photo' => $rr['xchan_photo_m'],
				'ignlnk' => z_root() . '/directory?ignore=' . $rr['xchan_hash'],
				'conntxt' => t('Connect'),
				'connlnk' => $connlnk,
				'ignore' => t('Ignore/Hide')
			);
		}

		$o = replace_macros(get_markup_template('suggest_widget.tpl'),array(
			'$title' => t('Suggestions'),
			'$more' => t('See more...'),
			'$entries' => $arr
		));

		return $o;
	}
}
