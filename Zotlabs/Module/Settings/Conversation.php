<?php

namespace Zotlabs\Module\Settings;

use Zotlabs\Lib\Libsync;

class Conversation {

	function post() {

		$module = substr(strrchr(strtolower(static::class), '\\'), 1);

		check_form_security_token_redirectOnErr('/settings/' . $module, 'settings_' . $module);
	
		$features = get_module_features($module);

		process_module_features_post(local_channel(), $features, $_POST);
		
		Libsync::build_sync_packet();
		
		if($_POST['aj']) {
			if($_POST['auto_update'] == 1)
				info(t('Settings saved.') . EOL);
			else
				info(t('Settings saved. Reload page please.') . EOL);

			killme();
		}
		else {
			return;
		}
	}

	function get() {

		$aj = ((isset($_GET['aj'])) ? true : false);

		$module = substr(strrchr(strtolower(static::class), '\\'), 1);

		$features = get_module_features($module);

		$tpl = (($aj) ? get_markup_template("settings_module_ajax.tpl") : get_markup_template("settings_module.tpl"));

		$o .= replace_macros($tpl, array(
			'$action_url' => 'settings/' . $module,
			'$form_security_token' => get_form_security_token('settings_' . $module),
			'$title' => t('Conversation Settings'),
			'$features'  => process_module_features_get(local_channel(), $features),
			'$submit'    => t('Submit')
		));
		
		if($aj)	{
			echo $o;
			killme();
		}
		else {
			return $o;
		}
	}

}
