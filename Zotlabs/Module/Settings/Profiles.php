<?php

namespace Zotlabs\Module\Settings;


class Profiles {

	function post() {
		check_form_security_token_redirectOnErr('/settings/profiles', 'settings_profiles');
	
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
			'$action_url' => 'settings/profiles',
			'$form_security_token' => get_form_security_token("settings_profiles"),
			'$title' => t('Profile Settings'),
			'$features'  => process_features_get(local_channel(), $features),
			'$submit'    => t('Submit')
		));
	
		return $o;
	}

	function get_features() {
		$arr = [

			[
				'advanced_profiles',   
				t('Advanced Profiles'),      
				t('Additional profile sections and selections'),
				false,
				get_config('feature_lock','advanced_profiles'),
			],

			[
				'profile_export',      
				t('Profile Import/Export'),  
				t('Save and load profile details across sites/channels'),
				false,
				get_config('feature_lock','profile_export'),
			],

			[
				'multi_profiles',      
				t('Multiple Profiles'),      
				t('Ability to create multiple profiles'), 
				false, 
				get_config('feature_lock','multi_profiles'),
			]


		];

		return $arr;

	}

}
