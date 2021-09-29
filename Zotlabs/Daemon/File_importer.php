<?php

namespace Zotlabs\Daemon;

use Zotlabs\Web\HTTPSig;

require_once('include/cli_startup.php');
require_once('include/attach.php');
require_once('include/import.php');

class File_importer {

	static public function run($argc,$argv) {

		cli_startup();

		$page = $argv[1];
		$channel_address = $argv[2];
		$hz_server = urldecode($argv[3]);

		$m = parse_url($hz_server);

		$channel = channelx_by_nick($channel_address);
		if(! $channel) {
			logger('channel not found');
			return;
		}

		$headers = [
			'X-API-Token'      => random_string(),
			'X-API-Request'    => $hz_server . '/api/z/1.0/file/export_page?f=records=1&page=' . $page,
			'Host'             => $m['host'],
			'(request-target)' => 'get /api/z/1.0/file/export_page?f=records=1&page=' . $page,
		];

		$headers = HTTPSig::create_sig($headers,$channel['channel_prvkey'],channel_url($channel),true,'sha512');

		$x = z_fetch_url($hz_server . '/api/z/1.0/file/export_page?f=records=1&page=' . $page, false, $redirects, [ 'headers' => $headers ]);

		if(! $x['success']) {
			logger('no API response',LOGGER_DEBUG);
			killme();
		}

		$j = json_decode($x['body'],true);

		if(! is_array($j[0]['attach']) || ! count($j[0]['attach']))
			return;

		$r = sync_files($channel,$j);

		$page++;

		Master::Summon([ 'File_importer',sprintf('%d',$page), $channel['channel_address'], urlencode($hz_server) ]);

		return;
	}
}
