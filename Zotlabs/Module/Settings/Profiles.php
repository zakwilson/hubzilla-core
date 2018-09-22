<?php

namespace Zotlabs\Module\Settings;


class Profiles {

	function post() {
		check_form_security_token_redirectOnErr('/settings/profiles', 'settings_profiles');
	
		$features = get_module_features('profiles');

		process_module_features_post(local_channel(), $features, $_POST);
		
		build_sync_packet();
		return;
	}

	function get() {
		
		$features = get_module_features('profiles');
		$rpath = (($_GET['rpath']) ? $_GET['rpath'] : '');

		$tpl = get_markup_template("settings_module.tpl");

		$o .= replace_macros($tpl, array(
			'$rpath' => $rpath,
			'$action_url' => 'settings/profiles',
			'$form_security_token' => get_form_security_token("settings_profiles"),
			'$title' => t('Profile Settings'),
			'$features'  => process_module_features_get(local_channel(), $features),
			'$submit'    => t('Submit')
		));
	
		return $o;
	}

}
