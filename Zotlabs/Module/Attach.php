<?php
namespace Zotlabs\Module;

use ZipArchive;
use Zotlabs\Web\Controller;

require_once('include/security.php');
require_once('include/attach.php');

class Attach extends Controller {

	function post() {

		$attach_ids = ((x($_REQUEST, 'attach_ids')) ? $_REQUEST['attach_ids'] : []);

		if ($attach_ids) {

			$ret = ['success' => false];

			$channel_id = ((x($_REQUEST, 'channel_id')) ? intval($_REQUEST['channel_id']) : 0);
			$channel = channelx_by_n($channel_id);

			if (! $channel) {
				notice(t('Channel not found.') . EOL);
				return;
			}

			$zip_dir = 'store/[data]/' . $channel['channel_address'] . '/tmp';
			if (! is_dir($zip_dir))
				mkdir($zip_dir, STORAGE_DEFAULT_PERMISSIONS, true);

			$rnd = random_string(10);

			$zip_file = 'download_' . $rnd . '.zip';
			$zip_path = $zip_dir . '/' . $zip_file;

			$zip = new ZipArchive();

			if ($zip->open($zip_path, ZipArchive::CREATE) === true) {

				$filename = self::zip_archive_handler($zip, $attach_ids);

				$zip->close();

				header('Content-Type: application/zip');
				header('Content-Disposition: attachment; filename="' . $filename . '"');
				header('Content-Length: ' . filesize($zip_path));

				$istream = fopen($zip_path, 'rb');
				$ostream = fopen('php://output', 'wb');

				if ($istream && $ostream) {
					pipe_streams($istream,$ostream);
					fclose($istream);
					fclose($ostream);
				}

				unlink($zip_path);
				killme();
			}
		}
	}

	function get() {

		if(argc() < 2) {
			notice( t('Item not available.') . EOL);
			return;
		}

		$r = attach_by_hash(argv(1),get_observer_hash(),((argc() > 2) ? intval(argv(2)) : 0));

		if(! $r['success']) {
			notice( $r['message'] . EOL);
			return;
		}

		$c = q("select channel_address from channel where channel_id = %d limit 1",
			intval($r['data']['uid'])
		);

		if(! $c)
			return;


		$unsafe_types = array('text/html','text/css','application/javascript');

		if(in_array($r['data']['filetype'],$unsafe_types) && (! channel_codeallowed($r['data']['uid']))) {
				header('Content-Type: text/plain');
		}
		else {
			header('Content-Type: ' . $r['data']['filetype']);
		}

		header('Content-Disposition: attachment; filename="' . $r['data']['filename'] . '"');
		if(intval($r['data']['os_storage'])) {
			$fname = $r['data']['content'];
			if(strpos($fname,'store') !== false)
				$istream = fopen($fname,'rb');
			else
				$istream = fopen('store/' . $c[0]['channel_address'] . '/' . $fname,'rb');
			$ostream = fopen('php://output','wb');
			if($istream && $ostream) {
				pipe_streams($istream,$ostream);
				fclose($istream);
				fclose($ostream);
			}
		}
		else
			echo $r['data']['content'];
		killme();

	}

	function zip_archive_handler($zip, $attach_ids, $pass = 1) {

		$observer_hash = get_observer_hash();
		$single = ((count($attach_ids) == 1) ? true : false);
		$filename = 'download.zip';

		foreach($attach_ids as $attach_id) {

			$r = attach_by_id($attach_id, $observer_hash);

			if (! $r['success']) {
				continue;
			}

			if ($r['data']['is_dir'] && $single && $pass === 1)
				$filename = $r['data']['filename'] . '.zip';

			if ($r['data']['is_dir']) {
				$zip->addEmptyDir($r['data']['display_path']);

				$d = q("SELECT id FROM attach WHERE folder = '%s'",
					dbesc($r['data']['hash'])
				);

				$attach_ids = ids_to_array($d);
				self::zip_archive_handler($zip, $attach_ids, $observer_hash, $pass++);
			}
			else {
				$file_path = $r['data']['content'];
				$file_name = $r['data']['display_path'];
				$zip->addFile($file_path, $file_name);
			}

		}

		return $filename;
	}

}
