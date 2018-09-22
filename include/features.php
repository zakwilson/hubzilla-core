<?php /** @file */

/*
 * Features management
 */





function feature_enabled($uid,$feature) {

	$x = get_config('feature_lock',$feature);
	if($x === false) {
		$x = get_pconfig($uid,'feature',$feature);
		if($x === false) {
			$x = get_config('feature',$feature);
			if($x === false)
				$x = get_feature_default($feature);
		}
	}
	$arr = array('uid' => $uid, 'feature' => $feature, 'enabled' => $x);
	call_hooks('feature_enabled',$arr);
	return($arr['enabled']);
}

function get_feature_default($feature) {
	$f = get_features(false);
	foreach($f as $cat) {
		foreach($cat as $feat) {
			if(is_array($feat) && $feat[0] === $feature) {
				return $feat[3];
			}
		}
	}
	return false;
}


function feature_level($feature,$def) {
	$x = get_config('feature_level',$feature);
	if($x !== false)
		return intval($x);
	return $def;
}

function process_module_features_get($uid, $features) {
	unset($features[0]);
	foreach($features as $f) {
		$arr[] = array('feature_' . $f[0],$f[1],((intval(feature_enabled($uid, $f[0]))) ? "1" : ''),$f[2],array(t('Off'),t('On')));
	}
	return $arr;
}

function process_module_features_post($uid, $features, $post_arr) {
	unset($features[0]);
	foreach($features as $f) {
		$k = $f[0];
		if(array_key_exists("feature_$k",$post_arr))
			set_pconfig($uid,'feature',$k, (string) $post_arr["feature_$k"]);
		else
			set_pconfig($uid,'feature', $k, '');
	}
	if($post_arr['rpath'])
		goaway($post_arr['rpath']);
}

