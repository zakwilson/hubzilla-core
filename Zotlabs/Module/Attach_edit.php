<?php
namespace Zotlabs\Module;
/**
 * @file Zotlabs/Module/Attach_edit.php
 *
 */

use App;
use Zotlabs\Web\Controller;
use Zotlabs\Lib\Libsync;
use Zotlabs\Access\AccessList;

class Attach_edit extends Controller {

	function post() {

		if (!local_channel() && !remote_channel()) {
			return;
		}

		$attach_ids = ((x($_POST, 'attach_ids')) ? $_POST['attach_ids'] : []);
		$attach_id = ((x($_POST, 'attach_id')) ? intval($_POST['attach_id']) : 0);
		$channel_id = ((x($_POST, 'channel_id')) ? intval($_POST['channel_id']) : 0);
		$dnd = ((x($_POST, 'dnd')) ? intval($_POST['dnd']) : 0);
		$permissions = ((x($_POST, 'permissions')) ? intval($_POST['permissions']) : 0);
		$return_path = ((x($_POST, 'return_path')) ? notags($_POST['return_path']) : 'cloud');
		$delete = ((x($_POST, 'delete')) ? intval($_POST['delete']) : 0);
		$newfolder  = ((x($_POST, 'newfolder_' . $attach_id))  ? notags($_POST['newfolder_' . $attach_id])  : '');
		if(! $newfolder)
			$newfolder = ((x($_POST, 'newfolder'))  ? notags($_POST['newfolder'])  : '');
		$newfilename = ((x($_POST, 'newfilename_' . $attach_id)) ? notags($_POST['newfilename_' . $attach_id]) : '');
		$recurse = ((x($_POST, 'recurse_' . $attach_id)) ? intval($_POST['recurse_' . $attach_id]) : 0);
		if(! $recurse)
			$recurse = ((x($_POST, 'recurse')) ? intval($_POST['recurse']) : 0);
		$notify = ((x($_POST, 'notify_edit_' . $attach_id)) ? intval($_POST['notify_edit_' . $attach_id]) : 0);
		$copy = ((x($_POST, 'copy_' . $attach_id)) ? intval($_POST['copy_' . $attach_id]) : 0);
		if(! $copy)
			$copy = ((x($_POST, 'copy')) ? intval($_POST['copy']) : 0);

		$categories = ((x($_POST, 'categories_' . $attach_id)) ? notags($_POST['categories_' . $attach_id]) : '');
		if(! $categories)
			$categories = ((x($_POST, 'categories')) ? notags($_POST['categories']) : '');

		if($attach_id)
			$attach_ids[] = $attach_id;

		$single = ((count($attach_ids) === 1) ? true : false);

		$channel = channelx_by_n($channel_id);

		if (! $channel) {
			notice(t('Channel not found.') . EOL);
			return;
		}

		$nick = $channel['channel_address'];
		$observer = App::get_observer();
		$observer_hash = (($observer) ? $observer['xchan_hash'] : '');
		$is_owner = ((local_channel() == $channel_id) ? true : false);

		$ids_str = implode(',', $attach_ids);

		$r = q("SELECT id, uid, hash, creator, folder, filename, is_photo, is_dir FROM attach WHERE id IN ( %s ) AND uid = %d",
			dbesc($ids_str),
			intval($channel_id)
		);

		if (! $r) {
			notice(t('File not found.') . EOL);
			return;
		}

		foreach ($r as $rr) {
			$actions_done = '';
			$attach_id = $rr['id'];
			$resource = $rr['hash'];
			$creator = $rr['creator'];
			$folder = $rr['folder'];
			$filename = $rr['filename'];
			$is_photo = intval($rr['is_photo']);
			$is_dir = intval($rr['is_dir']);
			$admin_delete = false;

			$is_creator = (($creator == $observer_hash) ? true : false);
			$move = ((! $copy && ($folder !== $newfolder || (($single) ? $filename !== $newfilename : false))) ? true : false);

			$perms = get_all_perms($channel_id, $observer_hash);

			if (! ($perms['view_storage'] || is_site_admin())) {
				notice( t('Permission denied.') . EOL);
				continue;
			}

			if (! $perms['write_storage']) {
				if (is_site_admin()) {
					$admin_delete = true;
				}
				else {
					notice( t('Permission denied.') . EOL);
					continue;
				}
			}

			if (!$is_owner && !$admin_delete) {
				if(! $is_creator) {
					notice( t('Permission denied.') . EOL);
					continue;
				}
			}

			if ($delete) {
				attach_delete($channel_id, $resource, $is_photo);
				$actions_done .= 'delete,';
			}

			if ($copy) {
				if($is_dir && $resource == $newfolder) {
					notice( t('Can not copy folder into itself.') . EOL);
					continue;
				}
				$x = attach_copy($channel_id, $resource, $newfolder, (($single) ? $newfilename : ''));
				if ($x['success'])
					$resource = $x['resource_id'];

				$actions_done .= 'copy,';

			}

			if ($move) {
				if($is_dir && $resource == $newfolder) {
					notice( sprintf(t('Can not move folder "%s" into itself.'), $filename) . EOL);
					continue;
				}
				$x = attach_move($channel_id, $resource, $newfolder, (($single) ? $newfilename : ''));

				$actions_done .= 'move,';

			}

			if(! $delete && ! $dnd) {
				if ($single || (! $single && $categories)) {
					q("DELETE FROM term WHERE uid = %d AND oid = %d AND otype = %d",
						intval($channel_id),
						intval($attach_id),
						intval(TERM_OBJ_FILE)
					);
					$cat = explode(',', $categories);
					if ($cat) {
						foreach($cat as $term) {
							$term = trim(escape_tags($term));
							if ($term) {
								$term_link = z_root() . '/cloud/' . $nick . '/?cat=' . $term;
								store_item_tag($channel_id, $attach_id, TERM_OBJ_FILE, TERM_CATEGORY, $term, $term_link);
							}
						}
						$actions_done .= 'cat_add,';
					}
				}
				else {
					q("DELETE FROM term WHERE uid = %d AND oid = %d AND otype = %d",
						intval($channel_id),
						intval($attach_id),
						intval(TERM_OBJ_FILE)
					);
					$actions_done .= 'cat_remove,';
				}

				if ($is_owner && ($single || (! $single && $permissions))) {
					$acl = new AccessList($channel);
					$acl->set_from_array($_REQUEST);
					$x = $acl->get();

					attach_change_permissions($channel_id, $resource, $x['allow_cid'], $x['allow_gid'], $x['deny_cid'], $x['deny_gid'], $recurse, true);
					$actions_done .= 'permissions,';

					if ($notify) {
						attach_store_item($channel, $observer, $resource);
						$actions_done .= 'notify,';
					}
				}
			}

			if (! $admin_delete && $actions_done) {
				$sync = attach_export_data($channel, $resource, (($delete) ? true : false));

				if ($sync) {
					Libsync::build_sync_packet($channel_id, ['file' => [$sync]]);
				}
			}

			logger('attach_edit: ' . $actions_done);

		}

		if($dnd || $delete) {
			json_return_and_die([ 'success' => true ]);
		}

		goaway($return_path);

	}

}
