<?php

namespace Zotlabs\Module;

use App;
use Zotlabs\Lib\Activity;
use Zotlabs\Lib\ActivityStreams;
use Zotlabs\Lib\Config;
use Zotlabs\Web\Controller;
use Zotlabs\Web\HTTPSig;

class Zotfeed extends Controller {

	function init() {

		if (ActivityStreams::is_as_request()) {

			if (observer_prohibited(true)) {
				killme();
			}

			if (argc() < 2) {
				killme();
			}

			$channel = channelx_by_nick(argv(1));
			if (!$channel) {
				killme();
			}

			if (intval($channel['channel_system'])) {
				killme();
			}

			$sigdata = HTTPSig::verify(($_SERVER['REQUEST_METHOD'] === 'POST') ? file_get_contents('php://input') : EMPTY_STR);
			if ($sigdata['portable_id'] && $sigdata['header_valid']) {
				$portable_id = $sigdata['portable_id'];
				if (!check_channelallowed($portable_id)) {
					http_status_exit(403, 'Permission denied');
				}
				if (!check_siteallowed($sigdata['signer'])) {
					http_status_exit(403, 'Permission denied');
				}
				observer_auth($portable_id);
			}
			elseif (Config::get('system', 'require_authenticated_fetch', false)) {
				http_status_exit(403, 'Permission denied');
			}

			$observer_hash = get_observer_hash();

			$params = [];

			$params['begin']     = ((x($_REQUEST, 'date_begin')) ? $_REQUEST['date_begin'] : NULL_DATE);
			$params['end']       = ((x($_REQUEST, 'date_end')) ? $_REQUEST['date_end'] : '');
			$params['type']      = 'json';
			$params['pages']     = ((x($_REQUEST, 'pages')) ? intval($_REQUEST['pages']) : 0);
			$params['top']       = ((x($_REQUEST, 'top')) ? intval($_REQUEST['top']) : 0);
			$params['direction'] = ((x($_REQUEST, 'direction')) ? dbesc($_REQUEST['direction']) : 'desc'); // unimplemented
			$params['cat']       = ((x($_REQUEST, 'cat')) ? escape_tags($_REQUEST['cat']) : '');
			$params['compat']    = 1;

			$total = items_fetch(
				[
					'total'      => true,
					'wall'       => '1',
					'datequery'  => $params['end'],
					'datequery2' => $params['begin'],
					'direction'  => dbesc($params['direction']),
					'pages'      => $params['pages'],
					'order'      => dbesc('post'),
					'top'        => $params['top'],
					'cat'        => $params['cat'],
					'compat'     => $params['compat']
				], $channel, $observer_hash, CLIENT_MODE_NORMAL, App::$module
			);

			if ($total) {
				App::set_pager_total($total);
				App::set_pager_itemspage(10);
			}

			if (App::$pager['unset'] && $total > 10) {
				$ret = Activity::paged_collection_init($total, App::$query_string);
			}
			else {
				$items = items_fetch(
					[
						'wall'       => '1',
						'datequery'  => $params['end'],
						'datequery2' => $params['begin'],
						'records'    => intval(App::$pager['itemspage']),
						'start'      => intval(App::$pager['start']),
						'direction'  => dbesc($params['direction']),
						'pages'      => $params['pages'],
						'order'      => dbesc('post'),
						'top'        => $params['top'],
						'cat'        => $params['cat'],
						'compat'     => $params['compat']
					], $channel, $observer_hash, CLIENT_MODE_NORMAL, App::$module
				);

				$ret = Activity::encode_item_collection($items, App::$query_string, 'OrderedCollection', $total);
			}

			as_return_and_die($ret, $channel);
		}

		/*
		$result = array('success' => false);

		$mindate = (($_REQUEST['mindate']) ? datetime_convert('UTC','UTC',$_REQUEST['mindate']) : '');
		if(! $mindate)
			$mindate = datetime_convert('UTC','UTC', 'now - 14 days');

		if(observer_prohibited()) {
			$result['message'] = 'Public access denied';
			json_return_and_die($result);
		}

		$observer = App::get_observer();

		logger('observer: ' . get_observer_hash(), LOGGER_DEBUG);

		$channel_address = ((argc() > 1) ? argv(1) : '');
		if($channel_address) {
			$r = q("select channel_id, channel_name from channel where channel_address = '%s' and channel_removed = 0 limit 1",
				dbesc(argv(1))
			);
		}
		else {
			$x = get_sys_channel();
			if($x)
				$r = array($x);
			$mindate = datetime_convert('UTC','UTC', 'now - 14 days');
		}
		if(! $r) {
			$result['message'] = 'Channel not found.';
			json_return_and_die($result);
		}

		logger('zotfeed request: ' . $r[0]['channel_name'], LOGGER_DEBUG);
		$result['project'] = 'Hubzilla';
		$result['messages'] = zot_feed($r[0]['channel_id'],$observer['xchan_hash'],array('mindate' => $mindate));
		$result['success'] = true;
		json_return_and_die($result);
		*/
	}
}
