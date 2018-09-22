<?php

namespace Zotlabs\Module\Settings;


class Editor {

	function post() {
		check_form_security_token_redirectOnErr('/settings/editor', 'settings_editor');
	
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
			'$action_url' => 'settings/editor',
			'$form_security_token' => get_form_security_token("settings_editor"),
			'$title' => t('Editor Settings'),
			'$features'  => process_features_get(local_channel(), $features),
			'$submit'    => t('Submit')
		));
	
		return $o;
	}

	function get_features() {
		$arr = [

			[
				'large_photos',   
				t('Large Photos'),              
				t('Include large (1024px) photo thumbnails in posts. If not enabled, use small (640px) photo thumbnails'),
				false,
				get_config('feature_lock','large_photos'),
			],

			[
				'content_encrypt', 
				t('Even More Encryption'),          
				t('Allow optional encryption of content end-to-end with a shared secret key'),
				false,
				get_config('feature_lock','content_encrypt'),
			],
			
			[
				'consensus_tools', 
				t('Enable Voting Tools'),      
				t('Provide a class of post which others can vote on'),
				false,
				get_config('feature_lock','consensus_tools'),
			],

			[
				'disable_comments', 
				t('Disable Comments'),      
				t('Provide the option to disable comments for a post'),
				false,
				get_config('feature_lock','disable_comments'),
			],

			[
				'delayed_posting', 
				t('Delayed Posting'),      
				t('Allow posts to be published at a later date'),
				false,
				get_config('feature_lock','delayed_posting'),
			],

			[ 	
				'content_expire',
				t('Content Expiration'),
				t('Remove posts/comments and/or private messages at a future time'), 
				false, 
				get_config('feature_lock','content_expire'),
			],

			[
				'suppress_duplicates', 
				t('Suppress Duplicate Posts/Comments'),  
				t('Prevent posts with identical content to be published with less than two minutes in between submissions.'),
				true,
				get_config('feature_lock','suppress_duplicates'),
			],

			[
				'auto_save_draft', 
				t('Auto-save drafts of posts and comments'),  
				t('Automatically saves post and comment drafts in local browser storage to help prevent accidental loss of compositions'),
				true,
				get_config('feature_lock','auto_save_draft'),
			]

		];

		return $arr;

	}

}
