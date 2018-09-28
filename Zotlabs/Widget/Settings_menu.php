<?php

namespace Zotlabs\Widget;

class Settings_menu {

	function widget($arr) {

		if(! local_channel())
			return;


		$channel = \App::get_channel();

		$abook_self_id = 0;

		// Retrieve the 'self' address book entry for use in the auto-permissions link

		$role = get_pconfig(local_channel(),'system','permissions_role');

		$abk = q("select abook_id from abook where abook_channel = %d and abook_self = 1 limit 1",
			intval(local_channel())
		);
		if($abk)
			$abook_self_id = $abk[0]['abook_id'];

		$x = q("select count(*) as total from hubloc where hubloc_hash = '%s' and hubloc_deleted = 0 ",
			dbesc($channel['channel_hash'])
		);

		$hublocs = (($x && $x[0]['total'] > 1) ? true : false);

		$tabs = array(
			array(
				'label'	=> t('Account settings'),
				'url' 	=> z_root().'/settings/account',
				'selected'	=> ((argv(1) === 'account') ? 'active' : ''),
			),

			array(
				'label'	=> t('Channel settings'),
				'url' 	=> z_root().'/settings/channel',
				'selected'	=> ((argv(1) === 'channel') ? 'active' : ''),
			),

		);


		$tabs[] =	array(
			'label'	=> t('Display settings'),
			'url' 	=> z_root().'/settings/display',
			'selected'	=> ((argv(1) === 'display') ? 'active' : ''),
		);

		$tabs[] =	array(
			'label'	=> t('Addon settings'),
			'url' 	=> z_root().'/settings/featured',
			'selected'	=> ((argv(1) === 'featured') ? 'active' : ''),
		);

		if($hublocs) {
			$tabs[] = array(
				'label' => t('Manage locations'),
				'url' => z_root() . '/locs',
				'selected' => ((argv(1) === 'locs') ? 'active' : ''),
			);
		}

		$tabs[] =	array(
			'label' => t('Export channel'),
			'url' => z_root() . '/uexport',
			'selected' => ''
		);

		if($role === false || $role === 'custom') {
			$tabs[] = array(
				'label' => t('Connection Default Permissions'),
				'url' => z_root() . '/defperms',
				'selected' => ''
			);
		}

		$tabtpl = get_markup_template("generic_links_widget.tpl");
		return replace_macros($tabtpl, array(
			'$title' => t('Settings'),
			'$class' => 'settings-widget',
			'$items' => $tabs,
		));
	}

}
