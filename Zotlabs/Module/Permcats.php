<?php

namespace Zotlabs\Module;

use App;
use Zotlabs\Web\Controller;
use Zotlabs\Lib\Apps;
use Zotlabs\Lib\Libsync;
use Zotlabs\Lib\AccessList;

class Permcats extends Controller {

	function post() {

		if(! local_channel())
			return;

		$channel = App::get_channel();

		check_form_security_token_redirectOnErr('/permcats', 'permcats');

		$name = escape_tags(trim($_REQUEST['name']));
		$is_system_role = isset($_REQUEST['is_system_role']);
		$return_path = z_root() . '/permcats/' . $_REQUEST['return_path'];
		$group_hash = $_REQUEST['group_select'] ?? '';
		$deleted_role = $_REQUEST['deleted_role'] ?? '';
		$new_role = $_REQUEST['new_role'] ?? '';
		$contacts = [];

		if (argv(1) && hex2bin(argv(1)) !== $name) {
			$return_path = z_root() . '/permcats/' . bin2hex($name);
		}

		if($deleted_role && $new_role) {
			$r = q("SELECT abook_xchan FROM abook WHERE abook_channel = %d AND abook_role = '%s' AND abook_self = 0 AND abook_pending = 0",
				intval(local_channel()),
				dbesc($deleted_role)
			);

			if ($r) {
				$contacts = ids_to_array($r, 'abook_xchan');
			}

			if ($contacts) {
				\Zotlabs\Lib\Permcat::assign($channel, $new_role, $contacts);
			}

			\Zotlabs\Lib\Permcat::delete(local_channel(), $deleted_role);

			$default_role = get_pconfig(local_channel(), 'system', 'default_permcat', 'default');
			if($deleted_role === $default_role) {
				set_pconfig(local_channel(), 'system', 'default_permcat', $new_role);
			}

			Libsync::build_sync_packet();
			info(t('Contact role deleted.') . EOL);

			goaway(z_root() . '/permcats/' . bin2hex($new_role));

			return;
		}

		if ($group_hash === 'all_contacts') {
			$r = q("SELECT abook_xchan FROM abook WHERE abook_channel = %d and abook_self = 0 and abook_pending = 0",
				intval(local_channel())
			);

			if ($r) {
				$contacts = ids_to_array($r, 'abook_xchan');
			}
		}

		if (!$contacts && $group_hash) {
			$group = AccessList::by_hash(local_channel(), $group_hash);
		}

		if ($group) {
			$contacts = AccessList::members_xchan(local_channel(), $group['id']);
		}

		if(! $name ) {
			notice( t('Permission category name is required.') . EOL);
			return;
		}

		set_pconfig(local_channel(), 'system', 'default_permcat', 'default');

		if (isset($_REQUEST['default_role'])) {
			set_pconfig(local_channel(), 'system', 'default_permcat', $name);
		}

		if ($is_system_role) {
			// if we have a system role just set the default and assign if aplicable and be done with it
			if ($contacts) {
				\Zotlabs\Lib\Permcat::assign($channel, $name, $contacts);
			}

			info( t('Contact role saved.') . EOL);
			Libsync::build_sync_packet();
			goaway($return_path);
			return;
		}

		$pcarr = [];
		$all_perms = \Zotlabs\Access\Permissions::Perms();

		if($all_perms) {
			foreach($all_perms as $perm => $desc) {
				if(array_key_exists('perms_' . $perm, $_POST)) {
					$pcarr[] = $perm;
				}
			}
		}

		\Zotlabs\Lib\Permcat::update(local_channel(), $name, $pcarr);

		if ($contacts) {
			\Zotlabs\Lib\Permcat::assign($channel, $name, $contacts);
		}

		Libsync::build_sync_packet();

		info( t('Contact role saved.') . EOL);
		goaway($return_path);

		return;
	}


	function get() {

		if(! local_channel())
			return;

		nav_set_selected('Contact Roles');

		$channel = App::get_channel();

		if(argc() > 1) {
			$name = hex2bin(argv(1));
		}

		$existing = [];

		$pcat = new \Zotlabs\Lib\Permcat(local_channel());
		$pcatlist = $pcat->listing();

		$is_system_role = false;
		$delete_role_select_options = [];
		$is_default_role = (get_pconfig(local_channel(),'system','default_permcat','default') === $name);

		if($pcatlist) {
			foreach($pcatlist as $pc) {
				if(($pc['name']) && ($name) && ($pc['name'] == $name)) {
					$existing = $pc['perms'];
					if (isset($pc['system']) && intval($pc['system']))
						$is_system_role = $pc['name'];
				}

				if($pc['name'] == $name) {
					$localname = $pc['localname'];
				}

				if ($pc['name'] !== $name) {
					$delete_role_select_options[$pc['name']] = $pc['localname'];
				}

			}
		}

		// select for delete action
		$delete_role_select = [
			'new_role',
			(($is_default_role) ? t('Role to assign affected contacts and default role to') : t('Role to assign affected contacts to')),
			'',
			'',
			$delete_role_select_options
		];

		$global_perms = \Zotlabs\Access\Permissions::Perms();

		foreach($global_perms as $k => $v) {
			$thisperm = \Zotlabs\Lib\Permcat::find_permcat($existing,$k);
			$checkinherited = \Zotlabs\Access\PermissionLimits::Get(local_channel(),$k);

			if($existing[$k])
				$thisperm = 1;

			$perms[] = [
				'perms_' . $k,
				$v,
				'',
				$thisperm,
				1,
				(($checkinherited & PERMS_SPECIFIC) ? '' : '1'),
				'',
				$checkinherited
			];
		}


		$group_select_options = [
			'selected' => '',
			'form_id' => 'group_select',
			'label' => t('Assign this role to'),
			'after' => [
				'name' => t('All my contacts'),
				'id' => 'all_contacts',
				'selected' => false
			]
		];

		$group_select = AccessList::select(local_channel(), $group_select_options);

		$tpl = get_markup_template("permcats.tpl");
		$o .= replace_macros($tpl, array(
			'$form_security_token' => get_form_security_token("permcats"),
			'$default_role' => array('default_role', t('Automatically assign this role to new contacts'), intval($is_default_role), '', [t('No'), t('Yes')]),
			'$title'	=> t('Contact Roles'),
			'$name' => ['name', t('Role name') . ' <span class="required">*</span>', (($localname) ? $localname : ''), (($is_system_role) ? t('System role - not editable') : '') , '', (($is_system_role) ? 'disabled' : '')],
			'$delete_label' => t('Deleting') . ' ' . $localname,
			'$current_role' => $name,
			'$perms' => $perms,
			'$inherited' => t('inherited'),
			'$is_system_role' => $is_system_role,
			'$permlbl' => t('Role Permissions'),
			'$permnote' => t('Some permissions may be inherited from your <a href="settings">channel role</a>, which have higher priority than contact role settings.'),
			'$submit' 	=> t('Submit'),
			'$return_path' => argv(1),
			'$group_select' => $group_select,
			'$delete_role_select' => $delete_role_select,
			'$delet_role_button' => t('Delete')

		));
		return $o;
	}

}
