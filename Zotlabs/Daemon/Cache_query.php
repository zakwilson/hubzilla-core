<?php

namespace Zotlabs\Daemon;

use Zotlabs\Lib\Cache;

class Cache_query {

	static public function run($argc, $argv) {

		if(! $argc == 3)
			return;

		$key = $argv[1];

		$pid = get_config('procid', $key, false);
		if ($pid && (function_exists('posix_kill') ? posix_kill($pid, 0) : true)) {
			logger($key . ': procedure already run with pid ' . $pid, LOGGER_DEBUG);
			return;
		}

		$pid = getmypid();
		set_config('procid', $key, $pid);

		array_shift($argv);
		array_shift($argv);
		
		$arr = json_decode(base64_decode($argv[0]), true);

		$r = call_user_func_array('q', $arr);
		if($r)
			Cache::set($key, serialize($r));

		del_config('procid', $key);
	}
}
