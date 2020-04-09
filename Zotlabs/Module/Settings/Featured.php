<?php

namespace Zotlabs\Module\Settings;

use Zotlabs\Lib\Libsync;

class Featured {
		
	function post() {
		check_form_security_token_redirectOnErr('/settings/featured', 'settings_featured');
	
		call_hooks('feature_settings_post', $_POST);
	
		Libsync::build_sync_packet();
		return;
	}

	function get() {
		$settings_addons = "";
	
		$o = '';
			
		$r = q("SELECT * FROM hook WHERE hook = 'feature_settings' ");
		if(! $r)
			$settings_addons = t('No feature settings configured');
	
		call_hooks('feature_settings', $settings_addons);
		
		$this->sortpanels($settings_addons);

		$tpl = get_markup_template("settings_addons.tpl");
		$o .= replace_macros($tpl, array(
			'$form_security_token' => get_form_security_token("settings_featured"),
			'$title'	=> t('Addon Settings'),
			'$descrip'  => t('Please save/submit changes to any panel before opening another.'),
			'$settings_addons' => $settings_addons
		));
		return $o;
	}

	function sortpanels(&$s) {
		$a = explode('<div class="panel">',$s);
		if($a) {
			usort($a,'featured_sort');
			$s = implode('<div class="panel">',$a);
		}
	}

}


