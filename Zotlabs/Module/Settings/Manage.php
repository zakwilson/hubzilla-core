<?php

namespace Zotlabs\Module\Settings;


class Manage {

	function post() {
		check_form_security_token_redirectOnErr('/settings/manage', 'settings_manage');
	
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
			'$action_url' => 'settings/manage',
			'$form_security_token' => get_form_security_token("settings_manage"),
			'$title' => t('Channel Manager Settings'),
			'$features'  => process_features_get(local_channel(), $features),
			'$submit'    => t('Submit')
		));
	
		return $o;
	}

	function get_features() {
		$arr = [

			[
				'nav_channel_select',  
				t('Navigation Channel Select'), 
				t('Change channels directly from within the navigation dropdown menu'),
				true,
				get_config('feature_lock','nav_channel_select'),
			]

		];

		return $arr;

	}

}
