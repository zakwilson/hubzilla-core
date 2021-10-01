<?php
namespace Zotlabs\Module;

use Zotlabs\Lib\PConfig;
use Zotlabs\Daemon\Master;

class Import_progress extends \Zotlabs\Web\Controller {

	function post() {

		if(! local_channel())
			return;

	}

	function get() {

		if(! local_channel()) {
			return;
		}

		nav_set_selected('Channel Import');

		// items
		$c = PConfig::Get(local_channel(), 'import', 'content_progress');

		if (!$c) {
			$cprogress = 'waiting to start...';
		}
		else {

			$total_cpages = floor(intval($c['items_total']) / intval($c['items_page']));
			if(!$total_cpages) {
				$total_cpages = 1; // because of floor
			}

			$cpage = $c['last_page'] + 1; // because page count start at 0

			$cprogress = intval(floor((intval($cpage) * 100) / $total_cpages));

			if(argv(1) === 'resume_itemsync' && $cprogress < 100) {
				Master::Summon($c['next_cmd']);
				goaway('/import_progress');
			}
		}

		$cprogress_str = ((intval($cprogress)) ? $cprogress . '%' : $cprogress);

		// files
		$f = PConfig::Get(local_channel(), 'import', 'files_progress');

		if (!$f) {
			$fprogress = 'waiting to start...';
		}
		else {
			$total_fpages = floor(intval($f['files_total']) / intval($f['files_page']));
			if(!$total_fpages) {
				$total_fpages = 1;
			}

			$fpage = $f['last_page'] + 1;

			$fprogress = intval(floor((intval($fpage) * 100) / $total_fpages));

			if(argv(1) === 'resume_filesync' && $fprogress < 100) {
				Master::Summon($f['next_cmd']);
				goaway('/import_progress');
			}

		}

		$fprogress_str = ((intval($fprogress)) ? $fprogress . '%' : $fprogress);

		if(is_ajax()) {
			$ret = [
				'cprogress' => $cprogress,
				'fprogress' => $fprogress
			];

			json_return_and_die($ret);
		}

		$o = replace_macros(get_markup_template("import_progress.tpl"), [
			'$cprogress_str' => $cprogress_str,
			'$cprogress' => intval($cprogress),
			'$fprogress_str' => $fprogress_str,
			'$fprogress' => intval($fprogress),
		]);

		return $o;
	}

}
