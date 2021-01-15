<?php

namespace Zotlabs\Module;

use ZipArchive;
use Zotlabs\Web\Controller;
use Zotlabs\Lib\Verify;

require_once('include/security.php');
require_once('include/attach.php');

class Attach extends Controller {

	function post() {

		$attach_ids  = ((x($_REQUEST, 'attach_ids')) ? $_REQUEST['attach_ids'] : []);
		$attach_path = ((x($_REQUEST, 'attach_path')) ? $_REQUEST['attach_path'] : '');
		$channel_id  = ((x($_REQUEST, 'channel_id')) ? intval($_REQUEST['channel_id']) : 0);
		$channel     = channelx_by_n($channel_id);

		if (!$channel) {
			notice(t('Channel not found.') . EOL);
			return;
		}

		$strip_str   = '/cloud/' . $channel['channel_address'] . '/';
		$count       = strlen($strip_str);
		$attach_path = substr($attach_path, $count);

		if ($attach_ids) {

			$zip_dir = 'store/[data]/' . $channel['channel_address'] . '/tmp';
			if (!is_dir($zip_dir))
				mkdir($zip_dir, STORAGE_DEFAULT_PERMISSIONS, true);

			$token = random_string(32);

			$zip_file = 'download_' . $token . '.zip';
			$zip_path = $zip_dir . '/' . $zip_file;

			$zip = new ZipArchive();

			if ($zip->open($zip_path, ZipArchive::CREATE) === true) {

				$zip_filename = self::zip_archive_handler($zip, $attach_ids, $attach_path);

				$zip->close();

				$meta = [
					'zip_filename' => $zip_filename,
					'zip_path'     => $zip_path
				];

				Verify::create('zip_token', 0, $token, json_encode($meta));

				json_return_and_die([
					'success' => true,
					'token'   => $token
				]);

			}
		}
	}

	function get() {

		if (argc() < 2) {
			notice(t('Item not available.') . EOL);
			return;
		}

		$token = ((x($_REQUEST, 'token')) ? $_REQUEST['token'] : '');

		if (argv(1) === 'download') {
			$meta = Verify::get_meta('zip_token', 0, $token);

			if (!$meta)
				killme();

			$meta = json_decode($meta, true);

			header('Content-Type: application/zip');
			header('Content-Disposition: attachment; filename="' . $meta['zip_filename'] . '"');
			header('Content-Length: ' . filesize($meta['zip_path']));

			$istream = fopen($meta['zip_path'], 'rb');
			$ostream = fopen('php://output', 'wb');
			if ($istream && $ostream) {
				pipe_streams($istream, $ostream);
				fclose($istream);
				fclose($ostream);
			}

			unlink($meta['zip_path']);
			killme();
		}

		$r = attach_by_hash(argv(1), get_observer_hash(), ((argc() > 2) ? intval(argv(2)) : 0));

		if (!$r['success']) {
			notice($r['message'] . EOL);
			return;
		}

		$c = q("select channel_address from channel where channel_id = %d limit 1",
			intval($r['data']['uid'])
		);

		if (!$c)
			return;

		$unsafe_types = array('text/html', 'text/css', 'application/javascript');

		if (in_array($r['data']['filetype'], $unsafe_types) && (!channel_codeallowed($r['data']['uid']))) {
			header('Content-Type: text/plain');
		}
		else {
			header('Content-Type: ' . $r['data']['filetype']);
		}

		header('Content-Disposition: attachment; filename="' . $r['data']['filename'] . '"');
		if (intval($r['data']['os_storage'])) {
			$fname = $r['data']['content'];
			if (strpos($fname, 'store') !== false)
				$istream = fopen($fname, 'rb');
			else
				$istream = fopen('store/' . $c[0]['channel_address'] . '/' . $fname, 'rb');
			$ostream = fopen('php://output', 'wb');
			if ($istream && $ostream) {
				pipe_streams($istream, $ostream);
				fclose($istream);
				fclose($ostream);
			}
		}
		else
			echo $r['data']['content'];
		killme();

	}

	public function zip_archive_handler($zip, $attach_ids, $attach_path, $pass = 1) {

		$observer_hash = get_observer_hash();
		$single        = ((count($attach_ids) == 1) ? true : false);
		$download_name = 'download.zip';

		foreach ($attach_ids as $attach_id) {

			$r = attach_by_id($attach_id, $observer_hash);

			if (!$r['success']) {
				continue;
			}

			if ($r['data']['is_dir'] && $single && $pass === 1)
				$download_name = $r['data']['filename'] . '.zip';

			$zip_path = $r['data']['display_path'];

			if ($attach_path) {
				$strip_str = $attach_path . '/';
				$count     = strlen($strip_str);
				$zip_path  = substr($r['data']['display_path'], $count);
			}

			if ($r['data']['is_dir']) {
				$zip->addEmptyDir($zip_path);

				$d = q("SELECT id FROM attach WHERE folder = '%s'",
					dbesc($r['data']['hash'])
				);

				$attach_ids = ids_to_array($d);
				self::zip_archive_handler($zip, $attach_ids, $attach_path, $pass++);
			}
			else {
				$file_path = $r['data']['content'];
				$zip->addFile($file_path, $zip_path);
				// compressing can be ressource intensive - just store the data
				$zip->setCompressionName($zip_path, ZipArchive::CM_STORE);
			}

		}

		return $download_name;
	}

}
