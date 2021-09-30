<?php
namespace Zotlabs\Module;

use Zotlabs\Lib\PConfig;

class Import_progress extends \Zotlabs\Web\Controller {

	function post() {

		if(! local_channel())
			return;

	}

	function get() {

		if(! local_channel()) {
			return;
		}

		// items
		$c = PConfig::Get(local_channel(), 'import', 'content_progress');

		if (!$c) {
			$co = 'Status items: waiting to start...';
		}
		else {
			$total_cpages = floor(intval($c['items_total']) / intval($c['items_page']));
			if(!$total_cpages)
				$total_cpages = 1; // because of floor

			$cpage = $c['last_page'] + 1; // because page count start at 0

			$co = 'Status items: ' .  floor((intval($cpage) * 100) / $total_cpages) . '%';
		}

		// files
		$f = PConfig::Get(local_channel(), 'import', 'files_progress');

		if (!$f) {
			$fo = 'Status files: waiting to start...';
		}
		else {
			$total_fpages = floor(intval($f['files_total']) / intval($f['files_page']));
			if(!$total_fpages)
				$total_fpages = 1;

			$fpage = $f['last_page'] + 1;

			$fo = 'Status files: ' .  floor((intval($fpage) * 100) / $total_fpages) . '%';
		}

		$o .= '<h3>' . $co . '</h3>';
		if (is_array($c))
			$o .= '<pre>' . htmlspecialchars(print_array($c)) . '</pre>';

		$o .= '<h3>' . $fo . '</h3>';
		if (is_array($f))
			$o .= '<pre>' . htmlspecialchars(print_array($f)) . '</pre>';

		$o .= '<hr>';
		$o .= '<h3>Refresh page for updates!</h3>';

		return $o;
	}

}
