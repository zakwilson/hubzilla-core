<?php

namespace Zotlabs\Widget;

use Zotlabs\Lib\Apps;

class Notes {

	function widget($arr) {
		if(! local_channel())
			return EMPTY_STR;

		$text = get_pconfig(local_channel(),'notes','text');

		$tpl = get_markup_template('notes.tpl');

		$o = replace_macros($tpl, array(
			'$banner' => t('Notes'),
			'$text' => $text,
			'$save' => t('Save'),
			'$app' => ((isset($arr['app'])) ? true : false),
			'$hidden' => ((isset($arr['hidden'])) ? true : false)
		));

		return $o;
	}
}