function get_features($filtered = true, $level = (-1)) {

	$account = \App::get_account();

	$arr = [

		// General
		'general' => [

			t('General Features'),

			[
				'start_menu',   
				t('New Member Links'),      
				t('Display new member quick links menu'),
				(($account['account_created'] > datetime_convert('','','now - 60 days')) ? true : false),
				get_config('feature_lock','start_menu'),
				feature_level('start_menu',1),
			],

/*
			[
				'hide_rating',       
				t('Hide Rating'),          
				t('Hide the rating buttons on your channel and profile pages. Note: People can still rate you somewhere else.'),
				false,
				get_config('feature_lock','hide_rating'),
				feature_level('hide_rating',3),
			],
*/			
			[
				'private_notes',       
				t('Private Notes'),          
				t('Enables a tool to store notes and reminders (note: not encrypted)'),
				false,
				get_config('feature_lock','private_notes'),
				feature_level('private_notes',1),
			],

			[
				'photo_location',       
				t('Photo Location'),          
				t('If location data is available on uploaded photos, link this to a map.'),
				false,
				get_config('feature_lock','photo_location'),
				feature_level('photo_location',2),
			],

			[
				'smart_birthdays',       
				t('Smart Birthdays'),          
				t('Make birthday events timezone aware in case your friends are scattered across the planet.'),
				true,
				get_config('feature_lock','smart_birthdays'),
				feature_level('smart_birthdays',2),
			],

			[
				'event_tz_select',       
				t('Event Timezone Selection'),          
				t('Allow event creation in timezones other than your own.'),
				false,
				get_config('feature_lock','event_tz_select'),
				feature_level('event_tz_select',2),
			],


			[
				'premium_channel', 
				t('Premium Channel'), 
				t('Allows you to set restrictions and terms on those that connect with your channel'),
				false,
				get_config('feature_lock','premium_channel'),
				feature_level('premium_channel',4),
			],

			[ 
				'advanced_dirsearch', 
				t('Advanced Directory Search'),
				t('Allows creation of complex directory search queries'),
				false, 
				get_config('feature_lock','advanced_dirsearch'),
				feature_level('advanced_dirsearch',4),
			],

			[ 
				'advanced_theming', 
				t('Advanced Theme and Layout Settings'),
				t('Allows fine tuning of themes and page layouts'),
				false, 
				get_config('feature_lock','advanced_theming'),
				feature_level('advanced_theming',4),
			],
		],


		'access_control' => [
			t('Access Control and Permissions'),

			[
				'groups',    		
				t('Privacy Groups'),		
				t('Enable management and selection of privacy groups'),
				true,
				get_config('feature_lock','groups'),
				feature_level('groups',0),
			],

			[
				'permcats',       
				t('Permission Categories'),
				t('Create custom connection permission limits'),
				false,
				get_config('feature_lock','permcats'),
				feature_level('permcats',2),
			],

			[
				'oauth_clients',       
				t('OAuth1 Clients'),          
				t('Manage OAuth1 authenticatication tokens for mobile and remote apps.'),
				false,
				get_config('feature_lock','oauth_clients'),
				feature_level('oauth_clients',1),
			],

			[
				'oauth2_clients',       
				t('OAuth2 Clients'),          
				t('Manage OAuth2 authenticatication tokens for mobile and remote apps.'),
				false,
				get_config('feature_lock','oauth2_clients'),
				feature_level('oauth2_clients',1),
			],

			[
				'access_tokens',       
				t('Access Tokens'),          
				t('Create access tokens so that non-members can access private content.'),
				false,
				get_config('feature_lock','access_tokens'),
				feature_level('access_tokens',2),
			],

		],


		// Item tools
		'tools' => [

			t('Post/Comment Tools'),

			[
				'commtag',        
				t('Community Tagging'),					
				t('Ability to tag existing posts'),
				false,
				get_config('feature_lock','commtag'),
				feature_level('commtag',1),
			],

			[
				'categories',     
				t('Post Categories'),			
				t('Add categories to your posts'),
				false,
				get_config('feature_lock','categories'),
				feature_level('categories',1),
			],

			[
				'emojis',     
				t('Emoji Reactions'),			
				t('Add emoji reaction ability to posts'),
				true,
				get_config('feature_lock','emojis'),
				feature_level('emojis',1),
			],

			[
				'filing',         
				t('Saved Folders'),				
				t('Ability to file posts under folders'),
				false,
				get_config('feature_lock','filing'),
				feature_level('filing',2),
			],

			[
				'dislike',        
				t('Dislike Posts'),				
				t('Ability to dislike posts/comments'),
				false,
				get_config('feature_lock','dislike'),
				feature_level('dislike',1),
			],

			[
				'star_posts',     
				t('Star Posts'),				
				t('Ability to mark special posts with a star indicator'),
				false,
				get_config('feature_lock','star_posts'),
				feature_level('star_posts',1),
			],

			[
				'tagadelic',      
				t('Tag Cloud'),				    
				t('Provide a personal tag cloud on your channel page'),
				false,
				get_config('feature_lock','tagadelic'),
				feature_level('tagadelic',2),
			],
		],

############################################
############################################

		'connections' => [

			t('Connections'),

			[
				'connfilter',
				t('Connection Filtering'),
				t('Filter incoming posts from connections based on keywords/content'),
				false,
				get_config('feature_lock','connfilter')
			]
		],

		'editor' => [

			t('Editor'),

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

		],

		'manage' => [

			t('Channel Manager'),

			[
				'nav_channel_select',  
				t('Navigation Channel Select'), 
				t('Change channels directly from within the navigation dropdown menu'),
				true,
				get_config('feature_lock','nav_channel_select'),
			]

		],

		'network' => [

			t('Activity'),

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
			]

		],

		'profiles' => [

			t('Profiles'),

			[
				'advanced_profiles',
				t('Advanced Profiles'),
				t('Additional profile sections and selections'),
				false,
				get_config('feature_lock','advanced_profiles')
			],

			[
				'profile_export',
				t('Profile Import/Export'),
				t('Save and load profile details across sites/channels'),
				false,
				get_config('feature_lock','profile_export')
			],

			[
				'multi_profiles',
				t('Multiple Profiles'),
				t('Ability to create multiple profiles'), 
				false, 
				get_config('feature_lock','multi_profiles')
			]

		]


	];

	$x = [ 'features' => $arr, ];
	call_hooks('get_features',$x);

	$arr = $x['features'];

	$techlevel = (($level >= 0) ? $level : get_account_techlevel());

	// removed any locked features and remove the entire category if this makes it empty

	if($filtered) {
		$narr = [];
		foreach($arr as $k => $x) {
			$narr[$k] = [ $arr[$k][0] ];
			$has_items = false;
			for($y = 0; $y < count($arr[$k]); $y ++) {
				$disabled = false;
				if(is_array($arr[$k][$y])) {
					if($arr[$k][$y][5] > $techlevel) {
						$disabled = true;
					}
					if($arr[$k][$y][4] !== false) { 
						$disabled = true;
					}
					if(! $disabled) {
						$has_items = true;
						$narr[$k][$y] = $arr[$k][$y];
					}
				}
			}
			if(! $has_items) {
				unset($narr[$k]);
			}
		}
	}
	else {
		$narr = $arr;
	}

	return $narr;
}
 
function get_module_features($module) {
	$features = get_features(false);
	return $features[$module];
}
