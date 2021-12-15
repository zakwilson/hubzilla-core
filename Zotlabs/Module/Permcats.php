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


		$name = escape_tags(trim($_POST['name']));
		$is_system_role = isset($_POST['is_system_role']);
		$return_path = z_root() . '/permcats/' . $_POST['return_path'];
		$group_hash = ((isset($_POST['group_select'])) ? $_POST['group_select'] : '');
		$contacts = [];

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

		if (isset($_POST['default_role'])) {
			set_pconfig(local_channel(), 'system', 'default_permcat', $name);
		}

		if ($is_system_role) {
			// if we have a system role just set the default and assign if aplicable and be done with it
			if ($contacts)
				\Zotlabs\Lib\Permcat::assign($channel, $name, $contacts);

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

		$channel = App::get_channel();

		if(argc() > 1)
			$name = hex2bin(argv(1));

		if(argc() > 2 && argv(2) === 'drop') {
			\Zotlabs\Lib\Permcat::delete(local_channel(),$name);

			// TODO: assign all members of the deleted role to the default role

			Libsync::build_sync_packet();
			json_return_and_die([ 'success' => true ]);
		}


		$existing = [];

		$pcat = new \Zotlabs\Lib\Permcat(local_channel());
		$pcatlist = $pcat->listing();

/* not yet ready
		$test = $pcatlist[4]['perms'];
		$role_sql = '';

		foreach ($test as $t)
			$role_sql .= "( k = '" . dbesc($t['name']) . "' AND v = '" . intval($t['value']) . "' ) OR ";

		$role_sql = rtrim($role_sql, ' OR ');

		// get all xchans belonging to a permission role
		$q = q("SELECT xchan FROM abconfig WHERE chan = %d AND cat = 'my_perms' AND ( $role_sql ) GROUP BY xchan HAVING count(xchan) = %d",
			intval(local_channel()),
			intval(count($test))
		);
*/

		$is_system_role = false;
		$permcats = [];
		if($pcatlist) {
			foreach($pcatlist as $pc) {
				if(($pc['name']) && ($name) && ($pc['name'] == $name)) {
					$existing = $pc['perms'];
					if (isset($pc['system']) && intval($pc['system']))
						$is_system_role = $pc['name'];
				}

				$permcats[bin2hex($pc['name'])] = $pc['localname'];

				if($pc['name'] == $name)
					$localname = $pc['localname'];
			}
		}

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

		$is_default_role = (get_pconfig(local_channel(),'system','default_permcat','default') == $name);

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
			'$default_role'      => array('default_role', t('Use this role as default for new contacts'), intval($is_default_role), '', [t('No'), t('Yes')]),

			'$title'	=> t('Contact Roles'),
			'$tokens' => $t,
			'$permcats' => $permcats,
			'$atoken' => $atoken,
			'$url1' => z_root() . '/channel/' . $channel['channel_address'],
			'$url2' => z_root() . '/photos/' . $channel['channel_address'],
			'$name' => ['name', t('Role name') . ' <span class="required">*</span>', (($localname) ? $localname : ''), (($is_system_role) ? t('System role - not editable') : '') , '', (($is_system_role) ? 'disabled' : '')],
			'$perms' => $perms,
			'$inherited' => t('inherited'),
			'$is_system_role' => $is_system_role,
			'$permlbl' => t('Role Permissions'),
			'$permnote' => t('Some permissions may be inherited from your <a href="settings">channel role</a>, which have higher priority than contact role settings.'),
			'$submit' 	=> t('Submit'),
			'$return_path' => argv(1),
			'$group_select' => $group_select,

		));
		return $o;
	}

}
