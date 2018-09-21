<?php

namespace Zotlabs\Module\Settings;


class Connections {

	function post() {
		check_form_security_token_redirectOnErr('/settings/connections', 'settings_connections');
	
		$features = self::get_features();

		process_features_post(local_channel(), $features, $_POST);
		
		build_sync_packet();
		return;
	}

	function get() {
		
		$features = self::get_features();
		$rpath = (($_GET['rpath']) ? $_GET['rpath'] : '');

		$tpl = get_markup_template("settings_module.tpl");

		$o .= replace_macros($tpl, array(
			'$rpath' => $rpath,
			'$action_url' => 'settings/connections',
			'$form_security_token' => get_form_security_token("settings_connections"),
			'$title' => t('Connections Settings'),
			'$features'  => process_features_get(local_channel(), $features),
			'$submit'    => t('Submit')
		));
	
		return $o;
	}

	function get_features() {
		$arr = [
			[
				'connfilter',
				t('Connection Filtering'),
				t('Filter incoming posts from connections based on keywords/content'),
				false,
				get_config('feature_lock','connfilter')
			]
		];

		return $arr;

	}

}
