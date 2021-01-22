<?php /** @file */

namespace Zotlabs\Daemon;

use Zotlabs\Lib\Activity;
use Zotlabs\Lib\ActivityStreams;
use Zotlabs\Lib\ASCollection;

require_once('include/channel.php');


class Externals {

	static public function run($argc, $argv) {

		logger('externals: start');

		$importer = get_sys_channel();
		$total    = 0;
		$attempts = 0;

		logger('externals: startup', LOGGER_DEBUG);

		// pull in some public posts

		while ($total == 0 && $attempts < 3) {
			$arr = ['url' => ''];
			call_hooks('externals_url_select', $arr);

			if ($arr['url']) {
				$url = $arr['url'];
			}
			else {
				$randfunc = db_getfunc('RAND');

				// fixme this query does not deal with directory realms.

				$r = q("select site_url, site_pull from site where site_url != '%s'
						and site_flags != %d and site_type = %d
                        and site_dead = 0 and site_project like '%s' and site_version > '5.3.1' order by $randfunc limit 1",
					dbesc(z_root()),
					intval(DIRECTORY_MODE_STANDALONE),
					intval(SITE_TYPE_ZOT),
					dbesc('hubzilla%')
				);
				if ($r)
					$url = $r[0]['site_url'];
			}

			$blacklisted = false;

			if (!check_siteallowed($url)) {
				logger('blacklisted site: ' . $url);
				$blacklisted = true;
			}

			$attempts++;

			// make sure we can eventually break out if somebody blacklists all known sites

			if ($blacklisted) {
				if ($attempts > 20)
					break;
				$attempts--;
				continue;
			}

			if ($url) {

				$max = intval(get_config('system', 'max_imported_posts', 30));
				if (intval($max)) {
					logger('externals: fetching outbox');

					$feed_url = $url . '/zotfeed';
					$obj      = new ASCollection($feed_url, $importer, 0, $max);
					$messages = $obj->get();

					if ($messages) {
						foreach ($messages as $message) {
							if (is_string($message)) {
								$message = Activity::fetch($message, $importer);
							}
							$AS = new ActivityStreams($message);
							if ($AS->is_valid() && is_array($AS->obj)) {
								$item = Activity::decode_note($AS);
								Activity::store($importer, $importer['xchan_hash'], $AS, $item, true);
								$total++;
							}
						}
					}
					logger('externals: import_public_posts: ' . $total . ' messages imported', LOGGER_DEBUG);
				}
			}
		}
		return;

		/*		$total    = 0;
				$attempts = 0;

				logger('externals: startup', LOGGER_DEBUG);

				// pull in some public posts

				while ($total == 0 && $attempts < 3) {
					$arr = ['url' => ''];
					call_hooks('externals_url_select', $arr);

					if ($arr['url']) {
						$url = $arr['url'];
					}
					else {
						$randfunc = db_getfunc('RAND');

						// fixme this query does not deal with directory realms.

						$r = q("select site_url, site_pull from site where site_url != '%s' and site_flags != %d and site_type = %d and site_dead = 0 order by $randfunc limit 1",
							dbesc(z_root()),
							intval(DIRECTORY_MODE_STANDALONE),
							intval(SITE_TYPE_ZOT)
						);
						if ($r)
							$url = $r[0]['site_url'];
					}

					$blacklisted = false;

					if (!check_siteallowed($url)) {
						logger('blacklisted site: ' . $url);
						$blacklisted = true;
					}

					$attempts++;

					// make sure we can eventually break out if somebody blacklists all known sites

					if ($blacklisted) {
						if ($attempts > 20)
							break;
						$attempts--;
						continue;
					}

					if ($url) {
						if ($r[0]['site_pull'] > NULL_DATE)
							$mindate = urlencode(datetime_convert('', '', $r[0]['site_pull'] . ' - 1 day'));
						else {
							$days = get_config('externals', 'since_days');
							if ($days === false)
								$days = 15;
							$mindate = urlencode(datetime_convert('', '', 'now - ' . intval($days) . ' days'));
						}

						$feedurl = $url . '/zotfeed?f=&mindate=' . $mindate;

						logger('externals: pulling public content from ' . $feedurl, LOGGER_DEBUG);

						$x = z_fetch_url($feedurl);
						if (($x) && ($x['success'])) {

							q("update site set site_pull = '%s' where site_url = '%s'",
								dbesc(datetime_convert()),
								dbesc($url)
							);

							$j = json_decode($x['body'], true);
							if ($j['success'] && $j['messages']) {
								$sys = get_sys_channel();
								foreach ($j['messages'] as $message) {
									// on these posts, clear any route info.
									$message['route'] = '';
									process_delivery(['hash' => 'undefined'], get_item_elements($message),
										[['hash' => $sys['xchan_hash']]], false, true);
									$total++;
								}
								logger('externals: import_public_posts: ' . $total . ' messages imported', LOGGER_DEBUG);
							}
						}
					}
				}*/
	}
}
