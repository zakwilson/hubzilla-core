<?php

namespace Zotlabs\Module\Settings;


class Editor {

	function post() {

		$module = substr(strrchr(strtolower(static::class), '\\'), 1);

		check_form_security_token_redirectOnErr('/settings/' . $module, 'settings_' . $module);
	
		$features = get_module_features($module);

		process_module_features_post(local_channel(), $features, $_POST);
		
		build_sync_packet();
		return;
	}

	function get() {

		$module = substr(strrchr(strtolower(static::class), '\\'), 1);

		$features = get_module_features($module);
		$rpath = (($_GET['rpath']) ? $_GET['rpath'] : '');

		$tpl = get_markup_template("settings_module.tpl");

		$o .= replace_macros($tpl, array(
			'$rpath' => $rpath,
			'$action_url' => 'settings/' . $module,
			'$form_security_token' => get_form_security_token('settings_' . $module),
			'$title' => t('Editor Settings'),
			'$features'  => process_module_features_get(local_channel(), $features),
			'$submit'    => t('Submit')
		));
	
		return $o;
	}

}
