<?php

namespace Zotlabs\Widget;

use Zotlabs\Lib\Apps;


class Hq_controls {

	function widget($options) {

		if (! local_channel())
			return;

		$entries = [
			'toggle_editor' => [
				'label' => t('Toggle post editor'),
				'id' => 'jot-toggle',
				'href' => '#',
				'class' => 'btn btn-outline-primary',
				'type' => 'button',
				'icon' => 'pencil',
				'extra' => 'data-toggle="button"'
			]
		];

		if(Apps::system_app_installed(local_channel(), 'Notes')) {
			$entries['toggle_notes'] = [
				'label' => t('Toggle personal notes'),
				'id' => 'notes-toggle',
				'href' => '#',
				'class' => 'btn btn-outline-primary',
				'type' => 'button',
				'icon' => 'sticky-note-o',
				'extra' => 'data-toggle="button"'
			];
		}

		return replace_macros(get_markup_template('hq_controls.tpl'),
			[
				'$entries' => $entries,
				'$wrapper_class' => $options['class']
			]
		);
	}
}
