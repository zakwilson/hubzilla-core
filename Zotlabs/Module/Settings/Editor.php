<?php

namespace Zotlabs\Module\Settings;


class Editor {

	function post() {
		check_form_security_token_redirectOnErr('/settings/editor', 'settings_editor');
	
		$features = get_module_features('editor');

		process_module_features_post(local_channel(), $features, $_POST);
		
		build_sync_packet();
		return;
	}

	function get() {
		
		$features = get_module_features('editor');
		$rpath = (($_GET['rpath']) ? $_GET['rpath'] : '');

		$tpl = get_markup_template("settings_module.tpl");

		$o .= replace_macros($tpl, array(
			'$rpath' => $rpath,
			'$action_url' => 'settings/editor',
			'$form_security_token' => get_form_security_token("settings_editor"),
			'$title' => t('Editor Settings'),
			'$features'  => process_module_features_get(local_channel(), $features),
			'$submit'    => t('Submit')
		));
	
		return $o;
	}

}
