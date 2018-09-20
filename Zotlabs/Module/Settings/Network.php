<?php

namespace Zotlabs\Module\Settings;


class Network {

	function post() {
		check_form_security_token_redirectOnErr('/settings/network', 'settings_network');
	
		$features = self::get_features();

		process_features_post(local_channel(), $features, $_POST);
		
		build_sync_packet();
		return;
	}

	function get() {
		
		$features = self::get_features();

		$tpl = get_markup_template("settings_module.tpl");

		$o .= replace_macros($tpl, array(
			'$action_url' => 'settings/network',
			'$form_security_token' => get_form_security_token("settings_network"),
			'$title' => t('Activity Settings'),
			'$features'  => process_features_get(local_channel(), $features),
			'$submit'    => t('Submit')
		));
	
		return $o;
	}

	function get_features() {
		$arr = [

			[
				'archives',       
				t('Search by Date'),			
				t('Ability to select posts by date ranges'),
				false,
				get_config('feature_lock','archives')
			],

			[
				'savedsearch',    
				t('Saved Searches'),			
				t('Save search terms for re-use'),
				false,
				get_config('feature_lock','savedsearch')
			],

			[
				'order_tab',
				t('Alternate Stream Order'),
				t('Ability to order the stream by last post date, last comment date or unthreaded activities'),
				false,
				get_config('feature_lock','order_tab')
			],

			[
				'name_tab',
				t('Contact Filter'),
				t('Ability to display only posts of a selected contact'),
				false,
				get_config('feature_lock','name_tab')
			],

			[
				'forums_tab',         
				t('Forum Filter'),				
				t('Ability to display only posts of a specific forum'),
				false,
				get_config('feature_lock','forums_tab')
			],

			[
				'personal_tab',
				t('Personal Posts Filter'),
				t('Ability to display only posts that you\'ve interacted on'),
				false,
				get_config('feature_lock','personal_tab')
			],

			[
				'affinity',       
				t('Affinity Tool'),			    
				t('Filter stream activity by depth of relationships'),
				false,
				get_config('feature_lock','affinity')
			],

			[
				'suggest',    	
				t('Suggest Channels'),			
				t('Show friend and connection suggestions'),
				false,
				get_config('feature_lock','suggest')
			],

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
